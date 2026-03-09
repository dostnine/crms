<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

$old = "                'cc3_total' => \$grand_cc3_total,
            ],
        ];
    }

    /**
     * Calculate CSI for a single unit using Weighted Sum method";

$new = "                'cc3_total' => \$grand_cc3_total,
            ],
            // Service totals for SERVICE CATEGORY TOTALS SUMMARY
            'service_totals' => \$service_totals,
            // Grand strongly agree + agree count for overall total
            'grand_strongly_agree_agree_count' => \$grand_strongly_agree_count + \$grand_agree_count,
            // Grand percentage of strongly agree + agree
            'grand_pct_strongly_agree_agree' => \$grand_total_ratings > 0 
                ? ((\$grand_strongly_agree_count + \$grand_agree_count) / \$grand_total_ratings) * 100 
                : 0,
        ];
    }

    /**
     * Calculate CSI for a single unit using Weighted Sum method";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents('app/Http/Controllers/ReportController.php', $content);
    echo "Edit successful!\n";
} else {
    echo "Pattern not found. Trying alternative pattern...\n";
    
    // Try alternative pattern
    $old2 = "                'cc3_total' => \$grand_cc3_total,
            ],
        ];
    }

    /**
     * Calculate CSI";
    
    $new2 = "                'cc3_total' => \$grand_cc3_total,
            ],
            // Service totals for SERVICE CATEGORY TOTALS SUMMARY
            'service_totals' => \$service_totals,
            // Grand strongly agree + agree count for overall total
            'grand_strongly_agree_agree_count' => \$grand_strongly_agree_count + \$grand_agree_count,
            // Grand percentage of strongly agree + agree
            'grand_pct_strongly_agree_agree' => \$grand_total_ratings > 0 
                ? ((\$grand_strongly_agree_count + \$grand_agree_count) / \$grand_total_ratings) * 100 
                : 0,
        ];
    }

    /**
     * Calculate CSI";
    
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, $new2, $content);
        file_put_contents('app/Http/Controllers/ReportController.php', $content);
        echo "Edit successful with alternative pattern!\n";
    } else {
        echo "Alternative pattern not found either.\n";
    }
}
?>
