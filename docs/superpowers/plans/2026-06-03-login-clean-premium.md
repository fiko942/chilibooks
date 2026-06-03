# Login Clean Premium Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the distracting metric grid from the login page and restyle the screen into a clean premium mobile-first layout.

**Architecture:** Keep the login page simple: a compact brand header, short hero copy, one login card, and a small FAQ link. All visual changes stay in the existing auth view and shared stylesheet so the login and FAQ pages continue to use the same mobile frame and typography system.

**Tech Stack:** CodeIgniter 4 views, shared CSS in `public/assets/chilibooks.css`.

---

### Task 1: Remove the metric strip from login

**Files:**
- Modify: `app/Views/auth/login.php`

- [ ] **Step 1: Remove the failing UI block**

```php
    <section class="metric-strip">
        <div><strong>3.74 bln</strong><span>estimasi dev</span></div>
        <div><strong>1.2 KLOC</strong><span>cakupan MVP</span></div>
        <div><strong>Rp2.5 jt</strong><span>batas anggaran</span></div>
    </section>
```

- [ ] **Step 2: Keep only brand, hero, login card, and FAQ link**

```php
<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<main class="auth-shell">
    <div class="brand"><span>CB</span><div><strong>ChiliBooks</strong><small>Pembukuan internal Chili Oil Gen Z</small></div></div>
    <section class="auth-copy">
        <p class="eyebrow" style="color:#fff">Owner access</p>
        <h1>Masuk untuk memantau transaksi, stok, dan keuntungan usaha.</h1>
        <p>Sistem hanya digunakan pihak internal Chili Oil Gen Z sesuai batasan proyek.</p>
    </section>
    <section class="login-card">
        <?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif ?>
        <p class="eyebrow">Selamat datang</p>
        <h2>Login ChiliBooks</h2>
        <form method="post" action="<?= site_url('login') ?>">
            <?= csrf_field() ?>
            <label>Email owner</label>
            <input name="email" type="email" value="<?= old('email', 'owner@chilioilgenz.id') ?>" required>
            <label>Password</label>
            <input name="password" type="password" required>
            <button class="primary full">Masuk Dashboard</button>
            <p class="helper">Default: owner@chilioilgenz.id / officer123</p>
        </form>
        <p class="faq-note">Butuh bantuan? <a class="faq-link" href="<?= site_url('faq') ?>">Lihat FAQ</a></p>
    </section>
</main>
<?= $this->endSection() ?>
```

### Task 2: Rebalance premium visual hierarchy

**Files:**
- Modify: `public/assets/chilibooks.css`

- [ ] **Step 1: Remove the metric strip rules**

```css
.metric-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:34px}.metric-strip div{padding:12px 10px;border:1px solid rgba(255,255,255,.22);border-radius:8px;background:rgba(255,255,255,.11)}.metric-strip strong{display:block;font-size:18px}.metric-strip span{display:block;margin-top:6px;color:rgba(255,255,255,.68);font-weight:700;font-size:12px}
```

- [ ] **Step 2: Strengthen the premium mobile layout**

```css
.auth-shell{min-height:844px;padding:22px 16px 28px;color:#fff}.auth-copy{margin-top:44px;max-width:328px}.auth-copy h1{font-size:34px;line-height:1.08;margin:4px 0 10px;letter-spacing:-.03em}.auth-copy p{color:rgba(255,255,255,.78);max-width:300px}.login-card{background:#fff;color:var(--ink);border-radius:18px;margin-top:24px;padding:22px;box-shadow:0 20px 54px rgba(40,21,14,.16)}.login-card h2{margin:0 0 8px;font-size:20px;line-height:1.15;letter-spacing:-.02em}.helper{color:var(--muted);font-size:12px;line-height:1.5}.faq-note{margin:12px 0 0;color:var(--muted);font-size:12px;line-height:1.4;text-align:center}.faq-note .faq-link{display:inline;font-weight:900;color:var(--chili);text-decoration:none;border-bottom:1px solid rgba(216,71,39,.25);padding-bottom:1px;background:none;border-radius:0;min-height:0;width:auto;margin:0}
```

### Task 3: Verify the rendered auth screen

**Files:**
- Test: `php -l app/Views/auth/login.php`
- Test: `php -l public/assets/chilibooks.css` (via browser/manual check; CSS syntax reviewed by diff)

- [ ] **Step 1: Run syntax check on the PHP view**

```bash
php -l app/Views/auth/login.php
```

- [ ] **Step 2: Confirm no login-grid regressions**

```bash
rg -n "metric-strip|faq-note|login-card|auth-copy" app/Views/auth/login.php public/assets/chilibooks.css
```

- [ ] **Step 3: Open the login page in the mobile frame and confirm the page reads as clean premium**

Expected: the grid is gone, the hero is simplified, and the FAQ remains a secondary text link.
