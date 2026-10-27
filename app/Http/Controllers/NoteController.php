<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Note::query()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ]);

        $note = Note::query()->create($validated);

        return response()->json($note, Response::HTTP_CREATED);
    }

    public function show(Note $note): JsonResponse
    {
        return response()->json($note);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ]);

        $note->update($validated);

        return response()->json($note->refresh());
    }

    public function destroy(Note $note): Response
    {
        $note->delete();

        return response()->noContent();
    }
}
