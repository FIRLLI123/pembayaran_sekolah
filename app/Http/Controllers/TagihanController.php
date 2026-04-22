<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{


public function index(Request $request)
{
    if (auth()->check() && auth()->user()->role === 'ortu') {
        return redirect()->route('ortu.riwayat');
    }

    $query = \App\Models\Tagihan::with(['siswa', 'jenisPembayaran'])
        ->select('siswa_id',
            DB::raw('SUM(nominal_tagihan) as total_nominal'),
            DB::raw('SUM(sisa_tagihan) as total_sisa')
        )
        ->groupBy('siswa_id');

    // filter nama siswa
    if ($request->filled('nama_siswa')) {
        $namaSiswa = trim($request->nama_siswa);
        $query->whereHas('siswa', function ($q) use ($namaSiswa) {
            $q->where('nama_siswa', 'like', '%' . $namaSiswa . '%');
        });
    }

    $tagihan = $query->latest()->paginate(10)->withQueryString();
    $siswa   = Siswa::all();

    return view('tagihan.index', compact('tagihan', 'siswa'));
}

public function detail(Request $request, $siswaId)
{
    if (auth()->check() && auth()->user()->role === 'ortu' && (int) auth()->user()->siswa_id !== (int) $siswaId) {
        abort(403, 'Akses ditolak');
    }

    $siswa = Siswa::findOrFail($siswaId);

    $query = Tagihan::with(['jenisPembayaran'])
        ->where('siswa_id', $siswaId);

    if ($request->status) {
        $query->where('status', $request->status);
    }
    if ($request->bulan) {
        $query->where('periode_bulan', $request->bulan);
    }
    if ($request->tahun) {
        $query->where('periode_tahun', $request->tahun);
    }

    $detail = $query->orderBy('periode_tahun')
                    ->orderBy('periode_bulan')
                    ->paginate(10);

    return view('tagihan.detail', compact('siswa', 'detail'));
}
    //

    public function generateSPPlangsung()
{
    $bulan = Carbon::now()->month;
    $tahun = Carbon::now()->year;

    // ambil jenis pembayaran SPP (rutin bulanan)
    $jenisSPP = JenisPembayaran::where('tipe', 'rutin')
        ->where('periode', 'bulanan')
        ->get();

    // ambil semua siswa
    $siswaList = Siswa::all();

    foreach ($siswaList as $siswa) {

        foreach ($jenisSPP as $jenis) {

            // 🚫 CEK BIAR TIDAK DOUBLE
            $sudahAda = Tagihan::where('siswa_id', $siswa->id)
                ->where('jenis_pembayaran_id', $jenis->id)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->exists();

            if ($sudahAda) {
                continue;
            }

            // ✅ BUAT TAGIHAN
            Tagihan::create([
                'siswa_id' => $siswa->id,
                'jenis_pembayaran_id' => $jenis->id,
                'tanggal_tagihan' => now(),
                'jatuh_tempo' => now()->addDays(10),

                'periode_bulan' => $bulan,
                'periode_tahun' => $tahun,

                'nominal_tagihan' => $jenis->nominal_default,
                'sisa_tagihan' => $jenis->nominal_default,

                'status' => 'belum_bayar',

                'created_user' => Auth::user()->name ?? 'system'
            ]);
        }
    }

    return redirect()->back()->with('success', 'Tagihan SPP berhasil digenerate!');
}




public function bayar(Request $request, $id)
{
    if (auth()->check() && auth()->user()->role === 'ortu') {
        abort(403, 'Akses ditolak');
    }

    // dd($request->all());
    $request->validate([
        'nominal_bayar' => 'required|numeric|min:1',
        'metode_bayar' => 'required|in:cash,transfer',
        'keterangan' => 'nullable|string'
    ]);

    DB::beginTransaction();

    try {
        $tagihan = Tagihan::findOrFail($id);

        // ❌ Validasi: tidak boleh lebih dari sisa
        if ($request->nominal_bayar > $tagihan->sisa_tagihan) {
            return back()->with('error', 'Nominal melebihi sisa tagihan!');
        }

        // ✅ Simpan ke tabel pembayaran
        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'siswa_id' => $tagihan->siswa_id,
            'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
            'tanggal_bayar' => now(),
            'nominal_bayar' => $request->nominal_bayar,
            'metode_bayar' => $request->metode_bayar,
            'status' => 'lunas', // status transaksi, bukan tagihan
            'keterangan' => $request->keterangan,
            'created_user' => auth()->user()->name ?? 'admin'
        ]);

        // 🔄 Update tagihan
        $tagihan->sisa_tagihan -= $request->nominal_bayar;

        if ($tagihan->sisa_tagihan == 0) {
            $tagihan->status = 'lunas';
        } else {
            $tagihan->status = 'cicil';
        }

        $tagihan->save();

        DB::commit();

       return redirect()->back()->with('success', 'Pembayaran berhasil!');
} catch (\Exception $e) {
    return redirect()->back()->with('error', 'Pembayaran gagal!');
}
}



