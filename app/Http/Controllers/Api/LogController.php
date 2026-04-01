<?php
// app/Http/Controllers/Api/LogController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Barryvdh\DomPDF\Facade\Pdf;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->search, fn($q) => $q->where('description', 'like', '%' . $request->search . '%'));

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc') === 'true' ? 'desc' : 'asc';
        $perPage = (int) $request->input('itemsPerPage', 10);
        $page = (int) $request->input('page', 1); // Tambahkan page dari request

        $query->orderBy($sortBy, $sortDesc);

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

    public function export(Request $request)
    {
        $filename = 'activity_logs_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new \App\Exports\ActivityLogExport($request->start_date, $request->end_date, $request->search),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('module', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.logs-pdf', compact('logs'));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="activity_logs.pdf"',
        ]);
    }

    public function print(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    now()->parse($request->start_date)->startOfDay(),
                    now()->parse($request->end_date)->endOfDay()
                ]);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('module', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->get();

        return response()->view('exports.logs-print', ['logs' => $logs]);
    }
}
