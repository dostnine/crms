# CSI All Services Units Report Fix Plan

## Issues Identified:
1. **Data Passing Issue in Index.vue**: The MonthlyContent component receives incorrect data structure
2. **Data Structure Mismatch**: Frontend expects different key names than what controller provides
3. **Service Totals Percentage Calculation**: Uses formula with `total_respo * 6` which may be incorrect

## Fixes to Implement:

### 1. Fix Index.vue - Correct data passing to MonthlyContent
- Pass proper data structure to MonthlyContent component

### 2. Fix Content.vue - Match expected data structure
- Update references to use correct data keys from controller

### 3. Fix Service Category Totals Calculation
- Update percentage calculation in getAllUnitsData and getAllUnitsDataByQuarter

### 4. Add missing grand total data
- Ensure grand_pct_strongly_agree_agree is properly calculated and returned

