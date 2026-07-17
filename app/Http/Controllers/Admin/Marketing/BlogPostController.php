<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = BlogPost::query()->orderByDesc('published_at')->orderBy('sort_order')->get();
        return $this->response->statusOk(['posts' => $posts]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $post = BlogPost::create($validated);
        return $this->response->statusOk(['post' => $post], 201);
    }

    public function show($id): JsonResponse
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return $this->response->notFound(['message' => 'Blog post not found'], 404);
        }
        return $this->response->statusOk(['post' => $post]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return $this->response->notFound(['message' => 'Blog post not found'], 404);
        }
        $validated = $request->validate($this->rules($id));
        $post->update($validated);
        return $this->response->statusOk(['post' => $post->fresh()]);
    }

    public function destroy($id): JsonResponse
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return $this->response->notFound(['message' => 'Blog post not found'], 404);
        }
        $post->delete();
        return $this->response->statusOk(['message' => 'Deleted', 'id' => (int) $id]);
    }

    protected function rules($id = null): array
    {
        $slug = $id
            ? 'sometimes|required|string|max:150|unique:blog_posts,slug,' . $id
            : 'required|string|max:150|unique:blog_posts,slug';

        return [
            'slug' => $slug,
            'published_at' => 'nullable|date',
            'title_en' => ($id ? 'sometimes|' : '') . 'required|string|max:500',
            'title_ar' => 'nullable|string|max:500',
            'badge_en' => 'nullable|string|max:100',
            'badge_ar' => 'nullable|string|max:100',
            'image_url' => 'nullable|string|max:2000',
            'author_name' => 'nullable|string|max:255',
            'author_title_en' => 'nullable|string|max:255',
            'author_title_ar' => 'nullable|string|max:255',
            'author_avatar_url' => 'nullable|string|max:2000',
            'lead_en' => 'nullable|string|max:5000',
            'lead_ar' => 'nullable|string|max:5000',
            'content_en' => 'nullable|array',
            'content_ar' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
