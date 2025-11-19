<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Asnaf;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::withCount('subprograms')->orderBy('sort_order', 'asc')->get();
        $selectedProgramId = $request->query('program_id') ?: ($programs->first()?->id);
        $searchSubprogram = $request->query('search_subprogram');

        $subprograms = collect();
        if ($selectedProgramId) {
            $query = Subprogram::where('program_id', $selectedProgramId)->orderBy('sort_order', 'asc');
            if ($searchSubprogram) {
                $query->where('nama', 'like', '%' . $searchSubprogram . '%');
            }
            $subprograms = $query->get();
        }

        $asnafs = Asnaf::orderBy('sort_order', 'asc')->get();

        return view('master.programs', compact('programs', 'selectedProgramId', 'subprograms', 'asnafs', 'searchSubprogram'));
    }

    public function storeProgram(Request $request)
    {
        $programId = $request->input('program_id');

        $validated = $request->validate([
            'program_nama' => 'required|string|max:255',
            'program_kode' => ['required', 'string', 'max:255', Rule::unique('programs', 'kode')->ignore($programId)],
            'program_sort_order' => 'required|integer',
            'program_is_active' => 'nullable|boolean',
        ]);

        $program = Program::updateOrCreate(
            ['id' => $programId],
            [
                'nama' => $validated['program_nama'],
                'kode' => $validated['program_kode'],
                'sort_order' => $validated['program_sort_order'],
                'is_active' => $request->has('program_is_active') ? (bool) $request->input('program_is_active') : true,
            ]
        );

        $msg = $programId ? 'Data program berhasil diperbarui.' : 'Program baru berhasil ditambahkan.';
        return redirect()->route('master.programs', ['program_id' => $program->id])->with('success', $msg);
    }

    public function destroyProgram($id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return redirect()->route('master.programs')->with('success', 'Data program berhasil dihapus.');
    }

    public function storeSubprogram(Request $request)
    {
        $subprogramId = $request->input('subprogram_id');

        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'subprogram_nama' => 'required|string|max:255',
            'subprogram_jenis' => 'required|in:Pendistribusian,Pendayagunaan',
            'subprogram_sort_order' => 'required|integer',
            'subprogram_is_active' => 'nullable|boolean',
        ]);

        Subprogram::updateOrCreate(
            ['id' => $subprogramId],
            [
                'program_id' => $validated['program_id'],
                'nama' => $validated['subprogram_nama'],
                'jenis' => $validated['subprogram_jenis'],
                'sort_order' => $validated['subprogram_sort_order'],
                'is_active' => $request->has('subprogram_is_active') ? (bool) $request->input('subprogram_is_active') : true,
            ]
        );

        $msg = $subprogramId ? 'Data sub-program berhasil diperbarui.' : 'Sub-program baru berhasil ditambahkan.';
        return redirect()->route('master.programs', ['program_id' => $validated['program_id']])->with('success', $msg);
    }

    public function destroySubprogram($id)
    {
        $sub = Subprogram::findOrFail($id);
        $programId = $sub->program_id;
        $sub->delete();

        return redirect()->route('master.programs', ['program_id' => $programId])->with('success', 'Data sub-program berhasil dihapus.');
    }

    public function moveUpAsnaf($id)
    {
        $asnaf = Asnaf::findOrFail($id);
        $previousAsnaf = Asnaf::where('sort_order', '<', $asnaf->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousAsnaf) {
            $temp = $asnaf->sort_order;
            $asnaf->sort_order = $previousAsnaf->sort_order;
            $previousAsnaf->sort_order = $temp;
            $asnaf->save();
            $previousAsnaf->save();
        }

        return redirect()->back()->with('success', 'Urutan asnaf berhasil diubah.');
    }

    public function moveDownAsnaf($id)
    {
        $asnaf = Asnaf::findOrFail($id);
        $nextAsnaf = Asnaf::where('sort_order', '>', $asnaf->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextAsnaf) {
            $temp = $asnaf->sort_order;
            $asnaf->sort_order = $nextAsnaf->sort_order;
            $nextAsnaf->sort_order = $temp;
            $asnaf->save();
            $nextAsnaf->save();
        }

        return redirect()->back()->with('success', 'Urutan asnaf berhasil diubah.');
    }
}
