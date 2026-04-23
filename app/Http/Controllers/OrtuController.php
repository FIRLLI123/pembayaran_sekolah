<?php

namespace App\Http\Controllers;

use App\Models\Ortu;
use Illuminate\Http\Request;

class OrtuController extends Controller
{
    public function index(Request $request)
    {
        $query = Ortu::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_ayah', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_ibu', 'like', '%' . $request->search . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            });
        }

        $ortu = $query->latest()->paginate(10)->withQueryString();

        return view('ortu.index', compact('ortu'));
    }

    public function create()
    {
        return view('ortu.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ayah' => 'required',
            'nama_ibu'  => 'required',
            'no_hp'     => 'nullable',
            'alamat'    => 'nullable',
        ]);

        Ortu::create([
            'nama_ayah' => $request->nama_ayah,
            'nama_ibu'  => $request->nama_ibu,
            'no_hp'     => $request->no_hp,
            'alamat'    => $request->alamat,
        ]);

        return redirect()->route('ortu.index')
            ->with('success', 'Data orang tua berhasil ditambahkan');
    }

    public function edit(Ortu $ortu)
    {
        return view('ortu.edit', compact('ortu'));
    }

    public function update(Request $request, Ortu $ortu)
    {
        $request->validate([
            'nama_ayah' => 'required',
            'nama_ibu'  => 'required',
            'no_hp'     => 'nullable',
            'alamat'    => 'nullable',
        ]);

        $ortu->update([
            'nama_ayah' => $request->nama_ayah,
            'nama_ibu'  => $request->nama_ibu,
            'no_hp'     => $request->no_hp,
            'alamat'    => $request->alamat,
        ]);

        return redirect()->route('ortu.index')
            ->with('success', 'Data orang tua berhasil diupdate');
    }

    public function destroy(Request $request, Ortu $ortu)
    {
        $ortu->delete();

        return redirect()->route('ortu.index')
            ->with('success', 'Data orang tua berhasil dihapus');
    }
}