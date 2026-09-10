<?php
// Blade/dompdf version of the All Units printout. Mirrors the two on-screen
// formats (AllServicesUnits/Monthly/Content.vue and AltContent.vue) -- only
// the rendering mechanism changed, the layouts are unchanged.
$toArray = function ($value) {
    return is_array($value) || is_object($value)
        ? json_decode(json_encode($value), true)
        : $value;
};

$services_units   = $toArray($services_units ?? null);
$all_units_data   = $toArray($all_units_data ?? null);
$cc_data          = $toArray($cc_data ?? null);
$respondent_profile = $toArray($respondent_profile ?? null);
$comments         = $toArray($comments ?? []);
$region           = $toArray($region ?? null);
$prepared_by      = $toArray($prepared_by ?? null);
$reviewed_by      = $toArray($reviewed_by ?? null);
$noted_by         = $toArray($noted_by ?? null);

$serviceList = $services_units['data'] ?? (is_array($services_units) ? $services_units : []);
$unitsData   = $all_units_data['units_data'] ?? [];

$isAlt = ($format ?? 'standard') === 'alternative';

// --- header labels (mirrors the computed properties in both components) ---
$regionLabel = $region['short_name'] ?? $region['code'] ?? $region['name'] ?? null;
$regionTitle = $regionLabel ? 'DOST ' . $regionLabel : 'DOST';

$csiType  = $form->csi_type ?? '';
$year     = $form->selected_year ?? '';
$quarter  = $form->selected_quarter ?? '';
$monthTxt = $form->selected_month ?? '';

if ($csiType === 'By Quarter') {
    $printPeriodText     = trim("FOR THE {$quarter} {$year}");
    $reportSummaryLabel  = 'QUARTERLY SUMMARY';
    $assessmentPeriodText = trim("{$quarter} {$year}");
} elseif ($csiType === 'By Year/Annual') {
    $printPeriodText     = "FOR THE YEAR {$year}";
    $reportSummaryLabel  = 'YEARLY SUMMARY';
    $assessmentPeriodText = (string) $year;
} else {
    $printPeriodText     = trim("FOR THE MONTH OF {$monthTxt} {$year}");
    $reportSummaryLabel  = 'MONTHLY SUMMARY';
    $assessmentPeriodText = trim("{$monthTxt} {$year}");
}

// --- helpers ported 1:1 from the Vue components ---
$isAdminSupportUnit = function ($unit) {
    $name = strtolower($unit['unit_name'] ?? '');
    return str_contains($name, 'administrative support services')
        || str_contains($name, 'administrative support service')
        || (str_contains($name, 'administrative') && str_contains($name, 'support'))
        || str_contains($name, 'admin support')
        || str_contains($name, 'admin support services')
        || str_contains($name, 'admin support service');
};

$node = function ($serviceId, $unitId) use ($unitsData) {
    return $unitsData[$serviceId][$unitId] ?? null;
};

$shouldShowServiceUnit = function ($serviceId, $unit) use ($node, $isAdminSupportUnit) {
    if ($isAdminSupportUnit($unit)) {
        return true;
    }
    $n = $node($serviceId, $unit['id'] ?? null);
    if (($n['total_respo'] ?? 0) > 0) {
        return true;
    }
    return !empty($unit['sub_units']);
};

// Rows with data only, keyed collections -> lists.
$withData = function ($collection) {
    $out = [];
    foreach (($collection ?? []) as $id => $row) {
        if (($row['total_respo'] ?? 0) > 0) {
            $out[] = array_merge(['id' => $id], $row);
        }
    }
    return $out;
};

// Percentage cell: blank-ish dash when zero, matching the Vue templates.
$pct = function ($v) {
    return (is_numeric($v) && (float) $v > 0) ? $v . '%' : '-';
};
$num = function ($v) {
    return (is_numeric($v) && (float) $v > 0) ? $v : '-';
};

// --- alternative-format numeric helpers ---
$rowVss = function ($row) {
    return (float) ($row['strongly_agree_count'] ?? 0) + (float) ($row['agree_count'] ?? 0);
};
$rowPct = function ($row) use ($rowVss) {
    $total = (float) ($row['total_respo'] ?? 0);
    $na = (float) ($row['na_count'] ?? 0);
    $denom = max($total - $na, 0);
    return $denom > 0 ? ($rowVss($row) / $denom) * 100 : 0;
};
$fmtPctOrDash = function ($v) {
    $n = is_numeric($v) ? (float) $v : null;
    return ($n === null || $n == 0.0) ? '-' : number_format($n, 2) . '%';
};
$fmtNumOrDash = function ($v) {
    $n = is_numeric($v) ? (float) $v : null;
    return ($n === null || $n == 0.0) ? '-' : number_format($n, 2);
};

