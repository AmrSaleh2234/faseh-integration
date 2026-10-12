<?php

namespace App\Http\Controllers;

use App\Services\LocalReadService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, LocalReadService $read) { return view('dashboard.index', $read->dashboard($request->user())); }
}
