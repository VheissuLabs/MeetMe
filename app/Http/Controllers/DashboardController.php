<?php

namespace App\Http\Controllers;

use App\Actions\GenerateQrCode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GenerateQrCode $qrCode): Response
    {
        $user = $request->user();
        $meetUrl = url('/meet/'.$user->qr_token);

        return Inertia::render('Dashboard', [
            'meetUrl' => $meetUrl,
            'qrSvg' => $qrCode->svg($meetUrl),
            'needsSocials' => blank($user->x_username) && blank($user->bluesky_handle),
        ]);
    }
}
