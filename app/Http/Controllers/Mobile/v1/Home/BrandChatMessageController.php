<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\StoreBrandChatMessageRequest;
use App\Models\BrandChat;
use App\Models\BrandChatMessage;
use App\Models\BrandNameSuggestion;
use App\Services\AI\DeepSeekService;
use App\Services\Domain\DomainAvailabilityService;
use Illuminate\Http\JsonResponse;

class BrandChatMessageController extends Controller
{
    public function index(int|string $id): JsonResponse
    {
        $chat = BrandChat::where('id', (int) $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$chat) {
            return $this->response->notFound(['message' => 'Project not found']);
        }

        $messages = BrandChatMessage::where('brand_chat_id', $chat->id)
            ->orderBy('id')
            ->get(['id', 'brand_chat_id', 'user_id', 'role', 'message', 'payload', 'created_at']);

        return $this->response->statusOk([
            'data' => [
                'project_id' => $chat->id,
                'messages' => $messages,
            ],
        ]);
    }

    public function store(
        StoreBrandChatMessageRequest $request,
        int|string $id,
        DeepSeekService $ai,
        DomainAvailabilityService $domains
    ): JsonResponse
    {
        $chat = BrandChat::where('id', (int) $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$chat) {
            return $this->response->notFound(['message' => 'Project not found']);
        }

        $userMessage = BrandChatMessage::create([
            'brand_chat_id' => $chat->id,
            'user_id' => auth('api')->id(),
            'role' => 'user',
            'message' => $request->input('message'),
            'payload' => null,
        ]);

        $comment = (string) $request->input('message', '');
        $tlds = (array) $request->input('tlds', ['com', 'io', 'ai']);
        $currentItems = BrandNameSuggestion::where('brand_chat_id', $chat->id)
            ->orderBy('suggestion_index')
            ->get()
            ->map(fn (BrandNameSuggestion $s) => [
                'suggestion_index' => $s->suggestion_index,
                'id' => $s->suggestion_index,
                'project_id' => $chat->id,
                'name' => $s->name,
                'archetype' => $s->archetype,
                'domains' => $s->domains,
                'liked' => (bool)$s->liked,
            ])
            ->values()
            ->all();

        $system = 'You are a senior brand naming expert. You produce STRICT JSON outputs only.';
        $instructions = "Revise the following brand name suggestions based on the user comments.\n"
            . "Return STRICT JSON with this shape:\n"
            . "{ \"response_message\": \"short message to user\", \"suggestions\": [ { \"name\": \"...\", \"archetype\": \"...\", \"rationale\": \"<= 14 words\" } ] }\n"
            . "If you cannot add response_message, still return suggestions as valid JSON.\n";

        $prompt = $instructions
            . "Current suggestions JSON:\n"
            . json_encode(
                ['suggestions' => array_map(fn($i) => ['name' => $i['name'] ?? null, 'archetype' => $i['archetype'] ?? null], $currentItems)],
                JSON_UNESCAPED_UNICODE
            )
            . "\n\nUser comments:\n"
            . $comment;

        $raw = $ai->simpleChat($prompt, $system);
        $parsed = $this->decodeJsonLenient($raw);
        $list = (is_array($parsed) && isset($parsed['suggestions']) && is_array($parsed['suggestions']))
            ? $parsed['suggestions'] : [];

        $items = [];
        $idx = 1;
        foreach ($list as $s) {
            $name = trim((string) ($s['name'] ?? ''));
            if ($name === '') {
                continue;
            }
//            $domainResults = $domains->check($name, $tlds);
//            $primary = collect($domainResults)->firstWhere('domain', strtolower($name) . '.com')
//                ?: (count($domainResults) ? $domainResults[0] : ['domain' => strtolower($name) . '.com', 'available' => null]);
            $domainResults = [];
            $primary = ['domain' => strtolower($name) . '.com', 'available' => null];
            $items[] = [
                'suggestion_index' => $idx,
                'id' => $idx,
                'project_id' => $chat->id,
                'name' => $name,
                'archetype' => (string) ($s['archetype'] ?? ''),
                'domains' => [
                    'primary' => [
                        'tld' => '.' . substr(strrchr($primary['domain'], '.'), 1),
                        'available' => (bool) ($primary['available'] ?? false),
                        'domain' => $primary['domain'],
                    ],
                    'list' => array_slice($domainResults, 0, 3),
                    'more_count' => max(0, count($domainResults) - 3),
                ],
                'liked' => false,
            ];
            $idx++;
        }

        $responseMessage = (is_array($parsed) && isset($parsed['response_message']) && is_string($parsed['response_message']) && trim($parsed['response_message']) !== '')
            ? trim($parsed['response_message'])
            : 'I updated your brand name suggestions based on your message.';

        BrandNameSuggestion::where('brand_chat_id', $chat->id)->delete();
        foreach ($items as $k => $row) {
            $brandNameSuggestion = BrandNameSuggestion::create([
                'brand_chat_id' => $chat->id,
                'suggestion_index' => (int)($row['suggestion_index'] ?? 1),
                'name' => (string)$row['name'],
                'archetype' => $row['archetype'] ?? null,
                'domains' => $row['domains'] ?? null,
                'liked' => (bool)($row['liked'] ?? false),
            ]);
            $items[$k]['id'] = $brandNameSuggestion->id;
        }

        $assistantPayload = [
            'response_message' => $responseMessage,
            'items' => $items,
        ];

        $assistantMessage = BrandChatMessage::create([
            'brand_chat_id' => $chat->id,
            'user_id' => auth('api')->id(),
            'role' => 'assistant',
            'message' => $responseMessage,
            'payload' => $assistantPayload,
        ]);

        return $this->response->statusOk([
            'data' => [
                'project_id' => $chat->id,
                'user_message' => $userMessage,
                'assistant_message' => $assistantMessage,
                'payload' => $assistantPayload,
            ],
            'message' => 'Chat updated successfully',
        ]);
    }

    private function decodeJsonLenient(string $text): ?array
    {
        $direct = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($direct)) {
            return $direct;
        }

        $trimmed = trim($text);
        if (preg_match('/```(?:json)?\\s*([\\s\\S]*?)\\s*```/i', $trimmed, $m)) {
            $block = trim($m[1]);
            $fromFence = json_decode($block, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($fromFence)) {
                return $fromFence;
            }
        }

        $withoutFences = preg_replace('/```[a-z]*\\s*|```/i', '', $trimmed);
        if (is_string($withoutFences)) {
            $retry = json_decode(trim($withoutFences), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($retry)) {
                return $retry;
            }
        }

        if (preg_match('/\\{(?:[^{}]|(?R))*\\}/s', $trimmed, $m2)) {
            $object = $m2[0];
            $fromObject = json_decode($object, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($fromObject)) {
                return $fromObject;
            }
        }

        return null;
    }
}
