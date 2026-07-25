<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\ContactSubmissionResource;
use App\Http\Resources\Mobile\MessageResource;
use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $submissions = ContactSubmission::query()
            ->latest()
            ->paginate($perPage);

        return $this->statusOk([
            'submissions' => ContactSubmissionResource::collection(collect($submissions->items())),
            'pagination' => [
                'current_page' => $submissions->currentPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
                'last_page' => $submissions->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $submission = ContactSubmission::find($id);
        if (!$submission) {
            return $this->notFound(['message' => 'Submission not found'], 404);
        }
        return $this->statusOk(['submission' => new ContactSubmissionResource($submission)]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $submission = ContactSubmission::find($id);
        if (!$submission) {
            return $this->notFound(['message' => 'Submission not found'], 404);
        }

        $validated = $request->validate([
            'is_read' => 'required|boolean',
        ]);

        $submission->update($validated);
        return $this->statusOk(['submission' => new ContactSubmissionResource($submission->fresh())]);
    }

    public function destroy($id): JsonResponse
    {
        $submission = ContactSubmission::find($id);
        if (!$submission) {
            return $this->notFound(['message' => 'Submission not found'], 404);
        }
        $submission->delete();
        return $this->statusOk(new MessageResource(['message' => 'Deleted', 'id' => (int) $id]));
    }
}
