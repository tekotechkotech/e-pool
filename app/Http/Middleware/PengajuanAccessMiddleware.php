<?php

namespace App\Http\Middleware;

use App\Models\PengajuanBantuan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PengajuanAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        $pengajuan = null;
        if ($action !== 'people-search') {
            $pengajuanId = $request->route('pengajuan') ?? $request->route('pengajuanId') ?? $request->route('id');
            if ($pengajuanId instanceof PengajuanBantuan) {
                $pengajuan = $pengajuanId;
            } elseif ($pengajuanId !== null && is_numeric($pengajuanId)) {
                $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
            }
        }

        if ($pengajuan) {
            abort_unless($user->canAccessPengajuan($pengajuan), 404);
        }

        abort_unless($user->canPerformPengajuanAction($action, $pengajuan), 403);

        return $next($request);
    }
}
