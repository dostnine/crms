# TODO List

## Current Tasks

### Make CSF Form Accept Longtext Comments [✅ Complete]

**Steps:**
- [✅] 1. Update TODO.md with new task tracking
- [✅] 2. Enhanced textarea in Survey-Forms/Index.vue: rows=10, resizable, min-height for long comments UX
- [✅] 3. Verified backend comment field (text/longtext capable)

**Changes:** CSF form now better supports long comments with larger, expandable textarea. DB already handles long text.

---

## Previous Tasks

### Fix Comments Not Displaying in CSI All Services Units

**Steps:**
- [✅] 1. Add comments section to Yearly/Content.vue (copy from Monthly/Content.vue)
- [ ] 2. Unify backend comments fetching logic in ReportController.php (use consistent JOIN/map across monthly/quarterly/yearly methods)
- [ ] 3. Enhance normalizeComment() in Content.vue/AltContent.vue (better null handling)
- [ ] 4. Test monthly/quarterly/yearly reports with sample comments data
- [ ] 5. Verify print formats unchanged
- [ ] 6. Update TODO.md as complete ✅

**Current: Step 1 ✅ Complete. Proceeding to backend unification (step 2).**
