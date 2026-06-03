# Dashboard Real Live Data Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the dashboard read live production data only, add a fixed period dropdown with `All` plus the current month and two previous months, and keep the add-transaction action right-aligned on the same header row.

**Architecture:** Keep the dashboard logic in `DashboardController`, the reusable date helpers in `app/Helpers/chili_helper.php`, and the presentation in `app/Views/dashboard/index.php` plus shared styles in `public/assets/chilibooks.css`. The controller owns period parsing and live SQL aggregation; the view only renders the returned arrays and strings; the CSS keeps the mobile header aligned without coupling to business logic.

**Tech Stack:** CodeIgniter 4 controller/view stack, MySQL production database, shared helper functions, CSS for mobile layout.

---

### Task 1: Add an explicit dashboard period model

**Files:**
- Modify: `app/Helpers/chili_helper.php`
- Modify: `app/Controllers/DashboardController.php`

- [ ] **Step 1: Write the failing test for period parsing**

```php
public function test_dashboard_period_helper_supports_all_and_months()
{
    $this->assertSame('all', dashboard_period_key('all'));
    $this->assertSame(date('Y-m'), dashboard_period_key(null));
    $this->assertSame(previous_month_key(date('Y-m')), previous_month_key(date('Y-m')));
}
```

- [ ] **Step 2: Run the test to confirm the helper does not exist yet**

```bash
./vendor/bin/phpunit --filter DashboardPeriodTest -v
```

Expected: failure because `dashboard_period_key()` is not yet implemented.

- [ ] **Step 3: Implement the helper and controller period branch**

```php
function dashboard_period_key(?string $period): string
{
    if ($period === 'all') {
        return 'all';
    }

    return month_key($period);
}
```

```php
$selectedPeriod = dashboard_period_key($this->request->getGet('month'));
$isAllPeriod = $selectedPeriod === 'all';
$currentMonth = date('Y-m');
$previousMonth = previous_month_key($currentMonth);
$twoMonthsAgo = previous_month_key($previousMonth);
```

- [ ] **Step 4: Run the test again**

```bash
./vendor/bin/phpunit --filter DashboardPeriodTest -v
```

Expected: pass.

### Task 2: Switch dashboard queries to live selected-period data

**Files:**
- Modify: `app/Controllers/DashboardController.php`
- Modify: `app/Views/dashboard/index.php`
- Modify: `app/Views/partials/page_head.php`

- [ ] **Step 1: Write the failing dashboard query test**

```php
public function test_dashboard_option_list_includes_all_and_three_months()
{
    $result = $this->get('/dashboard');
    $result->assertOK();
    $result->assertSee('All');
    $result->assertSee(month_label(date('Y-m')));
}
```

- [ ] **Step 2: Run the test to confirm the fixed option list is not yet present**

```bash
./vendor/bin/phpunit --filter DashboardTest -v
```

Expected: fail until the controller returns `All`, current month, previous month, and two months ago.

- [ ] **Step 3: Implement live aggregation for both month and all-time modes**

```php
if ($selectedPeriod === 'all') {
    $summary = $this->summaryForAllTime($db);
    $trend = $this->salesTrendForAllTime($db);
    $loyalCustomers = $this->loyalCustomersForAllTime($db);
    $latestTransactions = $this->latestTransactionsForAllTime($db);
} else {
    [$start, $end] = month_period($selectedPeriod);
    $summary = $this->summaryForPeriod($db, $start, $end);
    $trend = $this->salesTrend($db, $start, $end);
    $loyalCustomers = $this->loyalCustomers($db, $start, $end);
    $latestTransactions = $this->latestTransactions($db, $start, $end);
}
```

- [ ] **Step 4: Render the dropdown and header actions in one row**

```php
<?= view('partials/page_head', [
    'eyebrow' => 'Dashboard',
    'heading' => 'Ringkasan bisnis ' . $selectedPeriodLabel,
    'copy' => 'Data dashboard dibaca langsung dari transaksi real. Pilih periode untuk melihat bulan berjalan atau semua waktu.',
    'controls' => $monthSelector,
    'actions' => '<a class="btn primary" href="' . site_url('transactions/new') . '">Tambah Transaksi</a>',
]) ?>
```

- [ ] **Step 5: Run the dashboard tests and confirm the new option list and header render**

```bash
./vendor/bin/phpunit --filter 'DashboardTest|MonthHelperTest' --testdox
```

Expected: pass after the live query branches and four-option dropdown are in place.

### Task 3: Rebalance the dashboard mobile header and empty states

**Files:**
- Modify: `public/assets/chilibooks.css`
- Modify: `app/Views/dashboard/index.php`

- [ ] **Step 1: Tighten the header spacing so the action sits on the right**

```css
.page-head {
  gap: 16px;
}

.page-head-actions {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.page-head-controls {
  flex: 1 1 auto;
  min-width: 0;
}

.toolbar {
  flex: 0 0 auto;
}

.month-picker {
  width: 100%;
  max-width: 176px;
}
```

- [ ] **Step 2: Keep the dashboard empty states readable when the live DB has no completed rows**

```php
<?php if (empty($trend)): ?>
    <div class="empty-state">Belum ada transaksi completed untuk periode ini.</div>
<?php endif ?>
```

- [ ] **Step 3: Check the page in the mobile frame**

```bash
php spark serve
```

Expected: the header reads as one row, the dropdown is compact, and `Tambah Transaksi` sits at the far right rather than next to the month selector.

