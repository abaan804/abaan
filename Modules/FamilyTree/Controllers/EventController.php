<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtEvent;
use Modules\FamilyTree\Models\FtEventMedia;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Requests\StoreEventRequest;
use Modules\FamilyTree\Requests\UpdateEventRequest;

class EventController extends Controller
{
    public function index(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-events')
            && $request->user()->company_id === $family->company_id, 403);

        $members = FtMember::where('family_id', $family->id)->orderBy('full_name')->get();

        return view('familytree::events.index', compact('family', 'members'));
    }

    public function table(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-events'), 403);

        $query = FtEvent::where('family_id', $family->id)
            ->with(['member', 'media']);

        if ($type = $request->get('event_type')) $query->where('event_type', $type);
        if ($memberId = $request->get('member_id')) $query->where('member_id', $memberId);

        $events = $query->orderByDesc('event_date')->paginate(20)->withQueryString();

        return view('familytree::events._table', compact('events', 'family'));
    }

    public function json(Request $request, FtFamily $family, FtEvent $event): JsonResponse
    {
        abort_unless($event->family_id === $family->id, 403);
        $event->load('media');
        return response()->json(['data' => $event]);
    }

    public function store(StoreEventRequest $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        $data = $request->safe()->except('media');
        $data['company_id'] = $family->company_id;
        $data['family_id'] = $family->id;
        $data['created_by'] = $request->user()->id;

        $event = FtEvent::create($data);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('familytree/events', 'public');
                $fileType = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document';
                FtEventMedia::create([
                    'event_id' => $event->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_type' => $fileType,
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Event added successfully.'),
            'data' => $event,
        ]);
    }

    public function update(UpdateEventRequest $request, FtFamily $family, FtEvent $event): JsonResponse
    {
        abort_unless($event->family_id === $family->id, 403);
        $event->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Event updated.'),
            'data' => $event,
        ]);
    }

    public function destroy(Request $request, FtFamily $family, FtEvent $event): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-events')
            && $event->family_id === $family->id, 403);

        foreach ($event->media as $media) {
            Storage::disk('public')->delete($media->file_path);
        }

        $event->delete();

        return response()->json(['success' => true, 'message' => __('Event deleted.')]);
    }

    public function destroyMedia(Request $request, FtFamily $family, FtEventMedia $media): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-events'), 403);

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return response()->json(['success' => true, 'message' => __('Media removed.')]);
    }
}