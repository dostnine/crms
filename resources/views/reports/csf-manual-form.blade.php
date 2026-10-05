<?php
// The blank Customer Satisfaction Feedback form for clients who answer on
// paper. It follows the Word form that used to be kept on Google Drive, set
// to fit one sheet printed on both sides.

$questions = [
    'I am satisfied with the service I availed (Overall)',
    'I spent a reasonable amount of time for my transaction (Responsiveness)',
    'The office followed the transaction requirements and steps based on the information provided (Reliability)',
    'The steps (including payment) I needed for my transaction were easy and simple (Access & Facilities)',
    'I easily found information about my transaction from the office website (Communication)',
    'I paid a reasonable amount of fees for my transaction. (if the service is free mark the N/A option (Costs))',
    'I am confident my online transaction was secure (Integrity)',
    "The office's online support was available, and (if asked questions) online support was quick to respond (Assurance)",
    'I got what I needed from the government office, or (if denied) denial of request was sufficiently explained to me (Outcome)',
];
// Questions 1-4 close the front of the sheet; the rest go on the back.
$frontQuestions = 4;

$ratings = [
    ['very-happy', 'Strongly Agree'],
    ['happy', 'Agree'],
    ['neutral', 'Neither agree nor Disagree'],
    ['sad', 'Disagree'],
    ['very-sad', 'Strongly Disagree'],
];
$importance = [5 => 'Very Important', 4 => 'Important', 3 => 'Moderately', 2 => 'Slightly', 1 => 'Not at all'];

