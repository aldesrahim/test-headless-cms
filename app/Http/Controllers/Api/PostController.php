<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PostController extends Controller
{
    public function __construct(
        protected readonly PostService $service,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json([
            'message' => __('api.general.show.success'),
            'data' => $this->service->getPaginated($request->all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            return response()->json([
                'message' => __('api.general.create.success'),
                'data' => $this->service->create($request->all()),
            ]);
        } catch (Throwable $exception) {
            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            return response()->json([
                'message' => __('api.general.create.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return response()->json([
            'message' => __('api.general.show.success'),
            'data' => $post,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        try {
            return response()->json([
                'message' => __('api.general.update.success'),
                'data' => $this->service->update($post, $request->all()),
            ]);
        } catch (Throwable $exception) {
            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            return response()->json([
                'message' => __('api.general.update.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $this->service->delete($post);

            return response()->json([
                'message' => __('api.general.delete.success'),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => __('api.general.delete.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }

    public function markAsPublished(Post $post)
    {
        try {
            $post->markAsPublished();

            return response()->json([
                'message' => __('api.general.update.success'),
                'data' => $post,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => __('api.general.update.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }

    public function markAsDraft(Post $post)
    {
        try {
            $post->markAsDraft();

            return response()->json([
                'message' => __('api.general.update.success'),
                'data' => $post,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => __('api.general.update.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }
}
