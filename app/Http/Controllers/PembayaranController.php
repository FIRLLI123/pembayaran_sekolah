<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayaran = Pembayaran::with(['siswa', 'jenisPembayaran'])
            ->orderByDesc('tanggal_bayar')
            ->get();

        return view('pembayaran.index', compact('pembayaran'));
    }
}
