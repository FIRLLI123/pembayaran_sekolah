<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use Illuminate\Http\Request;

class JenisPembayaranController extends Controller
{
    public function index()
    {
        $data = JenisPembayaran::latest()->get();
        return view('jenis_pembayaran.index', compact('data'));
    }

    public function create()
    {
        return view('jenis_pembayaran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pembayaran' => 'required',
            'nominal_default' => 'required|numeric'
        ]);

        JenisPembayaran::create([
            'nama_pembayaran' => $request->nama_pembayaran,
            'nominal_default' => $request->nominal_default,
            'keterangan'      => $request->keterangan,
            'created_user'    => auth()->id()
        ]);

        return redirect()->route('jenis-pembayaran.index')
            ->with('success', 'Jenis pembayaran berhasil ditambahkan');
    }

    public function edit(JenisPembayaran $jenis_pembayaran)
    {
        return view('jenis_pembayaran.edit', compact('jenis_pembayaran'));
    }

    public function update(Request $request, JenisPembayaran $jenis_pembayaran)
    {
        $request->validate([
            'nama_pembayaran' => 'required',
            'nominal_default' => 'required|numeric'
        ]);

        $jenis_pembayaran->update([
            'nama_pembayaran' => $request->nama_pembayaran,
            'nominal_default' => $request->nominal_default,
            'keterangan'      => $request->keterangan,
            'updated_user'    => auth()->id()
        ]);

        return redirect()->route('jenis-pembayaran.index')
            ->with('success', 'Jenis pembayaran berhasil diupdate');
    }

    public function destroy(JenisPembayaran $jenis_pembayaran)
    {
        $jenis_pembayaran->delete();

        return redirect()->route('jenis-pembayaran.index')
            ->with('success', 'Jenis pembayaran berhasil dihapus');
    }
}