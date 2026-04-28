<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\RiwayatKelasSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::all();
        $siswaSemua = Siswa::with('kelas')
            ->orderBy('kelas_id')
            ->orderBy('nama_siswa')
            ->get();

        $query = Siswa::with('kelas');
        $sortField = $request->get('sort', 'nama_siswa');
        $sortDirection = strtolower((string) $request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSortFields = ['nis', 'nama_siswa', 'kelas', 'jenis_kelamin', 'no_hp'];

        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'nama_siswa';
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_siswa', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($sortField === 'kelas') {
            $query->orderBy(
                Kelas::select('nama_kelas')
                    ->whereColumn('kelas.id', 'siswa.kelas_id')
                    ->limit(1),
                $sortDirection
            )->orderBy('nama_siswa', 'asc');
        } else {
            $query->orderBy($sortField, $sortDirection)->orderBy('nama_siswa', 'asc');
        }

        $siswa = $query->paginate(10)->withQueryString();

        return view('siswa.index', compact('siswa', 'kelas', 'siswaSemua', 'sortField', 'sortDirection'));
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

    public function generateKenaikan(Request $request)
    {
        $validated = $request->validate([
            'kelas_baru_id' => 'required|exists:kelas,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'required|integer|exists:siswa,id',
        ]);

        $kelasBaruId = (int) $validated['kelas_baru_id'];
        $siswaTerpilih = Siswa::whereIn('id', $validated['siswa_ids'])->get(['id', 'kelas_id']);

        $siswaNaikKelas = $siswaTerpilih->filter(function ($item) use ($kelasBaruId) {
            return (int) $item->kelas_id !== $kelasBaruId;
        });

        if ($siswaNaikKelas->isEmpty()) {
            return redirect()->route('siswa.index')
                ->with('error', 'Tidak ada siswa yang diproses karena semua sudah berada di kelas tujuan.');
        }

        $now = now();
        $userId = auth()->id();
        $historyRows = $siswaNaikKelas->map(function ($item) use ($kelasBaruId, $now, $userId) {
            return [
                'siswa_id' => $item->id,
                'kelas_lama_id' => $item->kelas_id,
                'kelas_baru_id' => $kelasBaruId,
                'tanggal_pindah' => $now,
                'created_user' => $userId ? (string) $userId : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->all();

        DB::transaction(function () use ($historyRows, $kelasBaruId, $siswaNaikKelas, $now, $userId) {
            RiwayatKelasSiswa::insert($historyRows);

            Siswa::whereIn('id', $siswaNaikKelas->pluck('id'))
                ->update([
                    'kelas_id' => $kelasBaruId,
                    'updated_user' => $userId,
                    'updated_at' => $now,
                ]);
        });

        return redirect()->route('siswa.index')
            ->with('success', 'Kenaikan kelas berhasil diproses untuk ' . $siswaNaikKelas->count() . ' siswa.');
    }
}
