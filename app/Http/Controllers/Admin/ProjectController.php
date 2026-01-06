<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function show($id): JsonResponse
    {
        // Demo payload that matches the Brand page needs
        return $this->response->statusOk([
            'project' => [
                'id' => (int) $id,
                'name' => 'Project Name 1',
                'status' => 'Pending Feedback',
                'status_badge' => 'Pending',
                'plan' => 'Standard Plan',
                'plan_badge' => 'Paid Plan',
                'assigned_to' => 'Farouk Ahmed',
                'joined_at' => '2025-09-21',
            ],
            'brief' => 'Information about what the user has submitted in the chat',
            'concepts' => [],
            'feedback' => [
                [
                    'text' => 'The overall layout feels clean, but we’d like the navigation bar to be more prominent...',
                    'user' => 'Mohamed Samir',
                    'avatar' => 'https://i.pravatar.cc/40?img=12',
                ],
                [
                    'text' => 'Increase the visibility of the navigation bar (spacing + bolder labels).',
                    'user' => 'Admin',
                    'avatar' => null,
                ],
            ],
            'files' => [],
        ]);
    }
}

