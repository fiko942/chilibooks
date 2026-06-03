# Owner Transparency Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make dashboard and reports explain the business numbers in plain language, including omzet vs cash-in by payment method, so a non-expert owner can trust the figures.

**Architecture:** Keep the existing transaction model and compute everything from live transactions. Add a reusable payment-breakdown query to the dashboard and reports, surface a short “how numbers are calculated” block in both views, and make the monthly profit label stay on one line so the chart header stays readable on mobile.

**Tech Stack:** CodeIgniter 4, PHP, MySQL, existing mobile CSS, server-rendered views.

---

### Task 1: Lock the business explanations in tests

**Files:**
- Modify: `tests/feature/DashboardTest.php`
- Modify: `tests/feature/ReportTest.php`

- [ ] **Step 1: Write the failing tests**

```php
public function testDashboardExplainsCashInAndFormulas(): void
{
    helper('chili');

    $result = $this->withSession(['user_id' => 1])->get('dashboard?month=all');

    $result->assertOK();
    $result->assertSee('Cara hitung angka');
    $result->assertSee('Cash-in');
    $result->assertSee('QRIS');
    $result->assertSee('Transfer');
}

public function testReportExplainsCashInAndFormulas(): void
{
    $result = $this->withSession(['user_id' => 1])->get('reports?start=2026-06-01&end=2026-06-30');

    $result->assertOK();
    $result->assertSee('Cara membaca angka');
    $result->assertSee('Cash-in');
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `./vendor/bin/phpunit --filter 'DashboardTest|ReportTest' --testdox`
Expected: FAIL because the new explanation blocks and payment breakdown are not rendered yet.

### Task 2: Add payment breakdown queries and formulas

**Files:**
- Modify: `app/Controllers/DashboardController.php`
- Modify: `app/Controllers/ReportController.php`

- [ ] **Step 1: Add a completed-transaction payment breakdown query**

```php
$paymentBreakdown = $this->paymentBreakdown($db, $start ?? null, $end ?? null);
```

```php
private function paymentBreakdown($db, ?string $start = null, ?string $end = null): array
{
    return $this->applyDateRange(
        $db->table('transactions')
            ->select('payment_method, COALESCE(SUM(total),0) total_amount')
            ->where('status', 'completed')
            ->groupBy('payment_method'),
        $start,
        $end
    )->get()->getResultArray();
}
```

- [ ] **Step 2: Add a simple formula block to the view payload**

```php
'businessNotes' => [
    'omzet' => 'Omzet = total transaksi completed pada periode terpilih.',
    'profit' => 'Profit = total jual - modal pada transaksi completed.',
    'cashIn' => 'Cash-in = uang masuk per metode bayar dari transaksi completed.',
],
```

- [ ] **Step 3: Run the focused tests again**

Run: `./vendor/bin/phpunit --filter 'DashboardTest|ReportTest' --testdox`
Expected: PASS once the views render the explanation blocks and breakdown numbers.

### Task 3: Rebuild dashboard and report views

**Files:**
- Modify: `app/Views/dashboard/index.php`
- Modify: `app/Views/reports/index.php`
- Modify: `app/Views/reports/export_excel.php`
- Modify: `app/Views/reports/pdf.php`
- Modify: `public/assets/chilibooks.css`

- [ ] **Step 1: Add an explanation panel on dashboard and report**

```php
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Cara hitung angka</h2>
            <p>Versi singkat untuk owner: semua angka dibaca langsung dari transaksi real.</p>
        </div>
    </div>
    <div class="formula-list">
        <div><span>Omzet</span><b>total transaksi completed</b></div>
        <div><span>Profit</span><b>harga jual - harga modal</b></div>
        <div><span>Cash-in</span><b>total masuk per metode bayar</b></div>
    </div>
</section>
```

- [ ] **Step 2: Add cash-in summary cards**

```php
<?php foreach ($paymentBreakdown as $row): ?>
    <article class="row-card cashin-card">
        <h3><?= esc(transaction_payment_method_label($row['payment_method'])) ?></h3>
        <div class="meta"><span>Total masuk</span><b><?= rupiah($row['total_amount']) ?></b></div>
    </article>
<?php endforeach ?>
```

- [ ] **Step 3: Make the month label stay on one line**

```css
.inline-period,
.inline-period-pill,
.report-range-trigger span,
.pill {
  white-space: nowrap;
}
```

- [ ] **Step 4: Reflect the same business explanation in export files**

```php
<tr><th colspan="3">Cara membaca angka</th></tr>
<tr><td colspan="3">Omzet = total transaksi completed. Profit = total jual - modal. Cash-in = uang masuk per metode bayar.</td></tr>
```

- [ ] **Step 5: Run the UI tests again**

Run: `./vendor/bin/phpunit --filter 'DashboardTest|ReportTest' --testdox`
Expected: PASS.

### Task 4: Verify mobile readability and exports

**Files:**
- No new files

- [ ] **Step 1: Run the broader report/dashboard test slice**

Run: `./vendor/bin/phpunit --filter 'DashboardTest|ReportTest|MonthHelperTest' --testdox`
Expected: PASS.

- [ ] **Step 2: Open the dashboard and report pages in the browser**

Run: `php spark serve`
Expected: the new explanation blocks and cash-in summary are readable on mobile, and the profit-month label stays on one line.

