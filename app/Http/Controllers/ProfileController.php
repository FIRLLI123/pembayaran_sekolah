<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function ubahPassword(Request $request)
{
    $request->validate([
        'password_lama' => 'required',
        'password_baru' => 'required|min:6|confirmed',
    ], [
        'password_lama.required'  => 'Password lama wajib diisi.',
        'password_baru.required'  => 'Password baru wajib diisi.',
        'password_baru.min'       => 'Password baru minimal 6 karakter.',
        'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    $user = auth()->user();

    if (!Hash::check($request->password_lama, $user->password)) {
        return back()
            ->withErrors(['password_lama' => 'Password lama tidak sesuai.'])
            ->withInput()
            ->with('modal_password', true);
    }

    // Karena ada cast 'hashed', Laravel otomatis hash saat disimpan
    $user->update([
    'password'     => Hash::make($request->password_baru),
    'updated_user' => auth()->id(),
]);

    return back()->with('success_password', 'Password berhasil diubah.');
}

    public function ubahInformasi(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email,' . auth()->id(),
            'nama_ayah' => 'nullable',
            'nama_ibu'  => 'nullable',
            'no_hp'     => 'nullable',
            'alamat'    => 'nullable',
        ]);

        $user = auth()->user();

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'updated_user'  => auth()->id(),
        ]);

        // Update data ortu jika ada
        if ($user->ortu) {
            $user->ortu->update([
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu'  => $request->nama_ibu,
                'no_hp'     => $request->no_hp,
                'alamat'    => $request->alamat,
            ]);
        }

        return back()->with('success_informasi', 'Informasi berhasil diperbarui.');
    }
}