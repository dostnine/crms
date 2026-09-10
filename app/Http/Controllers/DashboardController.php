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

        if (!in_array($dateFilter, ['all', 'today', 'week', 'month', 'quarter', 'year', 'range'], true)) {
            $dateFilter = 'all';
        }

        // month / quarter / year / range mirror the report builder: each picks a
        // specific period, defaulting to the current one.
        [$dateRange, $dateRangeEnd, $customState, $periodLabel] =
            $this->resolveDateRange($request, $dateFilter);

        // Applies the resolved window to any query with a created_at column.
        $applyRange = function ($query, string $column = 'created_at') use ($dateRange, $dateRangeEnd) {
            if ($dateRange) {
                $query->where($column, '>=', $dateRange);
            }
            if ($dateRangeEnd) {
                $query->where($column, '<=', $dateRangeEnd);
            }
            return $query;
        };

        // Base queries with optional date filtering
        $formsQuery = CSFForm::query();
        $ratingsQuery = CustomerAttributeRating::query();

        $applyRange($formsQuery);
        $applyRange($ratingsQuery);

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
        $applyRange($pendingQuery, 'f.created_at');
        $pendingReviews = $pendingQuery->distinct('f.customer_id')->count('f.customer_id');

        // Rating distribution
        $ratingsFilteredQuery = CustomerAttributeRating::query();
        $applyRange($ratingsFilteredQuery);

        if ($ratingFilter === 'positive') {
            $ratingsFilteredQuery->whereIn('rate_score', [4, 5]);
        } elseif ($ratingFilter === 'neutral') {
            $ratingsFilteredQuery->where('rate_score', 3);
        } elseif ($ratingFilter === 'negative') {
            $ratingsFilteredQuery->whereIn('rate_score', [1, 2]);
        }

        $totalRatings = (clone $ratingsFilteredQuery)->count();
        // Distinct people behind those ratings -- each respondent scores several
        // attributes, so the rating count is far larger than the headcount.
        $filteredRespondents = (clone $ratingsFilteredQuery)
            ->distinct('customer_id')
            ->count('customer_id');
        $verySatisfied = (clone $ratingsFilteredQuery)->where('rate_score', 5)->count();
        $satisfied = (clone $ratingsFilteredQuery)->where('rate_score', 4)->count();
        $neutral = (clone $ratingsFilteredQuery)->where('rate_score', 3)->count();
        $dissatisfied = (clone $ratingsFilteredQuery)->whereIn('rate_score', [1, 2])->count();

        // Get distribution for ALL ratings (for pie chart) with date filter only
        $allRatingsQuery = CustomerAttributeRating::query();
        $applyRange($allRatingsQuery);

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

        // Average score out of 5 across the selected window.
        $averageRating = (clone $ratingsQuery)->avg('rate_score');

        // Stats based on filters
        $stats = [
            'total_surveys' => $totalSurveys,
            'active_users' => $activeUsers,
            'satisfaction_rate' => round($satisfactionRate, 2),
            'pending_reviews' => $pendingReviews,
            // Period-aware headline figures.
            'total_respondents' => $totalRespondents,
            'total_ratings' => $allTotalRatings,
            'average_rating' => round((float) $averageRating, 2),
            // Filtered counts
            'filtered_total_ratings' => $totalRatings,
            'filtered_total_respondents' => $filteredRespondents,
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
            'filters' => array_merge([
                'rating_filter' => $ratingFilter,
                'date_filter' => $dateFilter,
                'period_label' => $periodLabel,
            ], $customState),
            // Years that actually have data, so the year picker only offers
            // options that will return something.
            'available_years' => $this->availableYears(),
        ]);
    }

    /**
     * Work out the created_at window for the selected filter.
     *
     * Returns [start, end, selectionState, label]. "today"/"week" are rolling
     * presets (start only); month/quarter/year resolve to a closed range so a
     * specific period doesn't bleed into later records. Each period selector
     * defaults to the current month/quarter/year.
     */
    private function resolveDateRange(Request $request, string $dateFilter): array
    {
        $now = Carbon::now();

        // Defaults are "the period we're in right now", matching the reports.
        $year = (int) $request->query('year', $now->year);
        $month = min(max((int) $request->query('month', $now->month), 1), 12);
        $quarter = min(max((int) $request->query('quarter', $now->quarter), 1), 4);
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $state = [
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $monthNames = [1 => 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        switch ($dateFilter) {
            case 'today':
                return [Carbon::today(), null, $state, 'Today'];

            case 'week':
                return [$now->copy()->startOfWeek(), null, $state, 'This Week'];

            case 'month':
                $start = Carbon::create($year, $month, 1)->startOfDay();
                return [$start, $start->copy()->endOfMonth(), $state,
                    $monthNames[$month] . ' ' . $year];

            case 'quarter':
                $start = Carbon::create($year, (($quarter - 1) * 3) + 1, 1)->startOfDay();
                return [$start, $start->copy()->addMonths(2)->endOfMonth(), $state,
                    'Q' . $quarter . ' ' . $year];

            case 'year':
                $start = Carbon::create($year, 1, 1)->startOfDay();
                // "Year 2025" rather than a bare "2025", which reads as a
                // stray number in the summary line.
                return [$start, $start->copy()->endOfYear(), $state, 'Year ' . $year];

            case 'range':
                $start = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
                $end = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

                // Nothing picked yet -- show everything rather than nothing.
                if (!$start && !$end) {
                    return [null, null, $state, 'All Time'];
                }

                $label = ($start ? $start->format('M d, Y') : 'Start')
                    . ' to ' .
                    ($end ? $end->format('M d, Y') : 'Today');

                return [$start, $end, $state, $label];

            default:
                return [null, null, $state, 'All Time'];
        }
    }

    /**
     * Distinct years present in the survey data, newest first.
     */
    private function availableYears(): array
    {
        $years = CSFForm::selectRaw('DISTINCT YEAR(created_at) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values()
            ->all();

        // Always offer the current year even before any data lands in it.
        $current = Carbon::now()->year;
        if (!in_array($current, $years, true)) {
            array_unshift($years, $current);
        }

        return $years;
    }
}
