<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Backup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc') === 'true' ? 'desc' : 'asc';
        $perPage = (int) $request->input('itemsPerPage', 10);
        $page = (int) $request->input('page', 1); // Tambahkan page dari request

        $query = Backup::orderBy($sortBy, $sortDesc);

        if ($perPage <= 0) {
            $data = $query->get();
            return response()->json([
                'data' => $data,
                'total' => $data->count(),
            ]);
        }

        // Gunakan parameter $page untuk paginate
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->items(), // Data untuk tabel
            'total' => $paginated->total(), // Total item secara keseluruhan
            'per_page' => $paginated->perPage(), // Jumlah item per halaman
            'current_page' => $paginated->currentPage(), // Halaman saat ini
            'last_page' => $paginated->lastPage(), // Halaman terakhir
            // Anda juga bisa mengirim 'links' jika Anda ingin membangun kontrol paginasi kustom di frontend
            // 'links' => $paginated->linkCollection(),
        ]);
    }

    public function store()
    {
        Artisan::call('db:export');

        $files = Storage::disk('local')->files('backups');
        $latestFile = collect($files)->sortByDesc(
            fn($f) => Storage::disk('local')->lastModified($f)
        )->first();

        if (!$latestFile) {
            return response()->json(['message' => 'Failed to backup database'], 500);
        }

        // Ambil hanya nama file, tanpa path "backups/"
        $filename = basename($latestFile);

        Backup::create([
            'filename' => $filename,
            'size' => Storage::disk('local')->size($latestFile),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Database backup successful',
            'filename' => $filename,
        ]);
    }

    public function download($filename)
    {
        // Ambil token dari query string jika tidak ada Authorization header
        if (!Auth::check()) {
            $token = request()->query('token');
            if ($token) {
                try {
                    JWTAuth::setToken($token)->authenticate();
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
            } else {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $path = storage_path('app/backups/' . $filename);
        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download($path);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);

        $filePath = 'backups/' . $backup->filename;

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        $backup->delete();

        return response()->json(['message' => 'The database backup was successfully deleted']);
    }

    public function checkToday()
    {
        $today = Carbon::today()->toDateString();

        $backupExists = Backup::whereDate('created_at', $today)->exists();

        return response()->json(['status' => $backupExists]);
    }
}