public function multiBayar(Request $request, $siswaId)
{
    if (auth()->check() && auth()->user()->role === 'ortu') {
        abort(403, 'Akses ditolak');
    }

    // 🔍 Validasi
    $request->validate([
        'total_bayar' => 'required|numeric|min:1',
        'metode_bayar' => 'required|in:cash,transfer',
    ]);

    // 🔥 Ambil nominal (pastikan bersih)
    $totalBayar = (int) str_replace('.', '', $request->total_bayar);

    DB::beginTransaction();

    try {

        // 🔎 Ambil semua tagihan BELUM LUNAS
        $tagihanList = Tagihan::where('siswa_id', $siswaId)
            ->where('status', '!=', 'lunas')
            ->orderBy('periode_tahun')
            ->orderBy('periode_bulan')
            ->get();

        foreach ($tagihanList as $tagihan) {

            // kalau uang habis → stop
            if ($totalBayar <= 0) break;

            $sisa = $tagihan->sisa_tagihan;

            // =========================
            // 💰 KASUS 1: LUNAS
            // =========================
            if ($totalBayar >= $sisa) {

                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'siswa_id' => $siswaId,
                    'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
                    'tanggal_bayar' => now(),
                    'nominal_bayar' => $sisa,
                    'metode_bayar' => $request->metode_bayar,
                    'status' => 'lunas',
                    'keterangan' => 'Multi bayar',
                    'created_user' => auth()->user()->name ?? 'admin'
                ]);

                $tagihan->update([
                    'sisa_tagihan' => 0,
                    'status' => 'lunas'
                ]);

                $totalBayar -= $sisa;

            }

            // =========================
            // 💰 KASUS 2: CICIL
            // =========================
            else {

                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'siswa_id' => $siswaId,
                    'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
                    'tanggal_bayar' => now(),
                    'nominal_bayar' => $totalBayar,
                    'metode_bayar' => $request->metode_bayar,
                    'status' => 'cicil',
                    'keterangan' => 'Multi bayar',
                    'created_user' => auth()->user()->name ?? 'admin'
                ]);

                $tagihan->update([
                    'sisa_tagihan' => $sisa - $totalBayar,
                    'status' => 'cicil'
                ]);

                $totalBayar = 0;
            }
        }

        DB::commit();

        return back()->with('success', 'Multi pembayaran berhasil!');

    } catch (\Exception $e) {
        DB::rollBack();

        // 🔥 DEBUG MODE (hapus kalau sudah aman)
        // dd($e->getMessage());

        return back()->with('error', 'Multi pembayaran gagal!');
    }
}

public function totalBelumLunas($siswaId)
{
    if (auth()->check() && auth()->user()->role === 'ortu' && (int) auth()->user()->siswa_id !== (int) $siswaId) {
        abort(403, 'Akses ditolak');
    }

    $total = Tagihan::where('siswa_id', $siswaId)
        ->where('status', '!=', 'lunas')
        ->sum('sisa_tagihan');

    return response()->json(['total' => $total]);
}

