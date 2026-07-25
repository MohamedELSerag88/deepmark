<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardResource;
use App\Models\BrandChat;
use App\Models\BrandNameFavorite;
use App\Models\MeetingRequest;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function overview(): JsonResponse
    {
        $now = now();

        // Users
        $totalUsers = User::count();

        // Brands (brand name chats)
        $brandNameChats = BrandChat::where('topic', 'brand_names')->count();
        $brandTextChats = BrandChat::where('topic', 'brand_text')->count();
        $totalBrandChats = $brandNameChats + $brandTextChats;

        // Questions
        $totalQuestions = Question::count();

        // Meetings
        $totalMeetings = MeetingRequest::count();
        $upcomingMeetings = MeetingRequest::where('meeting_at', '>=', $now)
            ->where('status', '!=', 'cancelled')
            ->count();
        $doneMeetings = MeetingRequest::where('meeting_at', '<', $now)
            ->where('status', '!=', 'cancelled')
            ->count();

        // Subscriptions
        $subscriptionsByStatus = Subscription::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Active subscription MRR-like amount (sum of plan price_cents for active subs)
        $activeAmountCents = Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_cents');

        // Favorites
        $favoriteBrandsCount = BrandNameFavorite::count();

        return $this->okResource(new DashboardResource([
            'users' => [
                'total' => $totalUsers,
            ],
            'brands' => [
                'total_chats' => $totalBrandChats,
                'brand_names_chats' => $brandNameChats,
                'brand_text_chats' => $brandTextChats,
                'favorites_total' => $favoriteBrandsCount,
            ],
            'questions' => [
                'total' => $totalQuestions,
            ],
            'meetings' => [
                'total' => $totalMeetings,
                'upcoming' => $upcomingMeetings,
                'done' => $doneMeetings,
            ],
            'subscriptions' => [
                'by_status' => [
                    'active' => (int) ($subscriptionsByStatus['active'] ?? 0),
                    'pending' => (int) ($subscriptionsByStatus['pending'] ?? 0),
                    'canceled' => (int) ($subscriptionsByStatus['canceled'] ?? 0),
                ],
                'active_amount_cents' => (int) $activeAmountCents,
                'currency' => 'USD',
            ],
        ]));
    }
}

