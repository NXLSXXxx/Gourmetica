<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403);
        }

        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            $logs = array_reverse(explode("\n", $content));
            $logs = array_slice(array_filter($logs), 0, 100); // Last 100 lines
        }

        return view('admin.logs.index', compact('logs'));
    }
}
