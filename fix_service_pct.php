<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

// Add percentage calculation for service_totals before the return statement

$old = "        // Grand total ratings for percentage calculation
        \$grand_total_ratings = \$grand_strongly_agree_count + \$grand_agree_count + \$grand_neither_count + \$grand_disagree_count + \$grand_strongly_disagree_count + \$grand_na_count;

        // Grand percentages for rating categories";

$new = "        // Grand total ratings for percentage calculation
        \$grand_total_ratings = \$grand_strongly_agree_count + \$grand_agree_count + \$grand_neither_count + \$grand_disagree_count + \$grand_strongly_disagree_count + \$grand_na_count;

        // Calculate percentage for service_totals (Strongly Agree + Agree combined)
        foreach ([1, 2, 3] as \$serviceId) {
            if (isset(\$service_totals[\$serviceId])) {
                // Use total_respo * 6 (number of rating questions) as the base for percentage calculation
                \$service_totals[\$serviceId]['pct_strongly_agree_agree'] = (\$service_totals[\$serviceId]['total_respo'] * 6) > 0 
                    ? (\$service_totals[\$serviceId]['strongly_agree_agree_count'] / (\$service_totals[\$serviceId]['total_respo'] * 6)) * 100 
                    : 0;
            }
        }

        // Grand percentages for rating categories";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents('app/Http/Controllers/ReportController.php', $content);
    echo "Edit successful - service_totals percentage calculation added!\n";
} else {
    echo "Pattern not found.\n";
}
?>