// Standard format's per-service totals (percentage averages across units).
$serviceTotals = function ($serviceId) use ($unitsData) {
    $units = $unitsData[$serviceId] ?? [];
    $respo = 0; $sa = 0; $a = 0; $n = 0; $d = 0; $sd = 0; $count = 0;
    foreach ($units as $u) {
        if (!$u || ($u['total_respo'] ?? 0) <= 0) continue;
        $respo += (float) ($u['total_respo'] ?? 0);
        $sa += (float) ($u['pct_strongly_agree'] ?? 0);
        $a  += (float) ($u['pct_agree'] ?? 0);
        $n  += (float) ($u['pct_neither'] ?? 0);
        $d  += (float) ($u['pct_disagree'] ?? 0);
        $sd += (float) ($u['pct_strongly_disagree'] ?? 0);
        $count++;
    }
    return [
        'respo' => $respo,
        'pctStrongly' => $count ? $sa / $count : 0,
        'pctAgree' => $count ? $a / $count : 0,
        'pctNeither' => $count ? $n / $count : 0,
        'pctDisagree' => $count ? $d / $count : 0,
        'pctStronglyDisagree' => $count ? $sd / $count : 0,
    ];
};

// Alternative format's per-service totals (raw counts).
$altServiceTotals = function ($serviceId) use ($unitsData) {
    $units = $unitsData[$serviceId] ?? [];
    $t = ['total_respo' => 0, 'strongly_agree_count' => 0, 'agree_count' => 0, 'na_count' => 0];
    foreach ($units as $u) {
        if (!$u) continue;
        $t['total_respo'] += (float) ($u['total_respo'] ?? 0);
        $t['strongly_agree_count'] += (float) ($u['strongly_agree_count'] ?? 0);
        $t['agree_count'] += (float) ($u['agree_count'] ?? 0);
        $t['na_count'] += (float) ($u['na_count'] ?? 0);
    }
    return $t;
};

$hasAnyCCData = function () use ($cc_data) {
    $c1 = $cc_data['cc1_data'] ?? [];
    $c2 = $cc_data['cc2_data'] ?? [];
    $c3 = $cc_data['cc3_data'] ?? [];
    foreach ([$c1, $c2, $c3] as $grp) {
        foreach ($grp as $k => $v) {
            if (str_contains($k, '_ans') && !str_contains($k, '_pct') && (float) $v > 0) {
                return true;
            }
        }
    }
    return false;
};

