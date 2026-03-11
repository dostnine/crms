<?php

namespace App\Http\Controllers;

use App\Models\CSFForm;
use App\Models\CustomerAttributeRating;
use App\Models\psto;
use App\Models\Region;
use App\Models\Services;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $ratingFilter = $request->query('rating_filter', 'all');
        $dateFilter = $request->query('date_filter', 'all');

        if (!in_array($ratingFilter, ['all', 'positive', 'neutral', 'negative'], true)) {
            $ratingFilter = 'all';
        }

        if (!in_array($dateFilter, ['all', 'today', 'week', 'month', 'year'], true)) {
            $dateFilter = 'all';
        }

        // Build date range query
        $dateRange = null;
        if ($dateFilter === 'today') {
            $dateRange = Carbon::today();
        } elseif ($dateFilter === 'week') {
            $dateRange = Carbon::now()->startOfWeek();
        } elseif ($dateFilter === 'month') {
            $dateRange = Carbon::now()->startOfMonth();
        } elseif ($dateFilter === 'year') {
            $dateRange = Carbon::now()->startOfYear();
        }

        // Base queries with optional date filtering
        $formsQuery = CSFForm::query();
        $ratingsQuery = CustomerAttributeRating::query();
        
        if ($dateRange) {
            $formsQuery->where('created_at', '>=', $dateRange);
            $ratingsQuery->where('created_at', '>=', $dateRange);
        }

        $totalSurveys = (clone $formsQuery)->count();
        $activeUsers = User::count();

        $totalRespondents = (clone $ratingsQuery)->distinct('customer_id')->count('customer_id');
        $totalSatisfiedRespondents = (clone $ratingsQuery)->where('rate_score', '>', 3)
            ->distinct('customer_id')
            ->count('customer_id');
        $satisfactionRate = $totalRespondents > 0 ? ($totalSatisfiedRespondents / $totalRespondents) * 100 : 0;

        // Pending reviews query
        $pendingQuery = DB::table('c_s_f_forms as f')
            ->leftJoin('customer_recommendation_ratings as r', 'f.customer_id', '=', 'r.customer_id')
            ->whereNull('r.id');
        if ($dateRange) {
            $pendingQuery->where('f.created_at', '>=', $dateRange);
        }
        $pendingReviews = $pendingQuery->distinct('f.customer_id')->count('f.customer_id');

        // Rating distribution
        $ratingsFilteredQuery = CustomerAttributeRating::query();
        if ($dateRange) {
            $ratingsFilteredQuery->where('created_at', '>=', $dateRange);
        }
        
        if ($ratingFilter === 'positive') {
            $ratingsFilteredQuery->whereIn('rate_score', [4, 5]);
        } elseif ($ratingFilter === 'neutral') {
            $ratingsFilteredQuery->where('rate_score', 3);
        } elseif ($ratingFilter === 'negative') {
            $ratingsFilteredQuery->whereIn('rate_score', [1, 2]);
        }

        $totalRatings = (clone $ratingsFilteredQuery)->count();
        $verySatisfied = (clone $ratingsFilteredQuery)->where('rate_score', 5)->count();
        $satisfied = (clone $ratingsFilteredQuery)->where('rate_score', 4)->count();
        $neutral = (clone $ratingsFilteredQuery)->where('rate_score', 3)->count();
        $dissatisfied = (clone $ratingsFilteredQuery)->whereIn('rate_score', [1, 2])->count();

        // Get distribution for ALL ratings (for pie chart) with date filter only
        $allRatingsQuery = CustomerAttributeRating::query();
        if ($dateRange) {
            $allRatingsQuery->where('created_at', '>=', $dateRange);
        }
        
        $allTotalRatings = (clone $allRatingsQuery)->count();
        $allVerySatisfied = (clone $allRatingsQuery)->where('rate_score', 5)->count();
        $allSatisfied = (clone $allRatingsQuery)->where('rate_score', 4)->count();
        $allNeutral = (clone $allRatingsQuery)->where('rate_score', 3)->count();
        $allDissatisfied = (clone $allRatingsQuery)->whereIn('rate_score', [1, 2])->count();

        $distribution = [
            'very_satisfied' => [
                'count' => $allVerySatisfied,
                'pct' => $allTotalRatings > 0 ? round(($allVerySatisfied / $allTotalRatings) * 100, 2) : 0,
            ],
            'satisfied' => [
                'count' => $allSatisfied,
                'pct' => $allTotalRatings > 0 ? round(($allSatisfied / $allTotalRatings) * 100, 2) : 0,
            ],
            'neutral' => [
                'count' => $allNeutral,
                'pct' => $allTotalRatings > 0 ? round(($allNeutral / $allTotalRatings) * 100, 2) : 0,
            ],
            'dissatisfied' => [
                'count' => $allDissatisfied,
                'pct' => $allTotalRatings > 0 ? round(($allDissatisfied / $allTotalRatings) * 100, 2) : 0,
            ],
            'total_ratings' => $allTotalRatings,
        ];

        // Stats based on filters
        $stats = [
            'total_surveys' => $totalSurveys,
            'active_users' => $activeUsers,
            'satisfaction_rate' => round($satisfactionRate, 2),
            'pending_reviews' => $pendingReviews,
            // Filtered counts
            'filtered_total_ratings' => $totalRatings,
            'filtered_very_satisfied' => $verySatisfied,
            'filtered_satisfied' => $satisfied,
            'filtered_neutral' => $neutral,
            'filtered_dissatisfied' => $dissatisfied,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'module_counts' => [
                'users' => $activeUsers,
                'units' => Unit::count(),
                'regions' => Region::count(),
                'pstos' => psto::count(),
                'services' => Services::count(),
            ],
            'distribution' => $distribution,
            'filters' => [
                'rating_filter' => $ratingFilter,
                'date_filter' => $dateFilter,
            ],
        ]);
    }
}
