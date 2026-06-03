# Customer Management Mobile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move customer creation into its own page, add mobile-friendly search and pagination to the customer list, and add a WhatsApp contact action for valid Indonesian numbers.

**Architecture:** Keep customer data in the existing `customers` table. Split creation into a dedicated controller route and form view, move the list page to server-side search/pagination with compact cards, and reuse small helper functions for phone normalization/validation so the WhatsApp link is consistent across pages. Add only the transaction-form shortcut needed to reach the new customer page.

**Tech Stack:** CodeIgniter 4, PHP, MySQL, server-side pagination, existing app CSS.

---

### Task 1: Add customer flow coverage

**Files:**
- Modify: `tests/feature/CustomerManagementTest.php`

- [ ] **Step 1: Write the failing tests**

```php
public function testCustomerIndexShowsSearchAndWhatsAppAction(): void
{
    $result = $this->withSession(['user_id' => 1])->get('customers?q=Jaya');

    $result->assertOK();
    $result->assertSee('Cari pelanggan');
    $result->assertSee('Kontak melalui WhatsApp');
}

public function testCustomerCreateSupportsKeepAdding(): void
{
    $result = $this->withSession(['user_id' => 1])->post('customers', [
        'name' => 'Toko Jaya',
        'location' => 'Bandung',
        'phone' => '+6281234567890',
        'submit_mode' => 'keep_adding',
    ]);

    $result->assertRedirectTo('customers/new');
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `./vendor/bin/phpunit --filter CustomerManagementTest --testdox`
Expected: FAIL because the dedicated create page, pagination, and WhatsApp action are not implemented yet.

### Task 2: Split create page and add list search/pagination

**Files:**
- Modify: `app/Controllers/CustomerController.php:15-70`
- Modify: `app/Config/Routes.php:24-28`
- Create: `app/Views/customers/create.php`
- Modify: `app/Views/customers/index.php`
- Modify: `app/Views/transactions/form.php`

- [ ] **Step 1: Implement the controller and routing changes**

```php
public function index()
{
    $query = trim((string) $this->request->getGet('q'));
    $page = max(1, (int) ($this->request->getGet('page_customers') ?: 1));
    $perPage = 8;

    $builder = db_connect()->table('customers c')
        ->select('c.*, COUNT(t.id) transaction_count, COALESCE(SUM(CASE WHEN t.status = "completed" THEN t.total ELSE 0 END),0) total_spent')
        ->join('transactions t', 't.customer_id = c.id', 'left')
        ->where('c.deleted_at', null)
        ->groupBy('c.id');

    if ($query !== '') {
        $builder->groupStart()
            ->like('c.name', $query)
            ->orLike('c.location', $query)
            ->orLike('c.phone', $query)
            ->groupEnd();
    }

    $total = (int) (clone $builder)->countAllResults();
    $customers = $builder->orderBy('c.name')->get($perPage, ($page - 1) * $perPage)->getResultArray();
    $pagerHtml = $total > $perPage
        ? service('pager')->only(['q'])->makeLinks($page, $perPage, $total, 'default_full', 0, 'customers')
        : '';

    return view('customers/index', compact('customers', 'pagerHtml', 'query'));
}
```

- [ ] **Step 2: Add the dedicated create page and keep-adding submit mode**

```php
public function create()
{
    $payload = $this->payload();
    $this->customers->insert($payload);
    $mode = $this->request->getPost('submit_mode');

    if ($mode === 'keep_adding') {
        return redirect()->to('/customers/new')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    return redirect()->to('/customers')->with('success', 'Pelanggan berhasil ditambahkan.');
}
```

- [ ] **Step 3: Add transaction-form shortcut**

```php
<a class="btn ghost" href="<?= site_url('customers/new') ?>">Tambah pelanggan</a>
```

- [ ] **Step 4: Add search/pagination and compact cards**

```php
<?= view('partials/page_head', [
    'controls' => '<form method="get" class="list-search"><input type="search" name="q" value="'.esc($query ?? '').'" placeholder="Cari pelanggan"><button class="ghost" type="submit">Cari</button></form>',
    'actions' => '<a class="btn primary" href="'.site_url('customers/new').'">Tambah Pelanggan</a>',
]) ?>
```

```php
<a class="btn ghost full" href="https://wa.me/<?= esc($waNumber) ?>" target="_blank" rel="noopener">Kontak melalui WhatsApp</a>
```

- [ ] **Step 5: Add CSS for compact list and split form buttons**

```css
.customer-actions { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.customer-search { display:flex; gap:8px; }
.customer-card { padding:14px; }
.customer-pagination { margin-top:14px; }
```

- [ ] **Step 6: Run the tests again**

Run: `./vendor/bin/phpunit --filter CustomerManagementTest --testdox`
Expected: PASS once the controller and views are wired together.

### Task 3: Verify end-to-end customer flow

**Files:**
- No new files

- [ ] **Step 1: Run the customer test subset**

Run: `./vendor/bin/phpunit --filter 'CustomerManagementTest|TransactionEntryTest' --testdox`
Expected: PASS.

- [ ] **Step 2: Open the transaction and customer pages**

Run: `php spark serve`
Expected: `/transactions/new` shows `Tambah pelanggan`, `/customers` shows search, pagination, and WhatsApp actions, and `/customers/new` supports both submit modes.

