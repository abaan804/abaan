<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidNote;
use Modules\Masjid\Models\MasjidSeason;

class NoteController extends Controller
{
    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($mosque->company_id === $request->user()->company_id, 403);

        $seasons = MasjidSeason::where('mosque_id', $mosque->id)
            ->orderByDesc('start_date')->get();

        $pinnedNotes = MasjidNote::where('mosque_id', $mosque->id)
            ->pinned()
            ->with('season')
            ->orderByDesc('updated_at')
            ->get();

        $generalNotes = MasjidNote::where('mosque_id', $mosque->id)
            ->general()
            ->where('is_pinned', false)
            ->with('season')
            ->orderByDesc('updated_at')
            ->paginate(12);

        $seasonNotes = MasjidNote::where('mosque_id', $mosque->id)
            ->season()
            ->where('is_pinned', false)
            ->with('season')
            ->orderByDesc('updated_at')
            ->paginate(12);

        return view('masjid::notes.index', compact(
            'mosque', 'seasons',
            'pinnedNotes', 'generalNotes', 'seasonNotes'
        ));
    }

    public function store(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless($mosque->company_id === $request->user()->company_id, 403);

        $data = $request->validate([
            'type'      => 'required|in:season,general',
            'season_id' => 'nullable|required_if:type,season|exists:masjid_seasons,id',
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'color'     => 'required|in:default,warning,danger,success,info',
            'is_pinned' => 'nullable|boolean',
        ]);

        $data['company_id'] = $mosque->company_id;
        $data['mosque_id']  = $mosque->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['is_pinned']  = $request->boolean('is_pinned');

        $note = MasjidNote::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Note saved.'),
            'data'    => $note->load('season'),
        ]);
    }

    public function json(Request $request, MasjidMosque $mosque, MasjidNote $note): JsonResponse
    {
        abort_unless($note->mosque_id === $mosque->id, 403);
        return response()->json(['data' => $note]);
    }

    public function update(Request $request, MasjidMosque $mosque, MasjidNote $note): JsonResponse
    {
        abort_unless($note->mosque_id === $mosque->id, 403);

        $data = $request->validate([
            'type'      => 'required|in:season,general',
            'season_id' => 'nullable|required_if:type,season|exists:masjid_seasons,id',
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'color'     => 'required|in:default,warning,danger,success,info',
            'is_pinned' => 'nullable|boolean',
        ]);

        $data['updated_by'] = auth()->id();
        $data['is_pinned']  = $request->boolean('is_pinned');
        $note->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Note updated.'),
            'data'    => $note->load('season'),
        ]);
    }

    public function destroy(Request $request, MasjidMosque $mosque, MasjidNote $note): JsonResponse
    {
        abort_unless($note->mosque_id === $mosque->id, 403);
        $note->delete();
        return response()->json(['success' => true, 'message' => __('Note deleted.')]);
    }

    public function togglePin(Request $request, MasjidMosque $mosque, MasjidNote $note): JsonResponse
    {
        abort_unless($note->mosque_id === $mosque->id, 403);
        $note->update(['is_pinned' => ! $note->is_pinned]);
        return response()->json([
            'success'   => true,
            'is_pinned' => $note->is_pinned,
            'message'   => $note->is_pinned ? __('Note pinned.') : __('Note unpinned.'),
        ]);
    }
}