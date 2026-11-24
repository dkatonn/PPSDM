<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(): View
    {
        return view('notifications.index', [
            'items' => Notifikasi::with('kerjasama.ptn')->where('user_id', auth()->id())->latest()->paginate(20),
            'unreadCount' => Notifikasi::where('user_id', auth()->id())->whereNull('read_at')->count(),
        ]);
    }

    public function read(Notifikasi $notifikasi): RedirectResponse
    {
        abort_unless($notifikasi->user_id === auth()->id(), 404);
        $notifikasi->update(['read_at' => now()]);
        return redirect()->route('kerjasama.show', $notifikasi->kerjasama_id);
    }
}
