<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Marketing\StoreContactSubmissionRequest;
use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;

class ContactSubmissionController extends Controller
{
    public function store(StoreContactSubmissionRequest $request): JsonResponse
    {
        $submission = ContactSubmission::create($request->validated());

        return $this->response->statusOk([
            'message' => 'Thank you. We will get back to you soon.',
            'submission' => $submission,
        ], 201);
    }
}
