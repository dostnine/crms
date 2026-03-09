<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

// Find the location where we need to add service_totals update
// It should be after calculating $strongly_agree_count and $agree_count, and before grand totals

$old = "                \$grand_strongly_agree_count += \$strongly_agree_count;
                \$grand_agree_count += \$agree_count;
                \$grand_neither_count += \$neither_count;
                \$grand_disagree_count += \$disagree_count;
                \$grand_strongly_disagree_count += \$strongly_disagree_count;
                \$grand_na_count += \$na_count;
            }
        }";

$new = "                \$grand_strongly_agree_count += \$strongly_agree_count;
                \$grand_agree_count += \$agree_count;
                \$grand_neither_count += \$neither_count;
                \$grand_disagree_count += \$disagree_count;
                \$grand_strongly_disagree_count += \$strongly_disagree_count;
                \$grand_na_count += \$na_count;
                
                // Update service totals
                \$serviceId = \$service->id;
                if (isset(\$service_totals[\$serviceId])) {
                    \$service_totals[\$serviceId]['total_respo'] += \$total_respo;
                    \$service_totals[\$serviceId]['strongly_agree_agree_count'] += \$strongly_agree_count + \$agree_count;
                }
            }
        }";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents('app/Http/Controllers/ReportController.php', $content);
    echo "Edit successful - service_totals population added!\n";
} else {
    echo "Pattern not found. Trying alternative pattern...\n";
    
    // Try with double escaping
    $old2 = "                \$grand_strongly_agree_count += \$strongly_agree_count;\n                \$grand_agree_count += \$agree_count;\n                \$grand_neither_count += \$neither_count;\n                \$grand_disagree_count += \$disagree_count;\n                \$grand_strongly_disagree_count += \$strongly_disagree_count;\n                \$grand_na_count += \$na_count;\n            }\n        }";
    
    $new2 = "                \$grand_strongly_agree_count += \$strongly_agree_count;\n                \$grand_agree_count += \$agree_count;\n                \$grand_neither_count += \$neither_count;\n                \$grand_disagree_count += \$disagree_count;\n                \$grand_strongly_disagree_count += \$strongly_disagree_count;\n                \$grand_na_count += \$na_count;\n                \n                // Update service totals\n                \$serviceId = \$service->id;\n                if (isset(\$service_totals[\$serviceId])) {\n                    \$service_totals[\$serviceId]['total_respo'] += \$total_respo;\n                    \$service_totals[\$serviceId]['strongly_agree_agree_count'] += \$strongly_agree_count + \$agree_count;\n                }\n            }\n        }";
    
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, $new2, $content);
        file_put_contents('app/Http/Controllers/ReportController.php', $content);
        echo "Edit successful with alternative pattern!\n";
    } else {
        echo "Alternative pattern not found either.\n";
    }
}
?>
