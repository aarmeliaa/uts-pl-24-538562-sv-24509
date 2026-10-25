<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CRUD for the logged-in user's media.
 * Every query goes through $request->user()->media(), so a user can never
 * read or change another user's data (it simply returns 404).
 */
class MediaController extends Controller
{
    /**
     * GET /api/media
     */
    public function index(Request $request): JsonResponse
    {
        $media = $request->user()->media()->latest()->get();

        return response()->json($media);
    }

    /**
     * POST /api/media
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = $request->user()->media()->create($request->validated());

        return response()->json($media, 201);
    }

    /**
     * GET /api/media/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $media = $request->user()->media()->findOrFail($id);

        return response()->json($media);
    }

    /**
     * PUT/PATCH /api/media/{id}
     */
    public function update(UpdateMediaRequest $request, string $id): JsonResponse
    {
        $media = $request->user()->media()->findOrFail($id);
        $media->update($request->validated());

        return response()->json($media);
    }

    /**
     * DELETE /api/media/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $media = $request->user()->media()->findOrFail($id);
        $media->delete();

        return response()->json([
            'message' => 'Media deleted.',
        ]);
    }
}
