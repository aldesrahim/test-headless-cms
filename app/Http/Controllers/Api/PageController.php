<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Pages\PageService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PageController extends Controller
{
    public function __construct(
        protected readonly PageService $service,
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
    public function show(Page $page)
    {
        return response()->json([
            'message' => __('api.general.show.success'),
            'data' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        try {
            return response()->json([
                'message' => __('api.general.update.success'),
                'data' => $this->service->update($page, $request->all()),
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
    public function destroy(Page $page)
    {
        try {
            $this->service->delete($page);

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

    public function markAsPublished(Page $page)
    {
        try {
            $page->markAsPublished();

            return response()->json([
                'message' => __('api.general.update.success'),
                'data' => $page,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => __('api.general.update.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }

    public function markAsDraft(Page $page)
    {
        try {
            $page->markAsDraft();

            return response()->json([
                'message' => __('api.general.update.success'),
                'data' => $page,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => __('api.general.update.failed'),
                'error' => $exception->getMessage(),
            ], 422);
        }
    }
}
