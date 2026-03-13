<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::latest()->get();
        return view('kelas.index', compact('kelas'));
    }

    public function create()
    {
        return view('kelas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required',
            'tahun_ajaran' => 'required'
        ]);

        Kelas::create([
            'nama_kelas'   => $request->nama_kelas,
            'tahun_ajaran' => $request->tahun_ajaran,
            'created_user' => auth()->id(),
        ]);

        return redirect()->route('kelas.index')
            ->with('success', 'Data kelas berhasil ditambahkan');
    }

    public function edit(Kelas $kela)
    {
        return view('kelas.edit', [
            'kelas' => $kela
        ]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'nama_kelas' => 'required',
            'tahun_ajaran' => 'required'
        ]);

        $kela->update([
            'nama_kelas'   => $request->nama_kelas,
            'tahun_ajaran' => $request->tahun_ajaran,
            'updated_user' => auth()->id(),
        ]);

        return redirect()->route('kelas.index')
            ->with('success', 'Data kelas berhasil diupdate');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();

        return redirect()->route('kelas.index')
            ->with('success', 'Data kelas berhasil dihapus');
    }
}