public function statusBulan($tahun)
{
    $result = [];

    for ($i = 1; $i <= 12; $i++) {
        // cek apakah semua siswa sudah punya tagihan di bulan ini
        $jumlahSiswa   = Siswa::count();
        $jumlahTagihan = Tagihan::where('periode_bulan', $i)
            ->where('periode_tahun', $tahun)
            ->distinct('siswa_id')
            ->count('siswa_id');

        $result[$i] = $jumlahTagihan >= $jumlahSiswa && $jumlahSiswa > 0;
    }

    return response()->json($result);
}

public function generateSPP(Request $request)
{
    $request->validate([
        'bulan'   => 'required|array|min:1',
        'bulan.*' => 'integer|between:1,12',
        'tahun'   => 'required|integer',
    ]);

    $tahun     = $request->tahun;
    $bulanList = $request->bulan;

    $jenisSPP  = JenisPembayaran::where('tipe', 'rutin')
                    ->where('periode', 'bulanan')->get();
    $siswaList = Siswa::all();

    $sudahAda  = []; // bulan yang skip
    $berhasil  = []; // bulan yang berhasil di-generate

    foreach ($bulanList as $bulan) {

        $semuaSudahAda = true;

        foreach ($siswaList as $siswa) {
            foreach ($jenisSPP as $jenis) {

                $exists = Tagihan::where('siswa_id', $siswa->id)
                    ->where('jenis_pembayaran_id', $jenis->id)
                    ->where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->exists();

                if (!$exists) {
                    $semuaSudahAda = false;

                    Tagihan::create([
                        'siswa_id'            => $siswa->id,
                        'jenis_pembayaran_id' => $jenis->id,
                        'tanggal_tagihan'     => now(),
                        'jatuh_tempo'         => now()->addDays(10),
                        'periode_bulan'       => $bulan,
                        'periode_tahun'       => $tahun,
                        'nominal_tagihan'     => $jenis->nominal_default,
                        'sisa_tagihan'        => $jenis->nominal_default,
                        'status'              => 'belum_bayar',
                        'created_user'        => Auth::user()->name ?? 'system',
                    ]);
                }
            }
        }

        $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');

        if ($semuaSudahAda) {
            $sudahAda[] = $namaBulan;
        } else {
            $berhasil[] = $namaBulan;
        }
    }

    // ── susun pesan ──────────────────────────────────────
    if (empty($berhasil) && !empty($sudahAda)) {
        // semua yang dipilih sudah ter-generate
        $daftarSkip = implode(', ', $sudahAda);
        $pesan = "Semua bulan yang dipilih ({$daftarSkip}) sudah pernah di-generate sebelumnya. Tidak ada tagihan baru yang dibuat.";
        return redirect()->back()->with('info', $pesan);
    }

    $daftarBaru  = implode(', ', $berhasil);
    $pesan = "Tagihan SPP {$tahun} berhasil di-generate untuk: {$daftarBaru}.";

    if (!empty($sudahAda)) {
        $daftarSkip = implode(', ', $sudahAda);
        $pesan .= " (Dilewati karena sudah ada: {$daftarSkip})";
    }

    return redirect()->back()->with('success', $pesan);
}


public function detailAjax($siswaId)
{
    if (auth()->check() && auth()->user()->role === 'ortu' && (int) auth()->user()->siswa_id !== (int) $siswaId) {
        abort(403, 'Akses ditolak');
    }

    $detail = Tagihan::with(['jenisPembayaran'])
        ->where('siswa_id', $siswaId)
        ->orderBy('periode_tahun')
        ->orderBy('periode_bulan')
        ->get();

    return response()->json($detail);
}