// --- pie charts (standard format only) ---
// Same segmented-polygon builder used by the other reports: php-svg-lib drops
// arcs whose endpoints are mirror-symmetric about the 45-degree diagonal, so
// slices are traced with straight segments instead.
$piePalette = ['#1f77b4', '#2ca02c', '#ffbf00', '#ff7f0e', '#d62728'];
$buildPieChartDataUri = function (array $counts, array $colors) {
    $total = array_sum($counts);
    $cx = 45; $cy = 45; $r = 42;
    $polar = function ($a) use ($cx, $cy, $r) {
        $rad = deg2rad($a - 90);
        return [$cx + $r * cos($rad), $cy + $r * sin($rad)];
    };
    $parts = []; $angle = 0; $i = 0;
    foreach ($counts as $count) {
        $color = $colors[$i % count($colors)]; $i++;
        $sweep = $total > 0 ? ($count / $total) * 360 : 0;
        if ($sweep <= 0) continue;
        $start = $angle; $angle += $sweep;
        if ($sweep >= 359.999) {
            $parts[] = '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $color . '" stroke="#ffffff" stroke-width="0.5" />';
            continue;
        }
        $segments = max(2, (int) ceil($sweep / 2));
        [$x, $y] = $polar($start);
        $d = "M $cx,$cy L $x,$y";
        for ($s = 1; $s <= $segments; $s++) {
            [$px, $py] = $polar($start + ($sweep * $s / $segments));
            $d .= " L $px,$py";
        }
        $d .= ' Z';
        $parts[] = '<path d="' . $d . '" fill="' . $color . '" stroke="#ffffff" stroke-width="0.5" />';
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 90 90">' . implode('', $parts) . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
};

$pieLabels = ['Strongly Agree', 'Agree', 'Neither', 'Disagree', 'Strongly Disagree'];
$buildCharts = function ($adminOnly) use ($serviceList, $unitsData, $isAdminSupportUnit, $pieLabels, $piePalette, $buildPieChartDataUri) {
    $charts = [];
    foreach ($serviceList as $service) {
        foreach (($service['units'] ?? []) as $unit) {
            $isAdmin = $isAdminSupportUnit($unit);
            if ($adminOnly !== $isAdmin) continue;

            $sources = [];
            if ($adminOnly) {
                // Admin support services are charted per sub-unit.
                $subs = $unitsData[$service['id']][$unit['id']]['sub_units_data'] ?? [];
                foreach (($unit['sub_units'] ?? []) as $sub) {
                    $row = $subs[$sub['id']] ?? null;
                    if ($row && ($row['total_respo'] ?? 0) > 0) {
                        $sources[] = [$sub['sub_unit_name'] ?? 'Sub Unit', $row];
                    }
                }
            } else {
                $row = $unitsData[$service['id']][$unit['id']] ?? null;
                if ($row && ($row['total_respo'] ?? 0) > 0) {
                    $sources[] = [$unit['unit_name'] ?? 'Unit', $row];
                }
            }

            foreach ($sources as [$name, $row]) {
                $counts = [
                    (float) ($row['strongly_agree_count'] ?? 0),
                    (float) ($row['agree_count'] ?? 0),
                    (float) ($row['neither_count'] ?? 0),
                    (float) ($row['disagree_count'] ?? 0),
                    (float) ($row['strongly_disagree_count'] ?? 0),
                ];
                $total = array_sum($counts);
                if ($total <= 0) continue;

                $legend = [];
                foreach ($counts as $idx => $cnt) {
                    $legend[] = [
                        'label' => $pieLabels[$idx],
                        'color' => $piePalette[$idx],
                        'count' => $cnt,
                        'pct' => number_format(($cnt / $total) * 100, 2),
                    ];
                }
                $charts[] = [
                    'unitName' => $name,
                    'serviceName' => $service['services_name'] ?? '',
                    'total' => $total,
                    'uri' => $buildPieChartDataUri($counts, $piePalette),
                    'legend' => $legend,
                ];
            }
        }
    }
    return $charts;
};

$unitCharts = $isAlt ? [] : $buildCharts(false);
$subUnitCharts = $isAlt ? [] : $buildCharts(true);

// --- comments (shared) ---
// Placeholder answers ("None", "n/a", "-") are dropped and metadata columns
// blanked by the shared filter, so every report cleans feedback identically.
[$complaintRows, $commentRows] = \App\Support\FeedbackFilter::split($comments);
$normalized = array_merge($complaintRows, $commentRows);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    /* This report is consolidated across every service unit, so it is kept
       deliberately dense -- tight margins, small type and no forced page
       breaks -- to keep the printed page count down. */
    @page { margin: 5mm; }
    * { font-family: 'DejaVu Sans', sans-serif; }
    body { font-size: 8px; line-height: 1.1; color: #12243a; }
    table { border-collapse: collapse; width: 100%; }
    .grid, .grid td, .grid th { border: 1px solid #333; }
    .grid td, .grid th { padding: 1px 3px; vertical-align: middle; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    .print-title { font-size: 13px; font-weight: bold; text-align: center; margin: 0; }
    .print-subtitle { font-size: 10px; text-align: center; margin: 1px 0 6px 0; }
    .section-title {
        background-color: #1b365d; color: #fff; font-weight: bold;
        font-size: 10px; padding: 3px 5px; margin: 8px 0 3px 0;
    }
    .bg-blue-200 { background-color: #e6f1ff; }
    .bg-head { background-color: #1976d2; color: #fff; font-weight: bold; }
    .bg-navy { background-color: #1f3b6e; color: #fff; font-weight: bold; }
    .bg-total { background-color: #e3f2fd; font-weight: bold; }
    .bg-service { background-color: #e6f1ff; font-weight: bold; }
    .bg-sub { background-color: #f4f6f9; }
    .bg-psto { background-color: #eefaf0; }
    .bg-type { background-color: #fffbe9; }
    .bg-shade { background-color: #d7e4ef; font-weight: bold; }
    .pl-5  { padding-left: 12px !important; }
    .pl-10 { padding-left: 22px !important; }
    .pl-14 { padding-left: 32px !important; }
    .pie-card { border: 1px solid #cbd7ea; padding: 5px; }
    .legend-dot { display: inline-block; width: 7px; height: 7px; margin-right: 4px; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

<table style="border:none; margin-bottom:4px;">
    <tr>
        <td style="width:52px; border:none; padding:0;"></td>
        <td style="border:none; padding:0; vertical-align:middle;">
            <div class="print-title">{{ $regionTitle }} Customer Satisfaction Feedback</div>
            <div class="print-subtitle">{{ $isAlt ? $reportSummaryLabel : $printPeriodText }}</div>
        </td>
        <td style="width:52px; border:none; padding:0; text-align:right;">
            <img src="{{ public_path('images/dost-logo.jpg') }}" style="width:40px; height:40px;">
        </td>
    </tr>
</table>

@if($hasAnyCCData())
<div class="section-title">PART I: CITIZEN'S CHARTER(CC)</div>
<table class="grid">
    <tr class="bg-blue-200 bold text-center">
        <th></th>
        <th></th>
        <th>Number of Respondents</th>
        <th>Percentage</th>
    </tr>
    @foreach([
        ['CC1', 'Which of the following best describes your awareness of a CC?', 'cc1_data', 'cc1', [
            "I know what a CC is and I saw this office's CC",
            "I know what a CC is but I did NOT see this office's CC",
            "I learned the CC when I saw this office's CC",
            "I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)",
        ]],
        ['CC2', 'If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?', 'cc2_data', 'cc2', [
            'Easy to see', 'Somewhat easy to see', 'Difficult to see', 'Not visible at all', 'N/A',
        ]],
        ['CC3', 'If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?', 'cc3_data', 'cc3', [
            'Helped Very Much', 'Somewhat helped', 'Did not help', 'N/A',
        ]],
    ] as [$code, $question, $dataKey, $prefix, $answers])
        <tr class="bg-blue-200">
            <th>{{ $code }}</th>
            <th colspan="3" class="text-left">{{ $question }}</th>
        </tr>
        @foreach($answers as $i => $answer)
            @php $n = $i + 1; @endphp
            <tr>
                <td class="text-center">{{ $n }}</td>
                <td class="text-left">{{ $answer }}</td>
                <td class="text-center">{{ $cc_data[$dataKey][$prefix . '_ans' . $n] ?? 0 }}</td>
                <td class="text-center">{{ $cc_data[$dataKey][$prefix . '_ans' . $n . '_pct'] ?? 0 }}%</td>
            </tr>
        @endforeach
        <tr class="bg-blue-200 bold">
            <td></td>
            <td class="text-left">Total</td>
            <td class="text-center">{{ $cc_data[$dataKey][$prefix . '_total'] ?? 0 }}</td>
            <td class="text-center">{{ ($cc_data[$dataKey][$prefix . '_total'] ?? 0) > 0 ? '100%' : '0%' }}</td>
        </tr>
    @endforeach
</table>
@endif

@if($isAlt)
    {{-- ============ ALTERNATIVE FORMAT ============ --}}
    <div class="section-title">PART II: SERVICE UNITS OVERVIEW - {{ $assessmentPeriodText }}</div>
    <table class="grid">
        <tr class="bg-head text-center">
            <th>Service Unit</th>
            <th>Total No. of Respondents</th>
            <th>Total No. of Respondents who rated VS and S</th>
            <th>Percentage of Respondents who rated VS and S</th>
            <th>Customer Satisfaction Index (CSI)</th>
            <th>Net Promoter Score</th>
            <th>Likert Scale Rating (Attribute Average)</th>
        </tr>
        @foreach($serviceList as $service)
            @continue(empty($service['units']))
            <tr class="bg-service"><td colspan="7">{{ $service['services_name'] ?? '' }}</td></tr>

            @foreach($service['units'] as $unit)
                @continue(!$shouldShowServiceUnit($service['id'], $unit))
                @php
                    $isAdmin = $isAdminSupportUnit($unit);
                    $u = $node($service['id'], $unit['id'] ?? null);
                @endphp

                @if(!$isAdmin && ($u['total_respo'] ?? 0) > 0)
                    <tr>
                        <td class="pl-5">{{ $unit['unit_name'] ?? '' }}</td>
                        <td class="text-center">{{ $num($u['total_respo'] ?? 0) }}</td>
                        <td class="text-center">{{ $rowVss($u) ?: '-' }}</td>
                        <td class="text-center">{{ number_format($rowPct($u), 2) }}%</td>
                        <td class="text-center">{{ number_format($rowPct($u), 2) }}%</td>
                        <td class="text-center">{{ $fmtPctOrDash($u['nps'] ?? null) }}</td>
                        <td class="text-center">{{ $fmtNumOrDash($u['lsr'] ?? null) }}</td>
                    </tr>
                @elseif($isAdmin)
                    <tr>
                        <td class="pl-5">{{ $unit['unit_name'] ?? '' }}</td>
                        @for($i = 0; $i < 6; $i++)<td class="text-center">-</td>@endfor
                    </tr>
                @endif

                @if(!$isAdmin)
                    @foreach($withData($u['unit_pstos_data'] ?? []) as $psto)
                        <tr class="bg-psto">
                            <td class="pl-10">{{ $psto['psto_name'] ?? 'PSTO' }}</td>
                            <td class="text-center">{{ $num($psto['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $rowVss($psto) ?: '-' }}</td>
                            <td class="text-center">{{ number_format($rowPct($psto), 2) }}%</td>
                            <td class="text-center">{{ number_format($rowPct($psto), 2) }}%</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    @endforeach
                @endif

                @foreach(($unit['sub_units'] ?? []) as $subUnit)
                    @php $s = $u['sub_units_data'][$subUnit['id']] ?? null; @endphp
                    @if(($s['total_respo'] ?? 0) > 0)
                        <tr class="bg-sub">
                            <td class="pl-10">{{ $subUnit['sub_unit_name'] ?? '' }}</td>
                            <td class="text-center">{{ $num($s['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $rowVss($s) ?: '-' }}</td>
                            <td class="text-center">{{ number_format($rowPct($s), 2) }}%</td>
                            <td class="text-center">{{ number_format($rowPct($s), 2) }}%</td>
                            <td class="text-center">{{ $fmtPctOrDash($s['nps'] ?? null) }}</td>
                            <td class="text-center">{{ $fmtNumOrDash($s['lsr'] ?? null) }}</td>
                        </tr>
                    @endif
                    @foreach($withData($s['sub_unit_types_data'] ?? []) as $type)
                        <tr class="bg-type">
                            <td class="pl-14">{{ $type['type_name'] ?? '' }}</td>
                            <td class="text-center">{{ $num($type['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $rowVss($type) ?: '-' }}</td>
                            <td class="text-center">{{ number_format($rowPct($type), 2) }}%</td>
                            <td class="text-center">{{ number_format($rowPct($type), 2) }}%</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    @endforeach
                    @foreach($withData($s['pstos_data'] ?? []) as $sp)
                        <tr class="bg-psto">
                            <td class="pl-14">{{ $sp['psto_name'] ?? 'PSTO' }} (PSTO)</td>
                            <td class="text-center">{{ $num($sp['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $rowVss($sp) ?: '-' }}</td>
                            <td class="text-center">{{ number_format($rowPct($sp), 2) }}%</td>
                            <td class="text-center">{{ number_format($rowPct($sp), 2) }}%</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach

            @php $at = $altServiceTotals($service['id']); @endphp
            <tr class="bg-total">
                <td class="pl-5">{{ in_array($service['services_name'] ?? '', ['OFFICE OF THE REGIONAL DIRECTOR','FINANCE AND ADMINISTRATIVE SUPPORT SERVICES','TECHNICAL OPERATION SERVICES']) ? '' : ($service['services_name'] ?? '') . ' TOTAL' }}</td>
                <td class="text-center">{{ $at['total_respo'] ?: '-' }}</td>
                <td class="text-center">{{ ($at['strongly_agree_count'] + $at['agree_count']) ?: '-' }}</td>
                <td class="text-center">{{ number_format($rowPct($at), 2) }}%</td>
                <td class="text-center">{{ number_format($rowPct($at), 2) }}%</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            </tr>
        @endforeach

        <tr class="bg-total">
            <td>TOTAL:</td>
            <td class="text-center">{{ $num($total_respondents ?? 0) }}</td>
            <td class="text-center">{{ $num($total_vss_respondents ?? 0) }}</td>
            <td class="text-center">{{ $percentage_vss_respondents ?? 0 }}%</td>
            <td class="text-center">{{ $csi_total ?? 0 }}%</td>
            <td class="text-center">{{ $fmtPctOrDash($nps_total ?? null) }}</td>
            <td class="text-center">{{ $fmtNumOrDash($lsr_total ?? null) }}</td>
        </tr>
    </table>
@else
    {{-- ============ STANDARD FORMAT ============ --}}
    <div class="section-title">PART II: SERVICE UNITS OVERVIEW - {{ $assessmentPeriodText }}</div>
    <table class="grid">
        <tr class="bg-head text-center">
            <th>Service Unit</th>
            <th>Total No. of Respondents</th>
            <th>Strongly Agree</th>
            <th>Agree</th>
            <th>Neither</th>
            <th>Disagree</th>
            <th>Strongly Disagree</th>
        </tr>
        @foreach($serviceList as $service)
            @continue(empty($service['units']))
            <tr class="bg-service"><td colspan="7">{{ $service['services_name'] ?? '' }}</td></tr>

            @foreach($service['units'] as $unit)
                @continue(!$shouldShowServiceUnit($service['id'], $unit))
                @php
                    $isAdmin = $isAdminSupportUnit($unit);
                    $u = $node($service['id'], $unit['id'] ?? null);
                @endphp

                @if(!$isAdmin && ($u['total_respo'] ?? 0) > 0)
                    <tr>
                        <td class="pl-5">{{ $unit['unit_name'] ?? '' }}</td>
                        <td class="text-center">{{ $num($u['total_respo'] ?? 0) }}</td>
                        <td class="text-center">{{ $pct($u['pct_strongly_agree'] ?? 0) }}</td>
                        <td class="text-center">{{ $pct($u['pct_agree'] ?? 0) }}</td>
                        <td class="text-center">{{ $pct($u['pct_neither'] ?? 0) }}</td>
                        <td class="text-center">{{ $pct($u['pct_disagree'] ?? 0) }}</td>
                        <td class="text-center">{{ $pct($u['pct_strongly_disagree'] ?? 0) }}</td>
                    </tr>
                @elseif($isAdmin)
                    <tr>
                        <td class="pl-5">{{ $unit['unit_name'] ?? '' }}</td>
                        @for($i = 0; $i < 6; $i++)<td class="text-center">-</td>@endfor
                    </tr>
                @endif

                @if(!$isAdmin)
                    @foreach($withData($u['unit_pstos_data'] ?? []) as $psto)
                        <tr class="bg-psto">
                            <td class="pl-10">{{ $psto['psto_name'] ?? 'PSTO' }}</td>
                            <td class="text-center">{{ $num($psto['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($psto['pct_strongly_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($psto['pct_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($psto['pct_neither'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($psto['pct_disagree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($psto['pct_strongly_disagree'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                @endif

                @foreach(($unit['sub_units'] ?? []) as $subUnit)
                    @php $s = $u['sub_units_data'][$subUnit['id']] ?? null; @endphp
                    @if(($s['total_respo'] ?? 0) > 0)
                        <tr class="bg-sub">
                            <td class="pl-10">{{ $subUnit['sub_unit_name'] ?? '' }}</td>
                            <td class="text-center">{{ $num($s['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($s['pct_strongly_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($s['pct_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($s['pct_neither'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($s['pct_disagree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($s['pct_strongly_disagree'] ?? 0) }}</td>
                        </tr>
                    @endif
                    @foreach($withData($s['sub_unit_types_data'] ?? []) as $type)
                        <tr class="bg-type">
                            <td class="pl-14">{{ $type['type_name'] ?? '' }}</td>
                            <td class="text-center">{{ $num($type['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($type['pct_strongly_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($type['pct_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($type['pct_neither'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($type['pct_disagree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($type['pct_strongly_disagree'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                    @foreach($withData($s['pstos_data'] ?? []) as $sp)
                        <tr class="bg-psto">
                            <td class="pl-14">{{ $sp['psto_name'] ?? 'PSTO' }} (PSTO)</td>
                            <td class="text-center">{{ $num($sp['total_respo'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($sp['pct_strongly_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($sp['pct_agree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($sp['pct_neither'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($sp['pct_disagree'] ?? 0) }}</td>
                            <td class="text-center">{{ $pct($sp['pct_strongly_disagree'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach

            @php $st = $serviceTotals($service['id']); @endphp
            <tr class="bg-type bold">
                <td class="pl-5">{{ in_array($service['services_name'] ?? '', ['OFFICE OF THE REGIONAL DIRECTOR','FINANCE AND ADMINISTRATIVE SUPPORT SERVICES','TECHNICAL OPERATION SERVICES']) ? '' : ($service['services_name'] ?? '') . ' TOTAL' }}</td>
                <td class="text-center">{{ $st['respo'] ?: '-' }}</td>
                <td class="text-center">{{ $st['pctStrongly'] > 0 ? number_format($st['pctStrongly'], 2) . '%' : '-' }}</td>
                <td class="text-center">{{ $st['pctAgree'] > 0 ? number_format($st['pctAgree'], 2) . '%' : '-' }}</td>
                <td class="text-center">{{ $st['pctNeither'] > 0 ? number_format($st['pctNeither'], 2) . '%' : '-' }}</td>
                <td class="text-center">{{ $st['pctDisagree'] > 0 ? number_format($st['pctDisagree'], 2) . '%' : '-' }}</td>
                <td class="text-center">{{ $st['pctStronglyDisagree'] > 0 ? number_format($st['pctStronglyDisagree'], 2) . '%' : '-' }}</td>
            </tr>
        @endforeach

        <tr class="bg-total">
            <td>TOTAL:</td>
            <td class="text-center">{{ $num($total_respondents ?? 0) }}</td>
            <td class="text-center">{{ $pct($all_units_data['grand_pct_strongly_agree'] ?? 0) }}</td>
            <td class="text-center">{{ $pct($all_units_data['grand_pct_agree'] ?? 0) }}</td>
            <td class="text-center">{{ $pct($all_units_data['grand_pct_neither'] ?? 0) }}</td>
            <td class="text-center">{{ $pct($all_units_data['grand_pct_disagree'] ?? 0) }}</td>
            <td class="text-center">{{ $pct($all_units_data['grand_pct_strongly_disagree'] ?? 0) }}</td>
        </tr>
    </table>
@endif

@if(!empty($all_units_data['service_totals']))
@php $stots = $all_units_data['service_totals']; @endphp
<div class="section-title">SERVICE CATEGORY TOTALS SUMMARY</div>
<table class="grid">
    <tr class="bg-head text-center">
        <th>CATEGORIES</th>
        <th>OFFICE OF THE REGIONAL DIRECTOR</th>
        <th>FINANCE AND ADMINISTRATIVE SUPPORT SERVICES</th>
        <th>TECHNICAL OPERATION SERVICES</th>
        <th>TOTAL</th>
    </tr>
    <tr class="bg-total">
        <td>TOTAL RESPONDENTS</td>
        <td class="text-center">{{ $stots[1]['total_respo'] ?? 0 }}</td>
        <td class="text-center">{{ $stots[2]['total_respo'] ?? 0 }}</td>
        <td class="text-center">{{ $stots[3]['total_respo'] ?? 0 }}</td>
        <td class="text-center">{{ $total_respondents ?? 0 }}</td>
    </tr>
    <tr class="bg-total">
        <td>TOTAL NO. OF STRONGLY AGREE / AGREE RATING</td>
        <td class="text-center">{{ $stots[1]['strongly_agree_agree_count'] ?? 0 }}</td>
        <td class="text-center">{{ $stots[2]['strongly_agree_agree_count'] ?? 0 }}</td>
        <td class="text-center">{{ $stots[3]['strongly_agree_agree_count'] ?? 0 }}</td>
        <td class="text-center">{{ $all_units_data['grand_strongly_agree_agree_count'] ?? 0 }}</td>
    </tr>
    <tr class="bg-total">
        <td>% SA + A</td>
        <td class="text-center">{{ $stots[1]['pct_strongly_agree_agree'] ?? 0 }}%</td>
        <td class="text-center">{{ $stots[2]['pct_strongly_agree_agree'] ?? 0 }}%</td>
        <td class="text-center">{{ $stots[3]['pct_strongly_agree_agree'] ?? 0 }}%</td>
        <td class="text-center">{{ $all_units_data['grand_pct_strongly_agree_agree'] ?? 0 }}%</td>
    </tr>
    <tr class="bg-total">
        <td colspan="3">CUSTOMER SATISFACTION RATING (VERY STRONGLY AGREE AND AGREE COMBINED):</td>
        <td colspan="2" class="text-center">{{ $all_units_data['grand_pct_strongly_agree_agree'] ?? 0 }}%</td>
    </tr>
    <tr class="bg-total">
        <td colspan="3">OVERALL SCORING RESULTS INTERPRETATION:</td>
        <td colspan="2" class="text-center">{{ $all_units_data['grand_pct_strongly_agree_agree'] ?? 0 }}%</td>
    </tr>
    <tr class="bg-total">
        <td colspan="3">TARGET: AT LEAST:</td>
        <td colspan="2" class="text-center">95% (VS + S combined)</td>
    </tr>
</table>
@endif

@if(!$isAlt && !empty($respondent_profile))
@php
    $sexRows = $respondent_profile['sex_table'] ?? [];
    $ageRows = $respondent_profile['age_table'] ?? [];
@endphp
@if(!empty($sexRows) || !empty($ageRows))
<div class="section-title">SEX-DISAGGREGATED DATA AND AGE GROUPS</div>
{{-- Sex and Age sit side by side rather than stacked: each is only four
     columns wide, so pairing them halves the vertical space they take. --}}
<table style="border:none;">
<tr>
    @foreach([['Sex', $sexRows], ['Age', $ageRows]] as $i => [$heading, $rows])
        @if($i === 1)<td style="width:2%; border:none; padding:0;"></td>@endif
        <td style="width:49%; border:none; padding:0; vertical-align:top;">
            @if(!empty($rows))
                <table class="grid">
                    <tr class="bg-navy text-center">
                        <th style="width:34%;">{{ $heading }}</th>
                        <th style="width:22%;">External</th>
                        <th style="width:22%;">Internal</th>
                        <th style="width:22%;">Overall</th>
                    </tr>
                    @foreach($rows as $row)
                        <tr>
                            <td rowspan="2">{{ $row['label'] ?? '' }}</td>
                            @foreach(['external','internal','overall'] as $col)
                                <td class="text-center bg-shade">{{ ($row[$col]['pct'] ?? '-') === '-' ? '-' : $row[$col]['pct'] . '%' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach(['external','internal','overall'] as $col)
                                <td class="text-center">{{ $row[$col]['count'] ?? 0 }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            @endif
        </td>
    @endforeach
</tr>
</table>
@endif
@endif

@foreach([['PIE CHART REPORT BY UNIT', $unitCharts], ['ADMINISTRATIVE SUPPORT SERVICES', $subUnitCharts]] as [$heading, $charts])
    @continue(empty($charts))
    {{-- No forced page break: let the charts flow on from whatever came
         before, three across, so they use every bit of the page. --}}
    <div class="section-title">{{ $heading }}</div>
    @foreach(array_chunk($charts, 3) as $trio)
        <table style="border:none; margin-bottom:4px;">
        <tr>
            @foreach($trio as $chart)
                <td class="pie-card" style="width:32%; vertical-align:top;">
                    <div class="bold" style="font-size:8px;">{{ $chart['unitName'] }}</div>
                    <div style="font-size:7px; color:#5a6b80; margin-bottom:2px;">{{ $chart['serviceName'] }}</div>
                    <table style="border:none;">
                    <tr>
                        <td style="width:56px; border:none; padding:0; text-align:center; vertical-align:middle;">
                            <img src="{{ $chart['uri'] }}" width="56" height="56">
                        </td>
                        <td style="border:none; padding:0 0 0 4px; vertical-align:middle;">
                            <table class="grid" style="font-size:6.5px;">
                                @foreach($chart['legend'] as $item)
                                    <tr>
                                        <td><span class="legend-dot" style="width:5px; height:5px; margin-right:2px; background-color:{{ $item['color'] }};"></span>{{ $item['label'] }}</td>
                                        <td class="text-center">{{ $item['count'] }}</td>
                                        <td class="text-center">{{ (float) $item['pct'] > 0 ? $item['pct'] . '%' : '-' }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                    </table>
                    <div style="font-size:6.5px; margin-top:1px;">Total Ratings: {{ $chart['total'] }}</div>
                </td>
                @if(!$loop->last)<td style="width:2%; border:none;"></td>@endif
            @endforeach
            @for($k = count($trio); $k < 3; $k++)<td style="border:none;"></td>@endfor
        </tr>
        </table>
    @endforeach
@endforeach

@if(count($normalized) > 0)
<div class="section-title">COMMENTS AND COMPLAINTS</div>
<div style="margin-bottom:6px;">
    Comments: <span class="bold">{{ count($commentRows) }}</span>
    &nbsp;&nbsp; Complaints: <span class="bold">{{ count($complaintRows) }}</span>
    <span style="color:#5a6b80;">
        &nbsp;&nbsp;(of {{ ($total_comments ?? 0) + ($total_complaints ?? 0) }} submitted &mdash; blank and "none"/"n/a" entries omitted)
    </span>
</div>

@foreach([['Complaints', $complaintRows], ['Comments', $commentRows]] as [$label, $rows])
    @continue(empty($rows))
    <div class="bold" style="margin:8px 0 3px 0;">{{ strtoupper($label) }} ({{ count($rows) }})</div>
    <table class="grid">
        <tr class="bg-blue-200 bold text-center">
            <th style="width:5%;">#</th>
            <th style="width:18%;">Unit</th>
            <th style="width:16%;">Sub Unit</th>
            <th style="width:13%;">PSTO</th>
            <th>{{ $label === 'Complaints' ? 'Complaint' : 'Comment' }}</th>
            <th style="width:13%;">Date</th>
        </tr>
        @foreach($rows as $i => $row)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $row['unit'] }}</td>
                <td>{{ $row['subUnit'] }}</td>
                <td>{{ $row['psto'] }}</td>
                <td>{{ $row['text'] }}</td>
                <td class="text-center">{{ $row['date'] }}</td>
            </tr>
        @endforeach
    </table>
@endforeach
@endif

<div style="margin-top:16px;">
    <div class="bold">ASSESSMENT</div>
    <div style="text-align:justify; margin-top:4px;">
        For {{ $assessmentPeriodText }}, a total of {{ $total_respondents ?? 0 }} respondents rated the Customer
        Satisfaction Feedback across all service units.
        {{ $total_vss_respondents ?? 0 }} ({{ $percentage_vss_respondents ?? 0 }}%) rated the services as
        Strongly Agree or Agree, resulting in an overall Customer Satisfaction Index of {{ $csi_total ?? 0 }}%,
        a Net Promoter Score of {{ $nps_total ?? 0 }} and an average Likert Scale Rating of {{ $lsr_total ?? 0 }}.
    </div>
</div>

@php
    // Yearly reports are signed off by three people; every other period is
    // just Prepared by / Noted by.
    $signatories = ($csiType === 'By Year/Annual')
        ? [['Prepared by:', $prepared_by], ['Reviewed by:', $reviewed_by], ['Noted by:', $noted_by]]
        : [['Prepared by:', $prepared_by], ['Noted by:', $noted_by]];
    $signatoryWidth = floor(100 / count($signatories));
@endphp
<table style="border:none; margin-top:22px; font-size:10px;">
<tr>
    @foreach($signatories as [$label, $person])
        <td style="width:{{ $signatoryWidth }}%; border:none; padding:0; vertical-align:top;">
            {{ $label }}
            <div style="margin-top:14px; margin-left:16px;">
                @if(!empty($person['name']))
                    <span style="text-decoration:underline; font-weight:bold;">{{ $person['name'] }}</span><br>
                @endif
                @if(!empty($person['designation']))
                    {{ $person['designation'] }}
                @endif
            </div>
        </td>
    @endforeach
</tr>
</table>

</body>
</html>
