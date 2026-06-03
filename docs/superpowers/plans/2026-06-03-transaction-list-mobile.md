# Transaction List Mobile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the transaction list into a compact mobile-first page with server-side search, pagination, and an inert `Kirim Invoice` button.

**Architecture:** Keep the database schema unchanged. Move filtering and pagination into `TransactionController::index()`, keep rendering logic in `app/Views/transactions/index.php`, and add only the CSS needed to make cards and toolbar actions compact on mobile. Preserve the existing `Detail` action and add `Kirim Invoice` as a no-op UI affordance.

**Tech Stack:** CodeIgniter 4, PHP, MySQL, server-side pagination, existing app CSS.

---

### Task 1: Add list coverage first

**Files:**
- Modify: `tests/feature/TransactionListTest.php` or `tests/feature/TransactionEntryTest.php`

- [ ] **Step 1: Write the failing test**

```php
public function testTransactionIndexShowsSearchAndInvoiceAction(): void
{
    $result = $this->withSession(['user_id' => 1])->get('transactions?q=INV-');

    $result->assertOK();
    $result->assertSee('Cari transaksi');
    $result->assertSee('Kirim Invoice');
    $result->assertSee('Detail');
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `./vendor/bin/phpunit --filter TransactionIndexShowsSearchAndInvoiceAction --testdox`
Expected: FAIL because the view does not yet render the search field or invoice action.

### Task 2: Implement controller query + pagination

**Files:**
- Modify: `app/Controllers/TransactionController.php:27-36`

- [ ] **Step 1: Write the minimal implementation**

```php
public function index()
{
    $query = trim((string) $this->request->getGet('q'));
    $builder = db_connect()->table('transactions t')
        ->select('t.*, c.name customer_name')
        ->join('customers c', 'c.id = t.customer_id');

    if ($query !== '') {
        $builder->groupStart()
            ->like('t.invoice', $query)
            ->orLike('c.name', $query)
            ->groupEnd();
    }

    $rows = $builder
        ->orderBy('t.transaction_date', 'DESC')
        ->orderBy('t.id', 'DESC')
        ->paginate(8, 'transactions');

    return view('transactions/index', [
        'title' => 'Riwayat Transaksi',
        'active' => 'transactions',
        'transactions' => $rows,
        'pager' => db_connect()->pager,
        'query' => $query,
    ]);
}
```

- [ ] **Step 2: Run the focused test again**

Run: `./vendor/bin/phpunit --filter TransactionIndexShowsSearchAndInvoiceAction --testdox`
Expected: PASS once the view renders the new controls.

### Task 3: Rework the transaction list view

**Files:**
- Modify: `app/Views/transactions/index.php:1-20`
- Modify: `public/assets/chilibooks.css`

- [ ] **Step 1: Replace the simple stack with search + compact cards**

```php
<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Histori Transaksi',
    'heading' => 'Daftar penjualan tersimpan',
    'copy' => 'Cari invoice atau nama pelanggan tanpa mengorbankan tampilan mobile.',
    'controls' => '<form method="get" class="list-search"><input type="search" name="q" value="'.esc($query ?? '').'" placeholder="Cari transaksi"><button class="ghost" type="submit">Cari</button></form>',
    'actions' => '<a class="btn primary" href="'.site_url('transactions/new').'">Input Transaksi</a>',
]) ?>
<section class="list compact-list">
    <?php if (empty($transactions)): ?>
        <div class="panel empty-state">Tidak ada transaksi yang cocok.</div>
    <?php endif ?>
    <?php foreach ($transactions as $row): ?>
        <article class="row-card transaction-card">
            <div class="transaction-card-head">
                <div>
                    <h3><?= esc($row['invoice']) ?></h3>
                    <p><?= esc($row['customer_name']) ?></p>
                </div>
                <span class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></span>
            </div>
            <div class="transaction-card-meta">
                <div><span>Tanggal</span><b><?= esc($row['transaction_date']) ?></b></div>
                <div><span>Total</span><b><?= rupiah($row['total']) ?></b></div>
                <div><span>Profit</span><b><?= rupiah($row['profit']) ?></b></div>
            </div>
            <div class="toolbar transaction-actions">
                <a class="btn ghost full" href="<?= site_url('transactions/'.$row['id']) ?>">Detail</a>
                <a class="btn ghost full send-invoice" href="#" aria-disabled="true" tabindex="-1">Kirim Invoice</a>
            </div>
        </article>
    <?php endforeach ?>
</section>
<?= $pager?->links('transactions') ?>
<?= $this->endSection() ?>
```

- [ ] **Step 2: Add compact mobile styles**

```css
.list-search { display:flex; gap:8px; width:100%; }
.list-search input { flex:1; }
.compact-list { display:grid; gap:10px; }
.transaction-card { padding:14px; }
.transaction-card-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
.transaction-card-head h3 { margin:0 0 4px; font-size:16px; }
.transaction-card-head p { margin:0; color:var(--muted); font-size:12px; }
.transaction-card-meta { display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-top:12px; }
.transaction-card-meta span { display:block; font-size:11px; color:var(--muted); }
.transaction-card-meta b { display:block; margin-top:2px; font-size:13px; }
.transaction-actions { margin-top:12px; }
.send-invoice { pointer-events:auto; }
.pager { margin-top:14px; }
```

- [ ] **Step 3: Run the UI test again**

Run: `./vendor/bin/phpunit --filter TransactionIndexShowsSearchAndInvoiceAction --testdox`
Expected: PASS.

### Task 4: Verify full behavior

**Files:**
- No new files

- [ ] **Step 1: Run targeted test suite**

Run: `./vendor/bin/phpunit --filter 'TransactionListTest|TransactionEntryTest' --testdox`
Expected: PASS.

- [ ] **Step 2: Manually open the list page**

Run: `php spark serve`
Expected: `/transactions` shows compact cards, search input, pagination, and the inert `Kirim Invoice` button.