// dompdf only draws SVG that is loaded as an image, so each face is a small
// SVG document in a data: URI. The mouths are straight lines and cubic
// curves; php-svg-lib, which dompdf rasterizes SVG with, can drop arcs.
$face = function (string $mood) {
    $line = 'fill="none" stroke="#1a1a1a" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"';
    $mouths = [
        'very-happy' => '<path d="M6.6 13.2 L17.4 13.2 C16.6 18 7.4 18 6.6 13.2 Z" fill="#ffffff" stroke="#1a1a1a" stroke-width="1.25" stroke-linejoin="round"/>',
        'happy' => '<path d="M7.4 14 C9 17.6 15 17.6 16.6 14" ' . $line . '/>',
        'neutral' => '<path d="M8 15.4 L16 15.4" ' . $line . '/>',
        'sad' => '<path d="M7.6 16.8 C9.2 13.6 14.8 13.6 16.4 16.8" ' . $line . '/>',
        'very-sad' => '<path d="M7.4 17.4 C9 13.4 15 13.4 16.6 17.4" ' . $line . '/>'
            . '<path d="M6.4 6.6 L10 8.2" ' . $line . '/><path d="M17.6 6.6 L14 8.2" ' . $line . '/>',
    ];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">'
        . '<circle cx="12" cy="12" r="10.4" fill="#ffffff" stroke="#1a1a1a" stroke-width="1.3"/>'
        . '<circle cx="8.6" cy="10.2" r="1.25" fill="#1a1a1a"/>'
        . '<circle cx="15.4" cy="10.2" r="1.25" fill="#1a1a1a"/>'
        . $mouths[$mood]
        . '</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
};
$faces = [];
foreach ($ratings as [$mood]) {
    $faces[$mood] = $face($mood);
}
?>
<html>
<head>
<meta charset="UTF-8">
<title>Customer Satisfaction Feedback Form</title>
<style>
    @page {
        margin: 22pt 22pt 20pt 22pt;
    }
    body {
        margin: 0;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 9pt;
        line-height: 1.15;
        color: #111;
    }
    /* check mark and shaded box: not in the PDF core fonts */
    .sym {
        font-family: 'DejaVu Sans', sans-serif;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    /* The form is a stack of tables, one or a few rows each, because dompdf
       takes column widths from the style attribute of the cells in a table's
       first row (it ignores <col> and widths set by a class). Each cell draws its left, right and bottom edge; only the first
       table on a page draws a top edge. */
    .t td {
        border: 0.6pt solid #555;
        border-top: none;
        padding: 2pt 5pt;
        vertical-align: middle;
    }
    .t.first td {
        border-top: 0.6pt solid #555;
    }
    table.plain td {
        border: none !important;
        padding: 0;
        vertical-align: top;
    }
    .band td {
        height: 8pt;
        padding: 0;
        background: #bfbfbf;
    }
    .rule td {
        height: 4pt;
        padding: 0;
        background: #00b0f0;
    }
    .optional {
        font-style: italic;
        font-weight: normal;
        color: #00a3e0;
    }
    .box {
        display: inline-block;
        width: 7.5pt;
        height: 7.5pt;
        margin-right: 3pt;
        border: 0.8pt solid #222;
        border-radius: 1.5pt;
    }

    /* header */
    .brand {
        font-size: 9pt;
        font-weight: bold;
        line-height: 1.05;
        white-space: nowrap;
    }
    .brand span {
        color: #00a3e0;
    }
    .form-name {
        font-size: 6.6pt;
        font-weight: bold;
        text-align: center;
    }
    .intro {
        font-size: 10pt;
        font-weight: bold;
        text-align: center;
    }

    /* respondent details */
    .details td {
        height: 14pt;
        font-size: 9.5pt;
    }
    .details .label {
        font-weight: bold;
        text-align: right;
        padding-right: 2pt;
    }

    /* Citizen's Charter questions */
    .cc-intro {
        font-size: 8.6pt;
    }
    .cc-label {
        font-weight: bold;
        text-decoration: underline;
    }
    .cc-question {
        font-weight: bold;
    }
    .cc-options td {
        vertical-align: top;
        padding: 2pt 5pt 3pt 44pt;
        font-size: 9.5pt;
        line-height: 1.25;
    }

    /* ratings */
    .rate-intro td {
        font-size: 10pt;
        font-weight: bold;
    }
    .rate-title td {
        padding: 1pt 6pt;
        background: #00b0f0;
        font-size: 13pt;
        font-weight: bold;
        text-decoration: underline;
    }
    .q-head td {
        padding: 2pt 6pt;
        background: #ddd9c3;
        font-size: 11pt;
        font-weight: bold;
        vertical-align: top;
    }
    .q-head .q-no {
        padding-right: 0;
        border-right: none;
    }
    .q-head .q-text {
        padding-left: 2pt;
        border-left: none;
    }
    .question .choice {
        padding: 2pt 1pt 1pt;
        font-size: 6.6pt;
        font-weight: bold;
        text-align: center;
    }
    .question .choice img {
        width: 14pt;
        height: 14pt;
    }
    .question .ask {
        padding: 1.5pt 0 1pt;
        font-size: 7.2pt;
        font-weight: bold;
        text-align: center;
    }
    .gap td {
        height: 3pt;
        padding: 0;
    }
    .num {
        display: inline-block;
        width: 13pt;
        height: 13pt;
        border-radius: 6.5pt;
        background: #a6a6a6;
        color: #fff;
        font-size: 9pt;
        line-height: 13pt;
        text-align: center;
    }

    /* recommendation, comments, signature */
    .prompt td {
        font-size: 10.5pt;
        font-weight: bold;
    }
    .prompt .note {
        font-size: 8.6pt;
        font-style: italic;
        font-weight: normal;
    }
    .nps td {
        padding: 1pt 0;
        text-align: center;
    }
    .nps .scale td {
        background: #eeece1;
        font-size: 10.5pt;
    }
    .nps .box {
        width: 11pt;
        height: 11pt;
        margin: 2pt 0 0;
        border-radius: 2.5pt;
    }
    .comments td {
        height: 128pt;
    }
    .signature td {
        padding: 6pt 0 8pt;
        text-align: center;
    }
    .signature .pad {
        display: inline-block;
        width: 160pt;
        height: 54pt;
        border: 0.6pt solid #555;
    }
    .page-break {
        page-break-after: always;
    }
</style>
</head>
<body>

    {{-- ============================ Front ============================ --}}
    <table class="t first">
        <tr>
            <td rowspan="2" style="width: 27%; padding: 3pt 4pt 2pt;">
                <table class="plain">
                    <tr>
                        <td style="width: 50pt; vertical-align: middle;">
                            <img src="{{ public_path('images/dost-logo.jpg') }}" style="width: 46pt; height: 46pt;">
                        </td>
                        <td class="brand" style="vertical-align: middle;">
                            <span>D</span>EPARTMENT<br>
                            <span>O</span>F<br>
                            <span>S</span>CIENCE AND<br>
                            <span>T</span>ECHNOLOGY
                        </td>
                    </tr>
                </table>
                <div class="form-name">CUSTOMER SATISFACTION FEEDBACK</div>
            </td>
            {{-- left blank on the form: room to write the office or unit --}}
            <td style="height: 22pt;"></td>
        </tr>
        <tr>
            <td class="intro">
                This questionnaire aims to solicit your honest assessment of our services. Please take
                a minute to fill out this form and help us serve you better. Check mark
                (<span class="sym">&#10004;</span>) or shade (<span class="sym">&#9632;</span>)
                the corresponding boxes for each item. Please write legibly.
            </td>
        </tr>
    </table>

    <table class="t band"><tr><td></td></tr></table>

    <table class="t details">
        <tr>
            <td class="label" style="width: 17%; text-align: left;">Email <span class="optional">(Optional)</span>:</td>
            <td></td>
        </tr>
    </table>
    <table class="t details">
        <tr>
            <td class="label" style="width: 17%; text-align: left;">Name <span class="optional">(Optional)</span>:</td>
            <td></td>
        </tr>
    </table>
    <table class="t details">
        <tr>
            <td class="label" style="width: 13%;">Client Type:</td>
            <td style="width: 21%;"><span class="box"></span>Internal Employees</td>
            <td style="width: 16%;"><span class="box"></span>General Public</td>
            <td style="width: 23%;"><span class="box"></span>Government Employees</td>
            <td style="width: 27%;"><span class="box"></span>Business/ Organization</td>
        </tr>
    </table>
    <table class="t details">
        <tr>
            <td class="label" style="width: 13%;">Sex:</td>
            <td style="width: 21%;"><span class="box"></span>Male</td>
            <td style="width: 16%;"><span class="box"></span>Female</td>
            <td style="width: 50%;"><span class="box"></span>Prefer not to say</td>
        </tr>
    </table>
    <table class="t details">
        <tr>
            <td class="label" style="width: 13%;">Age Group:</td>
            <td style="width: 14%;"><span class="box"></span>19 or lower</td>
            <td style="width: 11%;"><span class="box"></span>20 - 34</td>
            <td style="width: 11%;"><span class="box"></span>35 - 49</td>
            <td style="width: 11%;"><span class="box"></span>50 - 64</td>
            <td style="width: 9%;"><span class="box"></span>60+</td>
            <td style="width: 31%;"><span class="box"></span>Prefer not to say</td>
        </tr>
    </table>

    <table class="t band"><tr><td></td></tr></table>

    <table class="t">
        <tr>
            <td class="cc-intro">
                <b><u>The Citizen&rsquo;s Charter questions.</u></b> The Citizen&rsquo;s Charter is an official document that
                reflects the services of a government agency/profile including its requirements, fees, and
                processing times among others.
            </td>
        </tr>
    </table>
    <table class="t">
        <tr>
            <td class="cc-label" style="width: 6.5%;">CC1:</td>
            <td class="cc-question">Which of the following best describes your awareness of a Citizen Charter (CC)?</td>
        </tr>
    </table>
    <table class="t cc-options">
        <tr>
            <td>
                <span class="box"></span>1. I know what a CC is and I saw this office&rsquo;s CC.<br>
                <span class="box"></span>2. I know what a CC is but I did NOT see this office&rsquo;s CC.<br>
                <span class="box"></span>3. I learned of the CC only when I saw this office&rsquo;s CC.<br>
                <span class="box"></span>4. I do not know what a CC and I did NOT see one in this office. (Answer &lsquo;N/A&rsquo; on CC2 and CC3)
            </td>
        </tr>
    </table>
    <table class="t">
        <tr>
            <td class="cc-label" style="width: 6.5%;">CC2:</td>
            <td class="cc-question">If aware of CC (answered 1-3 in CC1), would say that the CC of this was&hellip;?</td>
        </tr>
    </table>
    <table class="t cc-options">
        <tr>
            <td style="width: 40%; border-right: none;">
                <span class="box"></span>1. Easy to see.<br>
                <span class="box"></span>2. Somewhat easy to see.<br>
                <span class="box"></span>3. Difficult to see.
            </td>
            <td style="padding-left: 5pt; border-left: none;">
                <span class="box"></span>4. Not visible at all.<br>
                <span class="box"></span>5. N/A.
            </td>
        </tr>
    </table>
    <table class="t">
        <tr>
            <td class="cc-label" style="width: 6.5%;">CC3:</td>
            <td class="cc-question">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</td>
        </tr>
    </table>
    <table class="t cc-options">
        <tr>
            <td style="width: 40%; border-right: none;">
                <span class="box"></span>1. Helped very much.<br>
                <span class="box"></span>2. Somewhat helped.
            </td>
            <td style="padding-left: 5pt; border-left: none;">
                <span class="box"></span>3. Did not help.<br>
                <span class="box"></span>4. N/A.
            </td>
        </tr>
    </table>

    <table class="t band"><tr><td></td></tr></table>

    <table class="t rate-intro">
        <tr><td>Please rate the following attributes by checking or shading or encircling the icon image of your responses:</td></tr>
    </table>
    <table class="t rate-title">
        <tr><td>HOW WOULD YOU RATE OUR SERVICES?</td></tr>
    </table>

    @foreach ($questions as $index => $question)
        @if ($index === $frontQuestions)
            {{-- ============================ Back ============================ --}}
            <div class="page-break"></div>
        @endif
        <table class="t q-head {{ $index === $frontQuestions ? 'first' : '' }}">
            <tr>
                <td class="q-no" style="width: 3.4%;">{{ $index + 1 }}.</td>
                <td class="q-text">{{ $question }}</td>
            </tr>
        </table>
        <table class="t question">
            <tr>
                @foreach ($ratings as $position => [$mood, $label])
                    <td class="choice" style="width: {{ [18, 16, 16.5, 16, 15.5][$position] }}%;"><img src="{{ $faces[$mood] }}"><br>{{ $label }}</td>
                @endforeach
                <td class="choice" style="width: 18%; font-size: 7.2pt;">N/A</td>
            </tr>
        </table>
        <table class="t question">
            <tr><td class="ask">HOW IMPORTANT IS THIS ATTRIBUTE?</td></tr>
        </table>
        <table class="t question">
            <tr>
                @foreach ($importance as $score => $label)
                    <td class="choice" style="width: {{ [5 => 18, 4 => 16, 3 => 16.5, 2 => 16, 1 => 33.5][$score] }}%;"><span class="num">{{ $score }}</span><br>{{ $label }}</td>
                @endforeach
            </tr>
        </table>
        <table class="t gap"><tr><td></td></tr></table>
    @endforeach

    <table class="t rule"><tr><td></td></tr></table>
    <table class="t prompt">
        <tr>
            <td>
                Considering your complete experience with our agency, how likely would you recommend our services to others?
                <span class="note">(10 is the highest and 1 is the lowest)</span>
            </td>
        </tr>
    </table>
    <table class="t nps">
        <tr class="scale">
            @for ($score = 10; $score >= 1; $score--)
                <td>{{ $score }}</td>
            @endfor
        </tr>
        <tr>
            @for ($score = 10; $score >= 1; $score--)
                <td><span class="box"></span></td>
            @endfor
        </tr>
    </table>

    <table class="t rule"><tr><td></td></tr></table>
    <table class="t prompt">
        <tr>
            <td>
                Please write your comment/suggestions below.
                <span class="note">(If you rated Neither Agree nor Disagree(3) or below, please write your complaint so that we could address your concern.)</span>
                <span class="optional" style="font-size: 8pt;">(Optional)</span>
            </td>
        </tr>
    </table>
    <table class="t comments"><tr><td></td></tr></table>

    <table class="t rule"><tr><td></td></tr></table>
    <table class="t prompt">
        <tr>
            <td>Please write your signature on the box. <span class="optional" style="font-size: 8pt;">(Optional)</span></td>
        </tr>
    </table>
    <table class="t signature"><tr><td><span class="pad"></span></td></tr></table>

</body>
</html>
