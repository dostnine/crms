<?php
// The GET request that opens this page normalizes some form fields into
// stdClass via convertArraysToObjects(), while others stay plain arrays --
// flatten everything to arrays here so the template doesn't need to care.
$toArray = function ($value) {
    return is_array($value) || is_object($value)
        ? json_decode(json_encode($value), true)
        : $value;
};
$service = $toArray($service ?? null);
$unit = $toArray($unit ?? null);
$cc_data = $toArray($cc_data ?? null);
$prepared_by = $toArray($prepared_by ?? null);
$noted_by = $toArray($noted_by ?? null);
$unitName = $unit['data'][0]['unit_name'] ?? $unit['unit_name'] ?? '';

// dompdf's HTML parser does not render inline <svg> markup at all -- it only
// knows how to display SVG when it is loaded as an *image* (via <img src="">
// or a background-image url), using its bundled phenx/php-svg-lib renderer.
// So every pie chart in this report is built as a standalone SVG document
// (via the polar-to-cartesian pie-slice technique) and embedded as a base64
// data: URI image, since dompdf doesn't support CSS conic-gradient either.
$buildPieChartDataUri = function (array $counts, array $colors) {
    $total = array_sum($counts);
    $cx = 45; $cy = 45; $r = 42;
    $polarToCartesian = function ($angleDeg) use ($cx, $cy, $r) {
        $rad = deg2rad($angleDeg - 90);
        return [$cx + $r * cos($rad), $cy + $r * sin($rad)];
    };
    $svgParts = [];
    $angle = 0;
    $i = 0;
    foreach ($counts as $count) {
        $color = $colors[$i % count($colors)];
        $i++;
        $sweep = $total > 0 ? ($count / $total) * 360 : 0;
        if ($sweep <= 0) {
            continue;
        }
        $start = $angle;
        $angle += $sweep;

        if ($sweep >= 359.999) {
            $svgParts[] = '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $color . '" stroke="#ffffff" stroke-width="0.5" />';
            continue;
        }

        // Trace the slice as a fan of short straight segments instead of an
        // SVG arc. php-svg-lib (what dompdf rasterizes SVG with) drops arcs
        // whose endpoints are mirror-symmetric about the 45 degree diagonal
        // -- e.g. (45,3)->(3,45) -- emitting nothing at all, which is how a
        // 75% slice went missing and why the outline then bulged. Straight
        // segments only use M/L/Z, which cannot degenerate. At 2 degrees a
        // step the deviation from a true circle is ~0.006px: invisible.
        $segments = max(2, (int) ceil($sweep / 2));
        $d = "M $cx,$cy";
        for ($s = 0; $s <= $segments; $s++) {
            [$px, $py] = $polarToCartesian($start + ($sweep * $s / $segments));
            $d .= " L $px,$py";
        }
        $d .= ' Z';

        $svgParts[] = '<path d="' . $d . '" fill="' . $color . '" stroke="#ffffff" stroke-width="0.5" />';
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 90 90">' . implode('', $svgParts) . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
};
// Zero counts add visual noise to the rating tables -- render them blank.
$blankZero = function ($v) {
    return (is_numeric($v) && (float) $v == 0.0) ? '' : $v;
};
$blankZeroPct = function ($v) {
    return (is_numeric($v) && (float) $v == 0.0) ? '' : $v . '%';
};
$pieColors = ['#2e7d32', '#8bc34a', '#9e9e9e', '#f57c00', '#c62828'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 4mm;
    }
    * {
        font-family: 'DejaVu Sans', sans-serif;
    }
    body {
        font-size: 8px;
        line-height: 1.0;
        color: #12243a;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    td, th {
        border: 1px solid #333;
        padding: 0px 3px;
        vertical-align: middle;
        line-height: 1.05;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    .bg-header {
        background-color: #1b365d;
        color: #ffffff;
        font-weight: bold;
        font-size: 10px;
        letter-spacing: 0.3px;
        padding: 4px 6px;
    }
    .bottom-box {
        border: 1px solid #cbd7ea;
        padding: 5px;
    }
    .bg-blue-200 { background-color: #e6f1ff; }
    .bg-gray-200 { background-color: #eef0f4; }
    .bg-gray-300 { background-color: #dde4f2; }
    .row-vs { background-color: #eafaf0; }
    .row-s { background-color: #f4fbf6; }
    .row-n { background-color: #ffffff; }
    .row-d { background-color: #fff8ec; }
    .row-vd { background-color: #fdf0ef; }
    .section-title {
        font-size: 11px;
        margin: 0 0 3px 0;
    }
    .meta-line {
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 2px;
    }
    .legend-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        margin-right: 4px;
    }
    .comment-item {
        font-size: 10px;
        padding: 3px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .tag {
        font-size: 9px;
        font-weight: bold;
        padding: 0 3px;
    }
    .tag-complaint { color: #c0392b; }
    .tag-comment { color: #2a568f; }
</style>
</head>
<body>

<table style="border:none; margin-bottom:6px;">
    <tr>
        <td style="width:60px; border:none; padding:0;"></td>
        <td class="text-center" style="border:none; padding:0; vertical-align:middle;">
            <div style="font-size:14px; font-weight:bold;">CUSTOMER SATISFACTION FEEDBACK</div>
            <div style="font-size:11px;">SUMMARY REPORT FOR <u>{{ $form->selected_quarter }} {{ $form->selected_year }}</u></div>
        </td>
        <td style="width:60px; border:none; padding:0; text-align:right;">
            <img src="{{ public_path('images/dost-logo.jpg') }}" style="width:48px; height:48px;">
        </td>
    </tr>
</table>

<div class="meta-line">Services: <u>{{ $service['services_name'] ?? '' }}</u></div>
<div class="meta-line" style="margin-bottom:10px;">
    Service Unit: <u>{{ $unitName }}</u>
    @if(!empty($form->client_type)) &nbsp;&nbsp; {{ $form->client_type }} @endif
</div>

@if(!empty($cc_data))
<div class="section-title bold">PART I: CITIZEN'S CHARTER (CC)</div>
<table style="margin-bottom:10px;">
    <tr>
        <th></th>
        <th></th>
        <th>Respondents</th>
        <th>Percentage</th>
    </tr>
    <tr class="bg-blue-200">
        <th>CC1</th>
        <th colspan="3" class="text-left">Which of the following best describes your awareness of a CC?</th>
    </tr>
    <tr><td class="text-center">1</td><td class="text-left">I know what a CC is and I saw this office's CC</td><td class="text-center">{{ $blankZero($cc_data['cc1_data']['cc1_ans1'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc1_data']['cc1_ans1_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">2</td><td class="text-left">I know what a CC is but I did NOT see this office's CC</td><td class="text-center">{{ $blankZero($cc_data['cc1_data']['cc1_ans2'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc1_data']['cc1_ans2_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">3</td><td class="text-left">I learned the CC when I saw this office's CC</td><td class="text-center">{{ $blankZero($cc_data['cc1_data']['cc1_ans3'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc1_data']['cc1_ans3_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">4</td><td class="text-left">I do not know what a CC is and I did not see one in this office</td><td class="text-center">{{ $blankZero($cc_data['cc1_data']['cc1_ans4'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc1_data']['cc1_ans4_pct'] ?? 0) }}</td></tr>
    <tr class="bg-blue-200"><td></td><td class="text-left bold">Total</td><td class="text-center bold">{{ $blankZero($cc_data['cc1_data']['cc1_total'] ?? 0) }}</td><td class="text-center bold">{{ ($cc_data['cc1_data']['cc1_total'] ?? 0) ? '100%' : '' }}</td></tr>
    <tr class="bg-blue-200">
        <th>CC2</th>
        <th colspan="3" class="text-left">If aware of CC, would say that the CC of this was...?</th>
    </tr>
    <tr><td class="text-center">1</td><td class="text-left">Easy to see</td><td class="text-center">{{ $blankZero($cc_data['cc2_data']['cc2_ans1'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc2_data']['cc2_ans1_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">2</td><td class="text-left">Somewhat easy to see</td><td class="text-center">{{ $blankZero($cc_data['cc2_data']['cc2_ans2'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc2_data']['cc2_ans2_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">3</td><td class="text-left">Difficult to see</td><td class="text-center">{{ $blankZero($cc_data['cc2_data']['cc2_ans3'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc2_data']['cc2_ans3_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">4</td><td class="text-left">Not visible at all</td><td class="text-center">{{ $blankZero($cc_data['cc2_data']['cc2_ans4'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc2_data']['cc2_ans4_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">5</td><td class="text-left">N/A</td><td class="text-center">{{ $blankZero($cc_data['cc2_data']['cc2_ans5'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc2_data']['cc2_ans5_pct'] ?? 0) }}</td></tr>
    <tr class="bg-blue-200"><td></td><td class="text-left bold">Total</td><td class="text-center bold">{{ $blankZero($cc_data['cc2_data']['cc2_total'] ?? 0) }}</td><td class="text-center bold">{{ ($cc_data['cc2_data']['cc2_total'] ?? 0) ? '100%' : '' }}</td></tr>
    <tr class="bg-blue-200">
        <th>CC3</th>
        <th colspan="3" class="text-left">If aware of CC, how much did the CC help you in your transaction?</th>
    </tr>
    <tr><td class="text-center">1</td><td class="text-left">Helped Very Much</td><td class="text-center">{{ $blankZero($cc_data['cc3_data']['cc3_ans1'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc3_data']['cc3_ans1_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">2</td><td class="text-left">Somewhat helped</td><td class="text-center">{{ $blankZero($cc_data['cc3_data']['cc3_ans2'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc3_data']['cc3_ans2_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">3</td><td class="text-left">Did not help</td><td class="text-center">{{ $blankZero($cc_data['cc3_data']['cc3_ans3'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc3_data']['cc3_ans3_pct'] ?? 0) }}</td></tr>
    <tr><td class="text-center">4</td><td class="text-left">N/A</td><td class="text-center">{{ $blankZero($cc_data['cc3_data']['cc3_ans4'] ?? 0) }}</td><td class="text-center">{{ $blankZeroPct($cc_data['cc3_data']['cc3_ans4_pct'] ?? 0) }}</td></tr>
    <tr class="bg-blue-200"><td></td><td class="text-left bold">Total</td><td class="text-center bold">{{ $blankZero($cc_data['cc3_data']['cc3_total'] ?? 0) }}</td><td class="text-center bold">{{ ($cc_data['cc3_data']['cc3_total'] ?? 0) ? '100%' : '' }}</td></tr>
</table>

@php
    $cc1Pie = [
        'I know what a CC is and I saw this office\'s CC' => (float) ($cc_data['cc1_data']['cc1_ans1'] ?? 0),
        'I know what a CC is but I did NOT see this office\'s CC' => (float) ($cc_data['cc1_data']['cc1_ans2'] ?? 0),
        'I learned the CC when I saw this office\'s CC' => (float) ($cc_data['cc1_data']['cc1_ans3'] ?? 0),
        'I do not know what a CC is and I did not see one' => (float) ($cc_data['cc1_data']['cc1_ans4'] ?? 0),
    ];
    $cc2Pie = [
        'Easy to see' => (float) ($cc_data['cc2_data']['cc2_ans1'] ?? 0),
        'Somewhat easy to see' => (float) ($cc_data['cc2_data']['cc2_ans2'] ?? 0),
        'Difficult to see' => (float) ($cc_data['cc2_data']['cc2_ans3'] ?? 0),
        'Not visible at all' => (float) ($cc_data['cc2_data']['cc2_ans4'] ?? 0),
        'N/A' => (float) ($cc_data['cc2_data']['cc2_ans5'] ?? 0),
    ];
    $cc3Pie = [
        'Helped Very Much' => (float) ($cc_data['cc3_data']['cc3_ans1'] ?? 0),
        'Somewhat helped' => (float) ($cc_data['cc3_data']['cc3_ans2'] ?? 0),
        'Did not help' => (float) ($cc_data['cc3_data']['cc3_ans3'] ?? 0),
        'N/A' => (float) ($cc_data['cc3_data']['cc3_ans4'] ?? 0),
    ];
    $ccPieCharts = [
        ['title' => 'CC1: AWARENESS OF CC', 'counts' => $cc1Pie],
        ['title' => 'CC2: VISIBILITY OF CC', 'counts' => $cc2Pie],
        ['title' => 'CC3: HELPFULNESS OF CC', 'counts' => $cc3Pie],
    ];
@endphp

<table style="border:none; margin-top:20px;">
<tr>
    @foreach($ccPieCharts as $ccPie)
        <td class="bottom-box" style="width:32%; vertical-align:top; padding:14px 10px;">
            <div class="bg-header text-center" style="font-size:11px; padding:6px 8px;">{{ $ccPie['title'] }}</div>
            <table style="border:none; margin-top:16px;">
            <tr>
                <td style="width:160px; text-align:center; vertical-align:middle; border:none; padding:0;">
                    <img src="{{ $buildPieChartDataUri($ccPie['counts'], $pieColors) }}" width="160" height="160">
                </td>
                <td style="vertical-align:middle; border:none; padding:0 0 0 10px;">
                    <table style="font-size:9.5px;">
                        @php $ccPieTotal = array_sum($ccPie['counts']); @endphp
                        @foreach($ccPie['counts'] as $label => $count)
                            @php $pct = $ccPieTotal > 0 ? number_format(($count / $ccPieTotal) * 100, 2) : '0.00'; @endphp
                            <tr>
                                <td class="text-left" style="padding:3px 4px;"><span class="legend-dot" style="background-color:{{ $pieColors[$loop->index % count($pieColors)] }}; width:9px; height:9px;"></span>{{ $label }}</td>
                                <td class="text-center" style="padding:3px 4px;">{{ $pct }}%</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            </table>
        </td>
        @if(!$loop->last)
            <td style="width:2%; border:none; padding:0;"></td>
        @endif
    @endforeach
</tr>
</table>

<div style="page-break-before: always;"></div>
@endif

@php
    // Part II has 6 rows per dimension (VS/S/N/D/VD + a Total row) plus 5
    // summary rows below the loop; Part III only has 5 rows per dimension
    // (no Total row) plus 6 summary rows -- pad Part III with blank filler
    // rows so both columns end at the same height.
    $dimCount = count($dimensions);
    $partIITotalRows = (6 * $dimCount) + 5;
    $partIIITotalRows = (5 * $dimCount) + 6;
    $partIIIFillerRows = max(0, $partIITotalRows - $partIIITotalRows);
@endphp

<table style="border:none;">
<tr>
<td style="width:49%; vertical-align:top; border:none; padding:0;">

<div class="section-title bold">PART II: CUSTOMER RATING OF SERVICE QUALITY</div>
<table>
    <tr class="bg-blue-200">
        <th colspan="2">Service Quality Attributes</th>
        <th>{{ $monthLabels[0] }}</th>
        <th>{{ $monthLabels[1] }}</th>
        <th>{{ $monthLabels[2] }}</th>
        <th>Raw Pts</th>
        <th>Score</th>
        <th>LSR</th>
    </tr>
    @foreach($dimensions as $index => $dimension)
        @php $d = $index + 1; @endphp
        <tr class="row-vs">
            <td rowspan="6" class="text-left">[{{ $d }}] {{ $dimension->name }}</td>
            <td>5 Very Satisfied</td>
            <td class="text-center">{{ $blankZero($vs_totals[$d]['first_month_vs_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($vs_totals[$d]['second_month_vs_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($vs_totals[$d]['third_month_vs_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($trp_totals[$d]['vs_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($p1_total_scores[$d]['x_vs_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($lsr_totals[$d]['vs_lsr_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-s">
            <td>4 Satisfied</td>
            <td class="text-center">{{ $blankZero($s_totals[$d]['first_month_s_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($s_totals[$d]['second_month_s_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($s_totals[$d]['third_month_s_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($trp_totals[$d]['s_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($p1_total_scores[$d]['x_s_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($lsr_totals[$d]['s_lsr_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-n">
            <td>3 Neither</td>
            <td class="text-center">{{ $blankZero($n_totals[$d]['first_month_n_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($n_totals[$d]['second_month_n_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($n_totals[$d]['third_month_n_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($trp_totals[$d]['n_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($p1_total_scores[$d]['x_n_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($lsr_totals[$d]['n_lsr_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-d">
            <td>2 Dissatisfied</td>
            <td class="text-center">{{ $blankZero($d_totals[$d]['first_month_d_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($d_totals[$d]['second_month_d_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($d_totals[$d]['third_month_d_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($trp_totals[$d]['d_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($p1_total_scores[$d]['x_d_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($lsr_totals[$d]['d_lsr_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-vd">
            <td>1 Very Dissatisfied</td>
            <td class="text-center">{{ $blankZero($vd_totals[$d]['first_month_vd_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($vd_totals[$d]['second_month_vd_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($vd_totals[$d]['third_month_vd_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($trp_totals[$d]['vd_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($p1_total_scores[$d]['x_vd_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($lsr_totals[$d]['vd_lsr_total'] ?? 0) }}</td>
        </tr>
        <tr class="bg-gray-200 bold">
            <td>Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-center">{{ $blankZero($trp_totals[$d]['total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($p1_total_scores[$d]['x_total_score'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($lsr_totals[$d]['lsr_total'] ?? 0) }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2" class="text-right">Total No. of Very Satisfied (VS):</td>
        <td class="text-center">{{ $first_month_vs_grand_total ?? 0 }}</td>
        <td class="text-center">{{ $second_month_vs_grand_total ?? 0 }}</td>
        <td class="text-center">{{ $third_month_vs_grand_total ?? 0 }}</td>
        <td class="text-center">{{ $vs_grand_total_raw_points ?? 0 }}</td>
        <td class="text-center">{{ $vs_grand_total_score ?? 0 }}</td>
        <td class="text-center">{{ $grand_total_score ? number_format(($vs_grand_total_score / $grand_total_score) * 100, 2) : 0 }}%</td>
    </tr>
    <tr>
        <td colspan="2" class="text-right">Total No. of Satisfied (S):</td>
        <td class="text-center">{{ $first_month_s_grand_total ?? 0 }}</td>
        <td class="text-center">{{ $second_month_s_grand_total ?? 0 }}</td>
        <td class="text-center">{{ $third_month_s_grand_total ?? 0 }}</td>
        <td class="text-center">{{ $s_grand_total_raw_points ?? 0 }}</td>
        <td class="text-center">{{ $s_grand_total_score ?? 0 }}</td>
        <td class="text-center">{{ $grand_total_score ? number_format(($s_grand_total_score / $grand_total_score) * 100, 2) : 0 }}%</td>
    </tr>
    <tr>
        <td colspan="8" class="text-right">Total Respondents: <span class="bold">{{ $total_respondents ?? 0 }}</span></td>
    </tr>
    <tr>
        <td colspan="8" class="text-right">Respondents who rated VS or S: <span class="bold">{{ $total_vss_respondents ?? 0 }}</span> ({{ $percentage_vss_respondents ?? 0 }}%)</td>
    </tr>
    <tr>
        <td colspan="8" class="text-right">Likert Scale Rating (Average): <span class="bold">{{ $lsr_average ?? 0 }}</span></td>
    </tr>
</table>

</td>
<td style="width:2%; border:none; padding:0;"></td>
<td style="width:49%; vertical-align:top; border:none; padding:0;">

<div class="section-title bold">PART III: IMPORTANCE OF THESE ATTRIBUTES TO THE CUSTOMERS</div>
<table>
    <tr class="bg-blue-200">
        <th colspan="2">Importance Attributes</th>
        <th>{{ $monthLabels[0] }}</th>
        <th>{{ $monthLabels[1] }}</th>
        <th>{{ $monthLabels[2] }}</th>
        <th>Raw Pts</th>
        <th>Score</th>
    </tr>
    @foreach($dimensions as $index => $dimension)
        @php $d = $index + 1; @endphp
        <tr class="row-vs">
            <td rowspan="5" class="text-left">[{{ $d }}] {{ $dimension->name }}</td>
            <td>5 Very Important</td>
            <td class="text-center">{{ $blankZero($vi_totals[$d]['first_month_vi_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($vi_totals[$d]['second_month_vi_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($vi_totals[$d]['third_month_vi_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_trp_totals[$d]['vi_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_total_scores[$d]['x_vi_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-s">
            <td>4 Important</td>
            <td class="text-center">{{ $blankZero($i_totals[$d]['first_month_i_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_totals[$d]['second_month_i_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_totals[$d]['third_month_i_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_trp_totals[$d]['i_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_total_scores[$d]['x_i_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-n">
            <td>3 Moderately Important</td>
            <td class="text-center">{{ $blankZero($mi_totals[$d]['first_month_mi_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($mi_totals[$d]['second_month_mi_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($mi_totals[$d]['third_month_mi_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_trp_totals[$d]['mi_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_total_scores[$d]['x_mi_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-d">
            <td>2 Slightly Important</td>
            <td class="text-center">{{ $blankZero($si_totals[$d]['first_month_si_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($si_totals[$d]['second_month_si_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($si_totals[$d]['third_month_si_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_trp_totals[$d]['si_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_total_scores[$d]['x_si_total'] ?? 0) }}</td>
        </tr>
        <tr class="row-vd">
            <td>1 Not All Important</td>
            <td class="text-center">{{ $blankZero($nai_totals[$d]['first_month_nai_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($nai_totals[$d]['second_month_nai_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($nai_totals[$d]['third_month_nai_total'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_trp_totals[$d]['nai_total_raw_points'] ?? 0) }}</td>
            <td class="text-center">{{ $blankZero($i_total_scores[$d]['x_nai_total'] ?? 0) }}</td>
        </tr>
    @endforeach
    <tr class="bg-blue-200">
        <td colspan="2"></td>
        <th>{{ $monthLabels[0] }}</th>
        <th>{{ $monthLabels[1] }}</th>
        <th>{{ $monthLabels[2] }}</th>
        <th colspan="2">AVERAGE (%)</th>
    </tr>
    <tr>
        <td colspan="2" class="text-right">% of Promoters:</td>
        <td class="text-center">{{ $first_month_percentage_promoters ?? 0 }}</td>
        <td class="text-center">{{ $second_month_percentage_promoters ?? 0 }}</td>
        <td class="text-center">{{ $third_month_percentage_promoters ?? 0 }}</td>
        <td colspan="2" class="text-center">{{ $average_percentage_promoters ?? 0 }}</td>
    </tr>
    <tr>
        <td colspan="2" class="text-right">% of Detractors:</td>
        <td class="text-center">{{ $first_month_percentage_detractors ?? 0 }}</td>
        <td class="text-center">{{ $second_month_percentage_detractors ?? 0 }}</td>
        <td class="text-center">{{ $third_month_percentage_detractors ?? 0 }}</td>
        <td colspan="2" class="text-center">{{ $average_percentage_detractors ?? 0 }}</td>
    </tr>
    <tr>
        <td colspan="2" class="text-right">Net Promoter Score:</td>
        <td class="text-center">{{ $first_month_net_promoter_score ?? 0 }}</td>
        <td class="text-center">{{ $second_month_net_promoter_score ?? 0 }}</td>
        <td class="text-center">{{ $third_month_net_promoter_score ?? 0 }}</td>
        <td colspan="2" class="text-center">{{ $ave_net_promoter_score ?? 0 }}</td>
    </tr>
    <tr>
        <td colspan="2" class="text-right">Customer Satisfaction Index (CSI):</td>
        <td class="text-center">{{ $first_month_csi ?? 0 }}</td>
        <td class="text-center">{{ $second_month_csi ?? 0 }}</td>
        <td class="text-center">{{ $third_month_csi ?? 0 }}</td>
        <td colspan="2" class="text-center">{{ $csi ?? 0 }}</td>
    </tr>
    <tr class="bg-blue-200 bold">
        <td colspan="2" class="text-right">Customer Satisfaction (CSAT) Score Rating:</td>
        <td colspan="5" class="text-center">{{ $customer_satisfaction_rating ?? 0 }}%</td>
    </tr>
    @for($f = 0; $f < $partIIIFillerRows; $f++)
        <tr><td colspan="7">&nbsp;</td></tr>
    @endfor
</table>

</td>
</tr>
</table>

@php
    $pieCounts = [
        'Very Satisfied' => (float) ($vs_grand_total_raw_points ?? 0),
        'Satisfied' => (float) ($s_grand_total_raw_points ?? 0),
        'Neither' => (float) ($n_grand_total_raw_points ?? 0),
        'Dissatisfied' => (float) ($d_grand_total_raw_points ?? 0),
        'Very Dissatisfied' => (float) ($vd_grand_total_raw_points ?? 0),
    ];
    $pieTotal = array_sum($pieCounts);
    // Same segmented-arc builder the Citizen's Charter pies use, so a
    // wide slice can't silently vanish the way a 270-degree one did.
    $pieChartDataUri = $buildPieChartDataUri($pieCounts, $pieColors);
@endphp

<div style="page-break-before: always;"></div>

<table style="border:none;">
<tr>
<td class="bottom-box" style="width:49%; vertical-align:top;">
    <div class="bg-header">ASSESSMENT</div>
    <div style="text-align:justify; font-size:11px; line-height:1.35; margin-top:6px;">
        The {{ $unitName }} Unit for the {{ strtolower($form->selected_quarter) }} of {{ $form->selected_year }}
        had a total of {{ $total_respondents ?? 0 }} respondents who filled out and rated the Customer Satisfaction Feedback.
        {{ $total_vss_respondents ?? 0 }} (out of {{ $total_respondents ?? 0 }}, or {{ $percentage_vss_respondents ?? 0 }}%) of the respondents rated the CSF as either very satisfied (VS) or satisfied (S),
        which resulted in an overall average Customer Satisfaction Index (CSI) of {{ $csi ?? 0 }}%,
        a Net Promoter Score of {{ $ave_net_promoter_score ?? 0 }}%, and an average Likert Scale Rating of {{ $lsr_average ?? 0 }}.
        <br><br>
        The Customer Satisfaction Survey resulted in an Overall Customer Satisfaction Score Rating of {{ $customer_satisfaction_rating ?? 0 }}%
        for the {{ strtolower($form->selected_quarter) }} of {{ $form->selected_year }}, which
        {{ ($customer_satisfaction_rating ?? 0) < 95 ? 'did not achieve' : 'achieved' }}
        its quality objective of at least 95% of customers being satisfied with the S&T services.
    </div>
</td>
<td style="width:2%; border:none; padding:0;"></td>
<td class="bottom-box" style="width:49%; vertical-align:top;">
    <div class="bg-header text-center">SATISFACTION RATING</div>
    <table style="border:none; margin-top:4px;">
    <tr>
        <td style="width:150px; text-align:center; vertical-align:middle; border:none; padding:0;">
            <img src="{{ $pieChartDataUri }}" width="150" height="150">
        </td>
        <td style="vertical-align:middle; border:none; padding:0 0 0 14px;">
            <table style="font-size:11px;">
                @foreach($pieCounts as $label => $count)
                    @php $pct = $pieTotal > 0 ? number_format(($count / $pieTotal) * 100, 2) : '0.00'; @endphp
                    <tr>
                        <td class="text-left" style="padding:6px 7px;"><span class="legend-dot" style="background-color:{{ $pieColors[$loop->index] }}; width:11px; height:11px;"></span>{{ $label }}</td>
                        <td class="text-center" style="padding:6px 7px;">{{ $pct }}%</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
    </table>
</td>
</tr>
</table>

@php
    // Placeholder answers ("None", "n/a", "-") are dropped by the shared
    // filter, which also puts complaints ahead of comments.
    [$printComplaints, $printComments] = \App\Support\FeedbackFilter::split($comments ?? []);
    $sortedComments = array_merge($printComplaints, $printComments);
    $submittedTotal = ($total_comments ?? 0) + ($total_complaints ?? 0);
    $omittedNote = $submittedTotal > count($sortedComments)
        ? ' &mdash; of ' . $submittedTotal . ' submitted'
        : '';
@endphp
<div class="bg-header" style="margin-top:10px;">COMMENTS/COMPLAINTS ({{ count($printComments) }} comments, {{ count($printComplaints) }} complaints{!! $omittedNote !!})</div>
<div class="bottom-box" style="margin-top:4px;">
    @forelse($sortedComments as $i => $comment)
        <div class="comment-item">
            {{ $i + 1 }}. {{ $comment['text'] }}
            <span class="tag {{ $comment['isComplaint'] ? 'tag-complaint' : 'tag-comment' }}">{{ $comment['isComplaint'] ? 'COMPLAINT' : 'COMMENT' }}</span>
        </div>
    @empty
        <div class="comment-item">None.</div>
    @endforelse
</div>

<table style="border:none; margin-top:24px; font-size:12px;">
<tr>
    <td style="width:40%; vertical-align:top; border:none; padding:0;">
        Prepared by:
        <div style="margin-top:12px; margin-left:16px;">
            @if(!empty($prepared_by['name']))
                <span style="text-decoration:underline; font-weight:bold;">{{ $prepared_by['name'] }}</span><br>
            @endif
            @if(!empty($prepared_by['designation']))
                {{ $prepared_by['designation'] }}
            @endif
        </div>
    </td>
    <td style="width:20%; border:none; padding:0;"></td>
    <td style="width:40%; vertical-align:top; border:none; padding:0;">
        Noted by:
        <div style="margin-top:12px; margin-left:16px;">
            @if(!empty($noted_by['name']))
                <span style="text-decoration:underline; font-weight:bold;">{{ $noted_by['name'] }}</span><br>
            @endif
            @if(!empty($noted_by['designation']))
                {{ $noted_by['designation'] }}
            @endif
        </div>
    </td>
</tr>
</table>

</body>
</html>
