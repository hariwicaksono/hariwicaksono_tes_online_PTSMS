<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'product_id' => 'nullable|integer'
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $productId = $request->product_id ?? null;

        $data = DB::select(
            'CALL sp_report_purchases(?, ?, ?)',
            [$startDate, $endDate, $productId]
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}