<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Siswa::with('kelas');

        // SEARCH
        if ($request->search) {
            $query->where('nama_siswa', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
        }

        // FILTER KELAS
        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->latest()->paginate(10);

        return response()->json($siswa);
    }

    return view('siswa.index');
}

    public function create()
    {
        $kelas = Kelas::all();
        return view('siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:siswa',
            'nama_siswa' => 'required',
            'kelas_id' => 'required',
            'jenis_kelamin' => 'required'
        ]);

        Siswa::create([
            'nis'            => $request->nis,
            'nama_siswa'     => $request->nama_siswa,
            'kelas_id'       => $request->kelas_id,
            'jenis_kelamin'  => $request->jenis_kelamin,
            'alamat'         => $request->alamat,
            'no_hp'          => $request->no_hp,
            'created_user'   => auth()->id()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Data siswa berhasil ditambahkan'
            ], 201);
        }

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan');
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::all();
        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nis' => 'required|unique:siswa,nis,' . $siswa->id,
            'nama_siswa' => 'required',
            'kelas_id' => 'required',
            'jenis_kelamin' => 'required'
        ]);

        $siswa->update([
            'nis'            => $request->nis,
            'nama_siswa'     => $request->nama_siswa,
            'kelas_id'       => $request->kelas_id,
            'jenis_kelamin'  => $request->jenis_kelamin,
            'alamat'         => $request->alamat,
            'no_hp'          => $request->no_hp,
            'updated_user'   => auth()->id()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Data siswa berhasil diupdate'
            ]);
        }

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil diupdate');
    }

    public function destroy(Request $request, Siswa $siswa)
    {
        $siswa->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Data siswa berhasil dihapus'
            ]);
        }

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil dihapus');
    }
}
