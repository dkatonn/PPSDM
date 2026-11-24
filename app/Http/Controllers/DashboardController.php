<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ptn;
use App\Models\Upt;
use App\Models\Kerjasama;
use App\Models\DokumenKerjasama;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $yearExpression = DB::getDriverName() === 'sqlite' ? "strftime('%Y', starts_at)" : 'YEAR(starts_at)';
        return view('dashboard', [
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'adminUsers' => User::query()->where('role', 'admin')->where('is_active', true)->count(),
            'ptnCount' => Ptn::query()->count(),
            'partnerPtnCount' => Ptn::query()->whereHas('kerjasamas', fn ($q) => $q->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today())))->count(),
            'uptCount' => Upt::query()->count(),
            'activeAgreements' => Kerjasama::query()->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))->count(),
            'pksCount' => DokumenKerjasama::query()->where('type', 'pks')->count(),
            'iaCount' => DokumenKerjasama::query()->where('type', 'ia')->count(),
            'recentDocuments' => DokumenKerjasama::query()->with('kerjasama.ptn')->latest()->limit(8)->get(),
            'endingSoon' => Kerjasama::with('ptn')->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->whereBetween('ends_at', [today(), today()->addMonths((int) config('sipena.reminder_months', 6))])->orderBy('ends_at')->limit(5)->get(),
            'recentActivity' => AuditLog::with('user')->latest()->limit(8)->get(),
            'provinceStats' => Ptn::query()->whereHas('kerjasamas', fn ($q) => $q->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today())))->selectRaw('province, count(*) as total')->groupBy('province')->orderByDesc('total')->limit(8)->get(),
            'yearStats' => Kerjasama::query()->selectRaw($yearExpression.' as year, count(*) as total')->whereNotNull('starts_at')->groupBy('year')->orderBy('year')->limit(8)->get(),
        ]);
    }
}
