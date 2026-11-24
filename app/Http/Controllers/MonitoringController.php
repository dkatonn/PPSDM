<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        $base = Kerjasama::with('ptn')->withCount('implementasis');
        return view('monitoring.index', [
            'endingSoon' => (clone $base)->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->whereBetween('ends_at', [today(), today()->addMonths((int) config('sipena.reminder_months', 6))])->orderBy('ends_at')->paginate(15, ['*'], 'ending'),
            'ended' => (clone $base)->where(fn ($q) => $q->where('status', 'berakhir')->orWhere(fn ($q) => $q->whereNotNull('ends_at')->whereDate('ends_at', '<', today())))->orderByDesc('ends_at')->paginate(15, ['*'], 'ended'),
            'withoutImplementation' => (clone $base)->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->has('implementasis', '=', 0)->orderBy('ends_at')->paginate(15, ['*'], 'without'),
            'reminderMonths' => (int) config('sipena.reminder_months', 6),
        ]);
    }
}