public function ortuRiwayat(Request $request)
{
    $user = auth()->user();

    if (!$user || $user->role !== 'ortu') {
        abort(403, 'Akses ditolak');
    }

    if (!$user->siswa_id) {
        return redirect()->route('dashboard')->with('error', 'Akun ortu belum terhubung ke data siswa.');
    }

    $siswa = Siswa::with('kelas')->findOrFail($user->siswa_id);

    $pembayaranQuery = Pembayaran::with(['jenisPembayaran', 'tagihan'])
        ->where('siswa_id', $user->siswa_id);

    if ($request->filled('tanggal_mulai')) {
        $pembayaranQuery->whereDate('tanggal_bayar', '>=', $request->tanggal_mulai);
    }

    if ($request->filled('tanggal_selesai')) {
        $pembayaranQuery->whereDate('tanggal_bayar', '<=', $request->tanggal_selesai);
    }

    if ($request->filled('jenis_pembayaran_id')) {
        $pembayaranQuery->where('jenis_pembayaran_id', $request->jenis_pembayaran_id);
    }

    $pembayaran = $pembayaranQuery
        ->orderByDesc('tanggal_bayar')
        ->paginate(8, ['*'], 'pembayaran_page')
        ->withQueryString();

    $tagihan = Tagihan::with('jenisPembayaran')
        ->where('siswa_id', $user->siswa_id)
        ->orderByDesc('periode_tahun')
        ->orderByDesc('periode_bulan')
        ->orderByDesc('id')
        ->paginate(10, ['*'], 'tagihan_page')
        ->withQueryString();

    $jenisPembayaran = JenisPembayaran::orderBy('nama_pembayaran')
        ->get(['id', 'nama_pembayaran']);

    return view('ortu.riwayat', compact('siswa', 'pembayaran', 'tagihan', 'jenisPembayaran'));
}


public function createCustom()
{
    $jenis = JenisPembayaran::where('tipe', '!=', 'rutin')->get();

    $kelas = \App\Models\Kelas::all(); // kalau ada tabel kelas
    $siswa = Siswa::with('kelas')->get();

    return view('tagihan.custom', compact('jenis', 'kelas', 'siswa'));
}


public function storeCustom(Request $request)
{
    $request->validate([
        'jenis_pembayaran_id' => 'required|exists:jenis_pembayaran,id',
        'siswa_id' => 'required|array|min:1',
    ]);

    $jenis = JenisPembayaran::findOrFail($request->jenis_pembayaran_id);

    $bulan = now()->month;
    $tahun = now()->year;

    $force = $request->has('force');

    $siswaList = Siswa::whereIn('id', $request->siswa_id)
        ->pluck('nama_siswa', 'id');

    $sudahAda = [];

    foreach ($request->siswa_id as $siswaId) {

        $exists = Tagihan::where('siswa_id', $siswaId)
            ->where('jenis_pembayaran_id', $jenis->id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->exists();

        if ($exists) {
            $sudahAda[] = $siswaList[$siswaId] ?? 'Unknown';
        }
    }

    if (!empty($sudahAda) && !$force) {
        return redirect()->route('tagihan.custom')
    ->with('warning_generate', [
        'message' => 'Beberapa siswa sudah memiliki tagihan ini di bulan ini.',
        'list' => $sudahAda,
        'old_input' => $request->all()
    ]);
    }

    DB::beginTransaction();

    try {

        foreach ($request->siswa_id as $siswaId) {

            Tagihan::create([
                'siswa_id' => $siswaId,
                'jenis_pembayaran_id' => $jenis->id,
                'tanggal_tagihan' => now(),
                'jatuh_tempo' => $request->jatuh_tempo ?? now()->addDays(7),
                'periode_bulan' => $bulan,
                'periode_tahun' => $tahun,
                'nominal_tagihan' => $request->nominal_custom ?? $jenis->nominal_default,
                'sisa_tagihan' => $request->nominal_custom ?? $jenis->nominal_default,
                'status' => 'belum_bayar',
                'created_user' => auth()->user()->name ?? 'admin',
            ]);
        }

        DB::commit();

        return redirect()->route('tagihan.custom')
            ->with('success', 'Tagihan custom berhasil dibuat!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal generate tagihan!');
    }
}

}
