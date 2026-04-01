<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BrandChat;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Optional: filter by created_at date range (YYYY-MM-DD)
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Sort by join date
        if (in_array($request->get('date_joined'), ['newest', 'oldest'])) {
            $direction = $request->get('date_joined') === 'oldest' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderByDesc('id');
        }

        $users = $query->paginate((int)$request->get('per_page', 10));

        return $this->response->statusOk([
            'users' => collect($users->items())->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name ?? trim(($u->fname ?? '') . ' ' . ($u->lname ?? '')),
                    'email' => $u->email,
                    // Frontend design expects a "plan" value; default to "Paid" until plans exist
                    'plan' => 'Paid',
                    'joined_at' => optional($u->created_at)->toDateString(),
                ];
            }),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->response->notFound(['message' => 'User not found'], 404);
        }
        // Projects (brand chats)
        $projects = \App\Models\BrandChat::where('user_id', $user->id)
            ->latest('id')
            ->limit(20)
            ->get([
                'id','parent_id','user_id','topic','language','answers','response','raw_response','created_at','updated_at','device_token'
            ]);

        // Latest subscription with plan details (for quick summary)
        $latestSub = \App\Models\Subscription::with('plan')
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
        $latestSubMapped = $latestSub ? [
            'id' => $latestSub->id,
            'user_id' => $latestSub->user_id,
            'plan_id' => $latestSub->plan_id,
            'plan_name' => $latestSub->plan?->name,
            'amount_cents' => $latestSub->plan?->price_cents,
            'currency' => $latestSub->plan?->currency,
            'interval' => $latestSub->plan?->interval,
            'status' => $latestSub->status,
            'started_at' => $latestSub->started_at,
            'ends_at' => $latestSub->ends_at,
            'stripe_session_id' => $latestSub->stripe_session_id,
            'stripe_subscription_id' => $latestSub->stripe_subscription_id,
            'created_at' => $latestSub->created_at,
        ] : null;

        return $this->response->statusOk([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'email' => $user->email,
                'image' => $user->image,
                'bio' => $user->bio,
                'joined_at' => optional($user->created_at)->toDateString(),
                'last_login' => optional($user->updated_at)->toDateTimeString(),
            ],
            'projects' => $projects,
            // For compatibility with existing UI labels:
            'favorites' => $user->favorites,
            'plans' =>  $latestSubMapped,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';
        $path = 'exports/' . $filename;

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', 'Name', 'Email']);

        User::chunk(500, function ($chunk) use ($handle) {
            foreach ($chunk as $u) {
                fputcsv($handle, [$u->id, $u->name, $u->email]);
            }
        });
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('public')->put($path, $csv);
        $url = Storage::disk('public')->url($path);

        return $this->response->statusOk([
            'file' => [
                'name' => $filename,
                'size_kb' => round(strlen($csv) / 1024),
                'url' => $url,
            ],
        ]);
    }

    /**
     * Stream the CSV directly as a download (no storage URL needed).
     */
    public function exportDownload(Request $request)
    {
        $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';

        $callback = function () {
            $out = fopen('php://output', 'w');
            // header
            fputcsv($out, ['ID', 'Name', 'Email']);
            // body
            User::chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $u) {
                    fputcsv($out, [$u->id, $u->name, $u->email]);
                }
            });
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->response->notFound(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'fname' => 'nullable|string|max:255',
            'lname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        if (isset($validated['password']) && $validated['password']) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->fill($validated);
        $user->save();

        return $this->response->statusOk([
            'user' => [
                'id' => $user->id,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'name' => trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: ($user->name ?? null),
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->response->notFound(['message' => 'User not found'], 404);
        }
        $user->delete();
        return $this->response->statusOk([
            'message' => 'User deleted successfully',
            'id' => (int)$id,
        ]);
    }

    public function projects(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->response->notFound(['message' => 'User not found'], 404);
        }
        $query = BrandChat::where('user_id', $user->id)->orderByDesc('created_at');
        $projects = $query->paginate((int)$request->get('per_page', 10));

        return $this->response->statusOk([
            'projects' => collect($projects->items())->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->topic ?? 'Project',
                    'status' => 'Pending',
                    'created_at' => optional($p->created_at)->toDateString(),
                ];
            }),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'last_page' => $projects->lastPage(),
            ],
        ]);
    }

    public function plans(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->response->notFound(['message' => 'User not found'], 404);
        }
        $query = Subscription::with('plan')->where('user_id', $user->id)->orderByDesc('created_at');
        $subs = $query->paginate((int)$request->get('per_page', 10));

        return $this->response->statusOk([
            'plans' => collect($subs->items())->map(function ($s) {
                $plan = $s->plan;
                return [
                    'id' => $s->id,
                    'plan_name' => $plan?->name ?? 'Plan',
                    'amount' => $plan ? number_format(($plan->price_cents ?? 0) / 100, 2) : '0.00',
                    'currency' => $plan->currency ?? 'USD',
                    'status' => $s->status ?? 'pending',
                    'started_at' => optional($s->started_at)->toDateString(),
                ];
            }),
            'pagination' => [
                'current_page' => $subs->currentPage(),
                'per_page' => $subs->perPage(),
                'total' => $subs->total(),
                'last_page' => $subs->lastPage(),
            ],
        ]);
    }
}

