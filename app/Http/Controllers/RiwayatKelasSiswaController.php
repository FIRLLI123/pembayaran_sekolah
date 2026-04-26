<?php

namespace App\Http\Controllers;

use App\Models\RiwayatKelasSiswa;
use Illuminate\Http\Request;

class RiwayatKelasSiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatKelasSiswa::with(['siswa', 'kelasLama', 'kelasBaru']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%');
            });
        }

        $riwayat = $query
            ->orderByDesc('tanggal_pindah')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('information.riwayat-siswa.index', compact('riwayat'));
    }
}
