<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Prometheus metrics endpoint.
 * Returns metrics in Prometheus text format for scraping.
 */
class MetricsController extends Controller
{
    /**
     * GET /metrics
     * 
     * Returns application metrics in Prometheus text format.
     * This endpoint does not require authentication (Prometheus scraper needs access).
     */
    public function index(): Response
    {
        $metrics = [];

        // Application info
        $metrics[] = '# HELP media_tracker_info Application information';
        $metrics[] = '# TYPE media_tracker_info gauge';
        $metrics[] = 'media_tracker_info{version="1.0.0",laravel_version="' . app()->version() . '"} 1';

        // Total users
        try {
            $totalUsers = User::count();
            $metrics[] = '# HELP media_tracker_users_total Total number of registered users';
            $metrics[] = '# TYPE media_tracker_users_total gauge';
            $metrics[] = "media_tracker_users_total $totalUsers";
        } catch (\Exception $e) {
            // Skip if DB error
        }

        // Total media entries
        try {
            $totalMedia = Media::count();
            $metrics[] = '# HELP media_tracker_media_total Total number of media entries';
            $metrics[] = '# TYPE media_tracker_media_total gauge';
            $metrics[] = "media_tracker_media_total $totalMedia";
        } catch (\Exception $e) {
            // Skip if DB error
        }

        // Media by type
        try {
            $mediaByType = Media::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get();

            $metrics[] = '# HELP media_tracker_media_by_type Number of media entries by type';
            $metrics[] = '# TYPE media_tracker_media_by_type gauge';
            foreach ($mediaByType as $item) {
                $metrics[] = 'media_tracker_media_by_type{type="' . $item->type . '"} ' . $item->count;
            }
        } catch (\Exception $e) {
            // Skip if DB error
        }

        // Media by status
        try {
            $mediaByStatus = Media::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            $metrics[] = '# HELP media_tracker_media_by_status Number of media entries by status';
            $metrics[] = '# TYPE media_tracker_media_by_status gauge';
            foreach ($mediaByStatus as $item) {
                $metrics[] = 'media_tracker_media_by_status{status="' . $item->status . '"} ' . $item->count;
            }
        } catch (\Exception $e) {
            // Skip if DB error
        }

        // Database health (1 = healthy, 0 = unhealthy)
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbHealth = 1;
        } catch (\Exception $e) {
            $dbHealth = 0;
        }

        $metrics[] = '# HELP media_tracker_database_up Database connection status (1 = up, 0 = down)';
        $metrics[] = '# TYPE media_tracker_database_up gauge';
        $metrics[] = "media_tracker_database_up $dbHealth";

        $output = implode("\n", $metrics) . "\n";

        return response($output, 200)
            ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    }
}
