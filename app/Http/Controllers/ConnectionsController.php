<?php

namespace App\Http\Controllers;

use App\Actions\BuildConnections;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConnectionsController extends Controller
{
    public function __invoke(Request $request, BuildConnections $buildConnections): Response
    {
        return Inertia::render('Connections', [
            'connections' => $buildConnections->for($request->user()),
        ]);
    }
}
