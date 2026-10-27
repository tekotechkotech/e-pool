<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeatureToggleController extends Controller
{
    public function featureDisabled(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Fitur pengajuan bantuan sementara tidak tersedia',
                'feature_survey' => (bool) env('APP_FEATURE_SURVEY', 1),
                'feature_applications' => (bool) env('APP_FEATURE_APPLICATIONS', 0)
            ], 404);
        }
        
        $title = 'Fitur Pengajuan Bantuan';
        $message = 'Fitur pengajuan bantuan sosial sementara tidak tersedia.';
        $faqUrl = route('public.survey');
        
        return view('feature-toggle.disabled', compact('title', 'message', 'faqUrl'));
    }
}
