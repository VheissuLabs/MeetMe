<?php

namespace App\Http\Controllers;

use App\Actions\GenerateQrCode;
use App\Enums\Achievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GenerateQrCode $qrCode): Response
    {
        $user = $request->user();
        $meetUrl = url('/meet/'.$user->qr_token);
        $earned = $user->achievements()->get()->pluck('achievement')->all();

        return Inertia::render('Dashboard', [
            'meetUrl' => $meetUrl,
            'qrSvg' => $qrCode->svg(config('meetme.demo_qr_url') ?: $meetUrl),
            'needsSocials' => blank($user->x_username) && blank($user->bluesky_handle),
            'achievements' => array_map(fn (Achievement $achievement): array => [
                'key' => $achievement->value,
                'name' => $achievement->label(),
                'description' => $achievement->description(),
                'icon' => $achievement->icon(),
                'earned' => in_array($achievement, $earned, true),
            ], Achievement::cases()),
        ]);
    }
}
