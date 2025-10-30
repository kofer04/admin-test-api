<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/test-data', function () {
    try {
        // Check total jobs
        $total = DB::table('log_service_titan_jobs')->whereNull('deleted_at')->count();
        
        // Get date range if data exists
        if ($total > 0) {
            $dateRange = DB::table('log_service_titan_jobs')
                ->whereNull('deleted_at')
                ->selectRaw('MIN(start) as min_date, MAX(start) as max_date')
                ->first();
            
            // Get sample records
            $sample = DB::table('log_service_titan_jobs')
                ->whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'market_id', 'start', 'created_at']);
                
            return response()->json([
                'total_jobs' => $total,
                'date_range' => $dateRange,
                'sample_records' => $sample
            ]);
        }
        
        return response()->json(['total_jobs' => 0, 'message' => 'No data found']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
