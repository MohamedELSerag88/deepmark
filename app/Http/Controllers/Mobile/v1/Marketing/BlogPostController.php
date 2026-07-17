<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;

class BlogPostController extends Controller
{
    public function index(): JsonResponse
    {
        $blogs = BlogPost::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->get();

        return $this->response->statusOk([
            'data' => BlogPostResource::collection($blogs),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = BlogPost::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$blog) {
            return $this->response->notFound(['message' => 'Blog post not found'], 404);
        }

        return $this->response->statusOk([
            'data' => new BlogPostResource($blog),
        ]);
    }
}
