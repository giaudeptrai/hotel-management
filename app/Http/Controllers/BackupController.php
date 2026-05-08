<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class BackupController extends Controller
{
    
    protected $disk = 'local';

    public function index()
    {
        $backupName = config('backup.backup.name');
        $disk = Storage::disk($this->disk);

        
        $files = $disk->exists($backupName) ? $disk->files($backupName) : [];

        $backups = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'name' => str_replace($backupName . '/', '', $file),
                    'path' => $file,
                    'size' => round($disk->size($file) / 1048576, 2) . ' MB',
                    'date' => Carbon::createFromTimestamp($disk->lastModified($file))->format('Y-m-d H:i:s'),
                ];
            }
        }

        
        return Inertia::render('Admin/Backup/Index', [
            'backups' => array_reverse($backups)
        ]);
    }

    public function create()
    {
        try {
            
            Artisan::call('backup:run', ['--only-db' => true]);
            return redirect()->back()->with('success', 'Đã sao lưu Database thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi sao lưu: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $path = $request->input('path');
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->download($path);
        }
        return redirect()->back()->with('error', 'Không tìm thấy file sao lưu!');
    }

    public function destroy(Request $request)
    {
        $path = $request->input('path');
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
            return redirect()->back()->with('success', 'Đã xóa bản sao lưu thành công!');
        }
        return redirect()->back()->with('error', 'Không tìm thấy file!');
    }
}
