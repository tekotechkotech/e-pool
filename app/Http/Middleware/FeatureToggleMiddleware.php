<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FeatureToggleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (env('APP_FEATURE_APPLICATIONS') == 0) {
            if ($request->is('survey/*') || $request->is('api/survey/*') || $request->is('public/survey/*')) {
                return $next($request);
            }
            
            if ($request->is('pengajuan/*') || $request->is('api/pengajuan/*')) {
                $targetUrl = route('feature.disabled');
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Fitur pengajuan bantuan sementara tidak tersedia',
                        'redirect_url' => $targetUrl
                    ], 403);
                }
                return redirect()->to($targetUrl);
            }
        }
        
        return $next($request);
    }
}
