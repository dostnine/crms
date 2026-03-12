# TODO: Fix CSAT 0.00% Display in All Services Units Dashboard

## Task Analysis
The CSAT (Customer Satisfaction) is showing 0.00% in the All Services Units Dashboard because:
1. The template uses `props.csat_total` which doesn't exist in the props definition
2. The actual data is passed as `percentage_vss_respondents` from the backend

## Fix Plan
- [x] 1. Analyze the code to find the root cause
- [x] 2. Fix the template in Index.vue to use `percentage_vss_respondents` instead of `csat_total`

## Implementation Steps
1. Edit `resources/js/Pages/CSI/AllServicesUnits/Index.vue`
2. Change `props.csat_total` to `props.percentage_vss_respondents` in the template

## Status: FIXED ✓

