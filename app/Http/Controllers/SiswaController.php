<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::all();

        $query = Siswa::with('kelas');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_siswa', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->latest()->paginate(10)->withQueryString();

        return view('siswa.index', compact('siswa', 'kelas'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        return view('siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis'           => 'required|unique:siswa',
            'nama_siswa'    => 'required',
            'kelas_id'      => 'required',
            'jenis_kelamin' => 'required',
            'upload_foto'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('upload_foto')) {
            $fotoPath = $request->file('upload_foto')->store('foto_siswa', 'public');
        }

        Siswa::create([
            'nis'           => $request->nis,
            'nama_siswa'    => $request->nama_siswa,
            'kelas_id'      => $request->kelas_id,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat'        => $request->alamat,
            'no_hp'         => $request->no_hp,
            'upload_foto'   => $fotoPath,
            'created_user'  => auth()->id()
        ]);

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
            'nis'           => 'required|unique:siswa,nis,' . $siswa->id,
            'nama_siswa'    => 'required',
            'kelas_id'      => 'required',
            'jenis_kelamin' => 'required',
            'upload_foto'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoPath = $siswa->upload_foto;
        if ($request->hasFile('upload_foto')) {
            // Hapus foto lama jika ada
            if ($siswa->upload_foto) {
                Storage::disk('public')->delete($siswa->upload_foto);
            }
            $fotoPath = $request->file('upload_foto')->store('foto_siswa', 'public');
        }

        $siswa->update([
            'nis'           => $request->nis,
            'nama_siswa'    => $request->nama_siswa,
            'kelas_id'      => $request->kelas_id,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat'        => $request->alamat,
            'no_hp'         => $request->no_hp,
            'upload_foto'   => $fotoPath,
            'updated_user'  => auth()->id()
        ]);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil diupdate');
    }

    public function destroy(Request $request, Siswa $siswa)
    {
        // Hapus foto saat data dihapus
        if ($siswa->upload_foto) {
            Storage::disk('public')->delete($siswa->upload_foto);
        }

        $siswa->delete();

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil dihapus');
    }


    public function updateFoto(Request $request)
{
    $request->validate([
        'upload_foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $user = auth()->user();
    $siswa = Siswa::find($user->siswa_id);

    if (!$siswa) {
        return back()->with('error', 'Data siswa tidak ditemukan.');
    }

    // Hapus foto lama
    if ($siswa->upload_foto) {
        Storage::disk('public')->delete($siswa->upload_foto);
    }

    $path = $request->file('upload_foto')->store('foto_siswa', 'public');
    $siswa->update(['upload_foto' => $path]);

    return back()->with('success', 'Foto profil berhasil diperbarui.');
}
}