<?php

namespace App\Http\Controllers;

use App\Models\CSFForm;
use App\Models\CustomerAttributeRating;
use App\Models\psto;
use App\Models\Region;
use App\Models\Services;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalSurveys = CSFForm::count();
        $activeUsers = User::count();

        $totalRespondents = CustomerAttributeRating::distinct('customer_id')->count('customer_id');
        $totalSatisfiedRespondents = CustomerAttributeRating::where('rate_score', '>', 3)
            ->distinct('customer_id')
            ->count('customer_id');
        $satisfactionRate = $totalRespondents > 0 ? ($totalSatisfiedRespondents / $totalRespondents) * 100 : 0;

        $pendingReviews = DB::table('c_s_f_forms as f')
            ->leftJoin('customer_recommendation_ratings as r', 'f.customer_id', '=', 'r.customer_id')
            ->whereNull('r.id')
            ->distinct('f.customer_id')
            ->count('f.customer_id');

        $ratingFilter = $request->query('rating_filter', 'all');
        if (!in_array($ratingFilter, ['all', 'positive', 'neutral', 'negative'], true)) {
            $ratingFilter = 'all';
        }

        $ratingsQuery = CustomerAttributeRating::query();
        if ($ratingFilter === 'positive') {
            $ratingsQuery->whereIn('rate_score', [4, 5]);
        } elseif ($ratingFilter === 'neutral') {
            $ratingsQuery->where('rate_score', 3);
        } elseif ($ratingFilter === 'negative') {
            $ratingsQuery->whereIn('rate_score', [1, 2]);
        }

        $totalRatings = (clone $ratingsQuery)->count();
        $verySatisfied = (clone $ratingsQuery)->where('rate_score', 5)->count();
        $satisfied = (clone $ratingsQuery)->where('rate_score', 4)->count();
        $neutral = (clone $ratingsQuery)->where('rate_score', 3)->count();
        $dissatisfied = (clone $ratingsQuery)->whereIn('rate_score', [1, 2])->count();

        $distribution = [
            'very_satisfied' => [
                'count' => $verySatisfied,
                'pct' => $totalRatings > 0 ? round(($verySatisfied / $totalRatings) * 100, 2) : 0,
            ],
            'satisfied' => [
                'count' => $satisfied,
                'pct' => $totalRatings > 0 ? round(($satisfied / $totalRatings) * 100, 2) : 0,
            ],
            'neutral' => [
                'count' => $neutral,
                'pct' => $totalRatings > 0 ? round(($neutral / $totalRatings) * 100, 2) : 0,
            ],
            'dissatisfied' => [
                'count' => $dissatisfied,
                'pct' => $totalRatings > 0 ? round(($dissatisfied / $totalRatings) * 100, 2) : 0,
            ],
            'total_ratings' => $totalRatings,
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_surveys' => $totalSurveys,
                'active_users' => $activeUsers,
                'satisfaction_rate' => round($satisfactionRate, 2),
                'pending_reviews' => $pendingReviews,
            ],
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
            ],
        ]);
    }
}
