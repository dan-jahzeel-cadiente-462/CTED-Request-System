# Reports Section Improvement Plan

## Steps
- [x] Step 0: Understand the task and read relevant files
- [x] Step 1: Analyze current ReportsController, templates, repository, and entity
- [x] Step 2: Present plan to user for approval
- [x] Step 3: Update `src/Controller/Admin/ReportsController.php`
  - [x] Compute full `minReportDate` / `maxReportDate` (before pagination) to fix date-picker range
  - [x] Pass `totalDates` for recent-dates pagination
  - [x] Add `exportJson` route for JSON export
- [x] Step 4: Make `templates/admin/reports/pdf.html.twig` a standalone print-ready document
- [x] Step 5: Update `templates/admin/reports/index.html.twig`
  - [x] Replace three buttons with a unified Export dropdown (View PDF, CSV, Excel, JSON, Print)
  - [x] Fix date input min/max to use full report date range
  - [x] Add pagination controls to "Recent report dates" list
  - [x] Improve selected-date header + export + table layout
- [x] Step 6: Verify with tests / lint
  - [x] PHP syntax check passed (`php -l`)
  - [x] Route names verified to match between controller and template
