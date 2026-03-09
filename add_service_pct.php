<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

// Find the pattern in getAllUnitsData function where we need to add service_totals percentage calculation
// This is in the function around line 3680-3690

$search = "        \$grand_pct_na = \$grand_total_ratings > 0 ? (\$grand_na_count / \$grand_total_ratings) * 100 : 0;

        return [
            'units_data' => \$units_data,";

$replace = "        \$grand_pct_na = \$grand_total_ratings > 0 ? (\$grand_na_count / \$grand_total_ratings) * 100 : 0;

        // Calculate percentage for service_totals (Strongly Agree + Agree combined)
        foreach ([1, 2, 3] as \$serviceId) {
            if (isset(\$service_totals[\$serviceId])) {
                // Use total_respo * 6 (number of rating questions) as the base for percentage calculation
                \$service_totals[\$serviceId]['pct_strongly_agree_agree'] = (\$service_totals[\$serviceId]['total_respo'] * 6) > 0 
                    ? (\$service_totals[\$serviceId]['strongly_agree_agree_count'] / (\$service_totals[\$serviceId]['total_respo'] * 6)) * 100 
                    : 0;
            }
        }

        return [
            'units_data' => \$units_data,";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents('app/Http/Controllers/ReportController.php', $content);
    echo "Edit successful - service_totals percentage calculation added to getAllUnitsData!\n";
} else {
    echo "Pattern not found in getAllUnitsData\n";
}
?>
