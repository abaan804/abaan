<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Requests\StoreMosqueRequest;
use Modules\Masjid\Requests\UpdateMosqueRequest;

class MosqueController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('masjid.view-dashboard'), 403);

        return view('masjid::mosques.index');
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('masjid.view-dashboard'), 403);

        $mosques = MasjidMosque::where('company_id', $request->user()->company_id)
            ->withCount('members')
            ->orderBy('mosque_name')
            ->paginate(15)->withQueryString();

        return view('masjid::mosques._table', compact('mosques'));
    }

    public function json(MasjidMosque $mosque): JsonResponse
    {
        $this->authorizeCompany($mosque);

        return response()->json(['data' => $mosque]);
    }

    public function store(StoreMosqueRequest $request): JsonResponse
    {
        $data = $request->safe()->except('logo');
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('masjid/logos', 'public');
        }

        $mosque = MasjidMosque::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Mosque created successfully.'),
            'data' => $mosque,
        ]);
    }

    public function edit(Request $request, MasjidMosque $mosque): View
    {
        $this->authorizeCompany($mosque);

        return view('masjid::mosques.edit', compact('mosque'));
    }

    public function update(UpdateMosqueRequest $request, MasjidMosque $mosque): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeCompany($mosque);

        $data = $request->safe()->except('logo');
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('masjid/logos', 'public');
        }

        $mosque->update($data);

        return back()->with('success', __('Mosque profile updated.'));
    }

    public function destroy(MasjidMosque $mosque): JsonResponse
    {
        $this->authorizeCompany($mosque);
        abort_unless(request()->user()->can('masjid.manage-mosque-profile'), 403);

        if ($mosque->members()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a mosque with existing members. Set status to Inactive instead.'),
            ], 422);
        }

        $mosque->delete();

        return response()->json(['success' => true, 'message' => __('Mosque deleted.')]);
    }

    protected function authorizeCompany(MasjidMosque $mosque): void
    {
        abort_unless(request()->user()->company_id === $mosque->company_id, 403);
    }
}