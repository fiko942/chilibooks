# Dashboard Real Live Data Implementation Spec

**Goal:** Replace dashboard placeholders with production database metrics and simplify the period filter into four fixed options: `All`, current month, and the two prior months.

**Context:** The current dashboard already has a month-based controller and view structure. This spec tightens the data rules so the dashboard always reads live production data, keeps the period selector simple for mobile, and moves the add-transaction action to the far right of the header row.

---

## User-Facing Behavior

The dashboard shows live data only. The following sections are in scope:
- `Total Penjualan`
- `Total Keuntungan`
- `Transaksi`
- `Pesanan`
- `Transaksi Terbaru`
- `Pelanggan Loyal`
- `Tren Penjualan`

The period selector is a dropdown, not a button group. It always contains exactly four options:
- `All`
- current month
- previous month
- two months ago

Default selection is the current month. For month-based selections, the dashboard covers the date range from day 1 through the last day of the selected month. For `All`, the dashboard covers all transactions across all time.

The `Tambah Transaksi` button remains on the same row as the filter, but it is visually pushed to the right so it does not sit next to the dropdown.

---

## Data Rules

### 1) Live database is the source of truth
All dashboard values must come from the production database connection configured in `.env`. No dummy values, mock values, or front-end-only placeholders may remain for the dashboard cards or lists.

### 2) Month-based filtering
For a month selection, use:
- start date: `YYYY-MM-01`
- end date: last day of that month

For `All`, do not apply any date restriction. The dashboard should aggregate all transactions in the database.

### 3) Metrics
The dashboard cards should be computed from the selected period:
- `Total Penjualan` = sum of completed transaction totals
- `Total Keuntungan` = sum of completed transaction profits
- `Transaksi` = total transaction count in the selected period
- `Pesanan` = completed transaction count in the selected period

### 4) Trend chart
For a month selection, the trend chart should bucket that month into a small fixed number of visual buckets suitable for mobile, using the real daily sales in that month.

For `All`, the trend chart should still remain usable on mobile. The implementation should aggregate the full historical data into the same fixed 7 visual buckets instead of trying to render every day at once.

### 5) Loyal customers
`Pelanggan Loyal` should be derived from completed transactions in the selected period, ordered by transaction count and then by total spend. Show only the top results that fit the mobile layout.

### 6) Latest transactions
`Transaksi Terbaru` should list the most recent transactions in the selected period, ordered by transaction date and transaction id descending. Status badges should reflect the real transaction status.

---

## Architecture

Keep the current split between controller, helper, and view:
- `DashboardController` performs all database reads and period selection logic.
- `chili_helper.php` provides reusable month/period formatting helpers.
- `app/Views/dashboard/index.php` renders the selected period, cards, chart, and lists.
- `public/assets/chilibooks.css` handles the header layout and the mobile presentation of the dashboard blocks.

The controller should continue to build the month option list, but that list must now be fixed to four values only. The controller should also continue to catch database errors and render a safe fallback message if production database access fails.

---

## Header Layout

The dashboard header row should use three visual zones:
1. left: title and explanatory copy
2. middle: period dropdown
3. right: `Tambah Transaksi`

On mobile, the layout should remain one logical row, but the right action should be visually separated by spacing or flex behavior so it reads as a trailing action, not a competing control. The dropdown and button must not touch or visually merge into one control cluster.

The dropdown should be compact and readable, with `All` and the three month options presented clearly.

---

## Error Handling

If the production database cannot be reached or a query fails:
- the page must not crash into a raw Whoops screen
- the dashboard should render a clear error message in the page
- the failure should continue to be logged server-side for diagnosis

The error fallback should preserve the selected period and the four-option dropdown so the user can retry without losing context.

---

## Testing

Implement verification for:
- month helper behavior for current month and previous-month derivation
- dashboard page rendering when the selected period is passed through the query string
- dropdown contents containing `All` plus the three month choices
- header layout keeping the add-transaction action separate from the dropdown
- database failure fallback rendering a readable dashboard error instead of a crash

The test set should be small and focused on the behaviors above. The intent is to prove the dashboard is driven by live data and the period filter rules are correct, not to exhaustively test CSS polish.
