<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403);
        }

        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $files = array_diff(scandir($backupPath), ['.', '..']);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => $file,
                'size' => round(filesize($backupPath . '/' . $file) / 1024, 2) . ' KB',
                'date' => date('Y-m-d H:i:s', filemtime($backupPath . '/' . $file)),
            ];
        }

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403);
        }

        // Dummy backup generation for now
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        file_put_contents($path, "-- Gourmetica Backup\n-- Date: " . date('Y-m-d H:i:s'));

        return redirect()->back()->with('success', 'Backup generado correctamente: ' . $filename);
    }
}
