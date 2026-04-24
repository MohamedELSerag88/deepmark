<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandNameSuggestion;
use App\Models\MeetingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 10);
        $query = MeetingRequest::with(['user', 'brandChat'])->orderByDesc('id');

        // Optional filters
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', (int)$userId);
        }

        $meetings = $query->paginate($perPage);

        $items = collect($meetings->items())->map(function (MeetingRequest $m) {
            $brand = $m->brandChat;
            $brandTitle = $this->deriveBrandTitle($brand?->topic, $brand?->response, $brand?->id);

            return [
                'id' => $m->id,
                'status' => $m->status,
                'meeting_at' => $m->meeting_at,
                'notes' => $m->notes,
                'user' => $m->user ? [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                    'email' => $m->user->email,
                ] : null,
                'brand' => $brand ? [
                    'id' => $brand->id,
                    'topic' => $brand->topic,
                    'title' => $brandTitle,
                ] : null,
                'created_at' => $m->created_at,
            ];
        });

        return $this->response->statusOk([
            'meetings' => $items,
            'pagination' => [
                'current_page' => $meetings->currentPage(),
                'per_page' => $meetings->perPage(),
                'total' => $meetings->total(),
                'last_page' => $meetings->lastPage(),
            ],
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $meeting = MeetingRequest::find($id);
        if (!$meeting) {
            return $this->response->notFound(['message' => 'Meeting not found'], 404);
        }
        $validated = $request->validate([
            'status' => 'sometimes|required|string|in:pending,approved,cancelled,done',
            'meeting_at' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);
        $meeting->fill($validated)->save();

        return $this->response->statusOk([
            'meeting' => $meeting->load(['user','brandChat'])
        ]);
    }

    private function deriveBrandTitle(?string $topic, $response, ?int $id): string
    {
        if (is_array($response)) {
            if ($topic === 'brand_names') {
                $items = $response['items'] ?? [];
                if (is_array($items) && count($items)) {
                    $first = $items[0];
                    if (!empty($first['name'])) {
                        return (string)$first['name'];
                    }
                }
                if ($id) {
                    $firstSuggestion = BrandNameSuggestion::where('brand_chat_id', $id)
                        ->orderBy('suggestion_index')
                        ->value('name');
                    if ($firstSuggestion) {
                        return (string)$firstSuggestion;
                    }
                }
            } elseif ($topic === 'brand_text') {
                $bt = $response['brand_text'] ?? null;
                if (is_array($bt)) {
                    if (isset($bt['taglines']) && is_array($bt['taglines']) && count($bt['taglines'])) {
                        return (string)$bt['taglines'][0];
                    }
                    if (isset($bt['en']['taglines']) && is_array($bt['en']['taglines']) && count($bt['en']['taglines'])) {
                        return (string)$bt['en']['taglines'][0];
                    }
                    if (isset($bt['ar']['taglines']) && is_array($bt['ar']['taglines']) && count($bt['ar']['taglines'])) {
                        return (string)$bt['ar']['taglines'][0];
                    }
                }
            }
        }
        return 'Brand #' . ($id ?? '-');
    }
}

