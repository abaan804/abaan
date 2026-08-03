<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtDocument;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Requests\StoreDocumentRequest;

class DocumentController extends Controller
{
    public function index(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-documents')
            && $request->user()->company_id === $family->company_id, 403);

        $members = FtMember::where('family_id', $family->id)->orderBy('full_name')->get();

        return view('familytree::documents.index', compact('family', 'members'));
    }

    public function table(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-documents'), 403);

        $query = FtDocument::where('company_id', $family->company_id)
            ->whereIn('member_id', FtMember::where('family_id', $family->id)->pluck('id'))
            ->with('member');

        if ($type = $request->get('document_type')) $query->where('document_type', $type);
        if ($memberId = $request->get('member_id')) $query->where('member_id', $memberId);

        $documents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('familytree::documents._table', compact('documents', 'family'));
    }

    public function store(StoreDocumentRequest $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        $file = $request->file('file');
        $path = $file->store('familytree/documents', 'public');
        
        FtDocument::create([
            'company_id' => $family->company_id,
            'member_id' => $request->member_id,
            'document_type' => $request->document_type,
            'title' => $request->title,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'notes' => $request->notes,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Document uploaded successfully.'),
        ]);
    }

    public function destroy(Request $request, FtFamily $family, FtDocument $document): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-documents')
            && $request->user()->company_id === $document->company_id, 403);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true, 'message' => __('Document deleted.')]);
    }

    public function download(Request $request, FtFamily $family, FtDocument $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($request->user()->company_id === $document->company_id, 403);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}