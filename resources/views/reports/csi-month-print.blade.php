<?php
// Mirrors ByUnitMonthly.vue exactly -- only the rendering mechanism changed
// (Printd popup -> Blade/dompdf PDF), never the format.
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

// The Vue template hides every zero via `v-if="total > 0"`.
$hideZero = function ($v) {
    return (is_numeric($v) && (float) $v > 0) ? $v : '';
};

$readableDate = function ($value) {
    if (!$value) {
        return '';
    }
    try {
        return strtoupper(\Carbon\Carbon::parse($value)->format('F d, Y'));
    } catch (\Throwable $e) {
        return $value;
    }
};

$isByDate = ($form->csi_type ?? '') === 'By Date';
$periodLabel = $isByDate
    ? $readableDate($form->date_from ?? null) . ' TO ' . $readableDate($form->date_to ?? null)
    : trim(($form->selected_month ?? '') . '  ' . ($form->selected_year ?? ''));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 10mm; }
    * { font-family: 'DejaVu Sans', sans-serif; }
    body { font-size: 11px; line-height: 1.15; color: #12243a; }
    table { border-collapse: collapse; width: 100%; }
    .grid, .grid td, .grid th { border: 1px solid #333; }
    .grid td, .grid th { padding: 2px 4px; vertical-align: middle; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .bold { font-weight: bold; }
    /* Same navy section banner the quarterly/yearly reports use. */
    .bg-header {
        background-color: #1b365d;
        color: #ffffff;
        font-weight: bold;
        font-size: 11px;
        letter-spacing: 0.3px;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    .bg-blue-200 { background-color: #e6f1ff; }
    .bg-gray-200 { background-color: #eef0f4; }
    /* Rating-scale row tints, matching the quarterly report. */
    .row-vs { background-color: #eafaf0; }
    .row-s  { background-color: #f4fbf6; }
    .row-n  { background-color: #ffffff; }
    .row-d  { background-color: #fff8ec; }
    .row-vd { background-color: #fdf0ef; }
    .meta-line { font-size: 11px; font-weight: bold; }
    .box { border: 1px solid #cbd7ea; padding: 5px; }
    .tag { font-size: 9px; font-weight: bold; padding: 0 3px; }
    .tag-complaint { color: #c0392b; }
    .tag-comment { color: #2a568f; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

<table style="border:none; margin-bottom:6px;">
    <tr>
        <td style="width:60px; border:none; padding:0;"></td>
        <td class="text-center" style="border:none; padding:0; vertical-align:middle;">
            <div style="font-size:14px; font-weight:bold;">CUSTOMER SATISFACTION FEEDBACK</div>
            <div style="font-size:11px;">SUMMARY REPORT FOR <u>{{ $periodLabel }}</u></div>
        </td>
        <td style="width:60px; border:none; padding:0; text-align:right;">
            <img src="{{ public_path('images/dost-logo.jpg') }}" style="width:48px; height:48px;">
        </td>
    </tr>
</table>

<table style="border:none; margin-bottom:10px;">
    <tr>
        <td class="meta-line" style="border:none; padding:0; vertical-align:top;">
            Services : <u>{{ $service['services_name'] ?? '' }}</u>
        </td>
        <td class="meta-line" style="border:none; padding:0; vertical-align:top; text-align:right;">
            Services Unit : <u>{{ $unitName }}</u><br>
            @if(!empty($form->client_type))<u>{{ $form->client_type }}</u><br>@endif
            @if(!empty($form->selected_unit_psto['psto_name']))<u>{{ $form->selected_unit_psto['psto_name'] }}</u><br>@endif
            @if(!empty($form->selected_sub_unit['sub_unit_name']))<u>{{ $form->selected_sub_unit['sub_unit_name'] }}</u>@endif
            @if(!empty($form->sub_unit_type['type_name']))<u>{{ $form->sub_unit_type['type_name'] }}</u>@endif
            @if(!empty($form->selected_sub_unit_psto['psto_name']))<u>{{ $form->selected_sub_unit_psto['psto_name'] }}</u>@endif
        </td>
    </tr>
</table>

@if(!empty($cc_data))
<div class="bg-header">PART I: CITIZEN'S CHARTER(CC)</div>
<table class="grid" style="margin-bottom:20px;">
    <tr>
        <th></th>
        <th></th>
        <th style="font-size:12px;">Number of Respondents who selected</th>
    </tr>
    <tr class="bg-blue-200">
        <th>CC1</th>
        <th colspan="2" class="text-left">Which of the following best describes your awareness of a CC?</th>
    </tr>
    <tr>
        <td class="text-center">1</td>
        <td class="text-left">I know what a CC is and I saw this office's CC</td>
        <td class="text-center">{{ $hideZero($cc_data['cc1_data']['cc1_ans1'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">2</td>
        <td class="text-left">I know what a CC is but I did NOT see this office's CC</td>
        <td class="text-center">{{ $hideZero($cc_data['cc1_data']['cc1_ans2'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">3</td>
        <td class="text-left">I learned the CC when I saw this office's CC</td>
        <td class="text-center">{{ $hideZero($cc_data['cc1_data']['cc1_ans3'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">4</td>
        <td class="text-left">I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)</td>
        <td class="text-center">{{ $hideZero($cc_data['cc1_data']['cc1_ans4'] ?? 0) }}</td>
    </tr>
    <tr class="bg-blue-200">
        <td></td>
        <td class="text-left bold">Total</td>
        <td class="text-center bold">{{ $cc_data['cc1_data']['cc1_total'] ?? 0 }}</td>
    </tr>
    <tr class="bg-blue-200">
        <th>CC2</th>
        <th colspan="2" class="text-left">If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?</th>
    </tr>
    <tr>
        <td class="text-center">1</td>
        <td class="text-left">Easy to see</td>
        <td class="text-center">{{ $hideZero($cc_data['cc2_data']['cc2_ans1'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">2</td>
        <td class="text-left">Somewhat easy to see</td>
        <td class="text-center">{{ $hideZero($cc_data['cc2_data']['cc2_ans2'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">3</td>
        <td class="text-left">Difficult to see</td>
        <td class="text-center">{{ $hideZero($cc_data['cc2_data']['cc2_ans3'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">4</td>
        <td class="text-left">Not visible at all</td>
        <td class="text-center">{{ $hideZero($cc_data['cc2_data']['cc2_ans4'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">5</td>
        <td class="text-left">N/A</td>
        <td class="text-center">{{ $hideZero($cc_data['cc2_data']['cc2_ans5'] ?? 0) }}</td>
    </tr>
    <tr class="bg-blue-200">
        <td></td>
        <td class="text-left bold">Total</td>
        <td class="text-center bold">{{ $cc_data['cc2_data']['cc2_total'] ?? 0 }}</td>
    </tr>
    <tr class="bg-blue-200">
        <th>CC3</th>
        <th colspan="2" class="text-left">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</th>
    </tr>
    <tr>
        <td class="text-center">1</td>
        <td class="text-left">Helped Very Much</td>
        <td class="text-center">{{ $hideZero($cc_data['cc3_data']['cc3_ans1'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">2</td>
        <td class="text-left">Somewhat helped</td>
        <td class="text-center">{{ $hideZero($cc_data['cc3_data']['cc3_ans2'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">3</td>
        <td class="text-left">Did not help</td>
        <td class="text-center">{{ $hideZero($cc_data['cc3_data']['cc3_ans3'] ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-center">4</td>
        <td class="text-left">N/A</td>
        <td class="text-center">{{ $hideZero($cc_data['cc3_data']['cc3_ans4'] ?? 0) }}</td>
    </tr>
    <tr class="bg-blue-200">
        <td></td>
        <td class="text-left bold">Total</td>
        <td class="text-center bold">{{ $cc_data['cc3_data']['cc3_total'] ?? 0 }}</td>
    </tr>
</table>
@endif

<div class="bg-header">PART II: CUSTOMER RATING OF SERVICE QUALITY</div>
@php
    // The rating scale runs across columns here (not down rows as in the
    // quarterly report), so the same colour language is applied per column.
    $scaleTints = ['row-vs', 'row-s', 'row-n', 'row-d', 'row-vd'];
@endphp
<table class="grid" style="margin-bottom:40px;">
    <tr class="text-center bg-blue-200 bold">
        <td rowspan="2">Service Quality Attributes</td>
        <td>5</td>
        <td>4</td>
        <td>3</td>
        <td>2</td>
        <td>1</td>
        <td rowspan="2">TOTAL SCORE</td>
        <td rowspan="2">Likert Scale Rating</td>
        <td rowspan="2">GAP</td>
    </tr>
    <tr class="text-center bg-blue-200 bold">
        <td>Very Satisfied</td>
        <td>Satisfied</td>
        <td>Neither</td>
        <td>Dissatisfied</td>
        <td>Very Dissatisfied</td>
    </tr>
    @foreach($dimensions as $index => $dimension)
        @php $d = $index + 1; @endphp
        <tr>
            <td class="text-left">[{{ $d }}] {{ is_array($dimension) ? $dimension['name'] : $dimension->name }}</td>
            @foreach(array_values($y_totals[$d] ?? []) as $i => $total)
                <td class="text-center {{ $scaleTints[$i] ?? '' }}">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($x_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($likert_scale_rating_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($gap_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
        </tr>
    @endforeach
    <tr class="text-center bg-gray-200 bold">
        <td class="text-left">TOTAL SCORE</td>
        <td>{{ $hideZero($grand_vs_total ?? 0) }}</td>
        <td>{{ $hideZero($grand_s_total ?? 0) }}</td>
        <td>{{ $hideZero($grand_n_total ?? 0) }}</td>
        <td>{{ $hideZero($grand_d_total ?? 0) }}</td>
        <td>{{ $hideZero($grand_vd_total ?? 0) }}</td>
        <td>{{ $hideZero($x_grand_total ?? 0) }}</td>
        <td>{{ $hideZero($lsr_grand_total ?? 0) }}</td>
        <td>{{ $hideZero($gap_grand_total ?? 0) }}</td>
    </tr>
</table>

<div class="bg-header page-break">PART III: IMPORTANCE OF THIS ATTRIBUTE</div>
<table class="grid">
    <tr class="text-center bg-blue-200 bold">
        <td rowspan="2">Importance Service Quality Attributes</td>
        <td>5</td>
        <td>4</td>
        <td>3</td>
        <td>2</td>
        <td>1</td>
        <td rowspan="2">TOTAL SCORE</td>
        <td rowspan="2">Likert Scale Rating</td>
        <td rowspan="2">WF</td>
        <td rowspan="2">SS</td>
        <td rowspan="2">WS</td>
    </tr>
    <tr class="text-center bg-blue-200 bold">
        <td>Very Important</td>
        <td>Important</td>
        <td>Moderately Important</td>
        <td>Slightly Important</td>
        <td>Not All Important</td>
    </tr>
    @foreach($dimensions as $index => $dimension)
        @php $d = $index + 1; @endphp
        <tr>
            <td class="text-left">[{{ $d }}] {{ is_array($dimension) ? $dimension['name'] : $dimension->name }}</td>
            @foreach(array_values($importance_rate_score_totals[$d] ?? []) as $i => $total)
                <td class="text-center {{ $scaleTints[$i] ?? '' }}">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($x_importance_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($importance_ilsr_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($wf_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($ss_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
            @foreach(($ws_totals[$d] ?? []) as $total)
                <td class="text-center">{{ $hideZero($total) }}</td>
            @endforeach
        </tr>
    @endforeach
</table>

<table class="grid text-center" style="margin-top:20px;">
    <tr>
        <td class="text-left">Total No. of Respondents/Customers:</td>
        <td class="bold">{{ $hideZero($total_respondents ?? 0) }}</td>
        <td class="text-left">Customer Satisfaction Index (CSI) :</td>
        <td class="bold">{{ $hideZero($customer_satisfaction_index ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-left">Total No. of Respondents/Customers who rated VS/S:</td>
        <td class="bold">{{ ($customer_satisfaction_index ?? 0) > 0 ? ($total_vss_respondents ?? '') : '' }}</td>
        <td class="text-left">Net Promotion Score:</td>
        <td class="bold">{{ $hideZero($net_promoter_score ?? 0) }}</td>
    </tr>
    <tr>
        <td class="text-left">Percentage of Respondents/Customers who rated VS/S:</td>
        <td class="bold">{{ $hideZero($percentage_vss_respondents ?? 0) }}</td>
        <td class="text-left">Percentage of Promoters:</td>
        <td class="bold">{{ $hideZero($percentage_promoters ?? 0) }}</td>
    </tr>
    <tr>
        <td style="border:none;"></td>
        <td style="border:none;"></td>
        <td class="text-left">Percentage of Detractors:</td>
        <td class="bold">{{ $hideZero($percentage_detractors ?? 0) }}</td>
    </tr>
    <tr class="bg-blue-200 bold">
        <td class="text-left">Customer Satisfaction Rating :</td>
        <td>{{ $hideZero($customer_satisfaction_rating ?? 0) }}</td>
        <td class="text-left">Likert Scale Rating(Average):</td>
        <td>{{ $hideZero($lsr_grand_total ?? 0) }}</td>
    </tr>
</table>

@php
    // Placeholder answers ("None", "n/a", "-") are dropped by the shared
    // filter, which also puts complaints ahead of comments.
    [$printComplaints, $printComments] = \App\Support\FeedbackFilter::split($comments ?? []);
    $printFeedback = array_merge($printComplaints, $printComments);
@endphp
<div class="bg-header" style="margin-top:20px;">COMMENTS/COMPLAINTS ({{ count($printComments) }} comments, {{ count($printComplaints) }} complaints)</div>
<div class="box">
    @forelse($printFeedback as $comment)
        <div style="padding:2px 0;">
            - {{ $comment['text'] }}
            <span class="tag {{ $comment['isComplaint'] ? 'tag-complaint' : 'tag-comment' }}">{{ $comment['isComplaint'] ? 'COMPLAINT' : 'COMMENT' }}</span>
        </div>
    @empty
        none
    @endforelse
</div>

<div class="bg-header" style="margin-top:14px;">ANALYSIS</div>
<div class="box">
    <div style="text-align:justify;">
        The <span>{{ $unitName }}</span> unit had <span>{{ $total_respondents ?? 0 }}</span> respondents who rated the CSF,
        and <span>{{ $total_vss_respondents ?? 0 }}</span> (or <span>{{ $percentage_vss_respondents ?? 0 }}</span>%) of those respondents rated
        the unit with satisfied responses (VS &amp; S) for all service quality attributes. The <span>{{ $unitName }}</span> unit had a
        <span>{{ $customer_satisfaction_index ?? 0 }}</span>% Customer Satisfaction Index as well as a Net Promoter Score of <span>{{ $net_promoter_score ?? 0 }}</span>.
        The Customer Satisfaction Rating for the <span>{{ $unitName }}</span>
        unit is <span>{{ $customer_satisfaction_rating ?? 0 }}</span>%,
        which @if(($customer_satisfaction_rating ?? 0) < 95)<span>does not</span> @endif achieved its functional objective of 95% of customer surveyed are at least satisfied with the S&amp;T services.
    </div>
</div>

<table style="border:none; margin-top:20px;">
<tr>
    <td style="width:50%; border:none; padding:0; vertical-align:top;">
        Prepared by : <br>
        <div style="margin-left:50px;">
            @if(!empty($prepared_by['name']))<u>{{ $prepared_by['name'] }}</u>@endif<br>
            @if(!empty($prepared_by['designation'])){{ $prepared_by['designation'] }}@endif
        </div>
    </td>
    <td style="width:50%; border:none; padding:0; vertical-align:top;">
        Noted by :
        <div style="margin-left:60px;">
            @if(!empty($noted_by['name']))<u>{{ $noted_by['name'] }}</u>@endif<br>
            @if(!empty($noted_by['designation'])){{ $noted_by['designation'] }}@endif
        </div>
    </td>
</tr>
</table>

</body>
</html>
