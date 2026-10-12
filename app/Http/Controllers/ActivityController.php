<?php

namespace App\Http\Controllers;

use App\Services\LocalReadService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request, LocalReadService $read) { return view('settings.activity', ['activities' => $read->activities($request)]); }
}
