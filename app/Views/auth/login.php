<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<main class="auth-shell">
    <div class="brand"><span>CB</span><div><strong>ChiliBooks</strong><small>Pembukuan internal Chili Oil Gen Z</small></div></div>
    <section class="auth-copy">
        <p class="eyebrow" style="color:#fff">Owner access</p>
        <h1>Masuk untuk memantau transaksi, stok, dan keuntungan usaha.</h1>
        <p>Sistem hanya digunakan pihak internal Chili Oil Gen Z sesuai batasan proyek.</p>
    </section>
    <section class="metric-strip">
        <div><strong>3.74 bln</strong><span>estimasi dev</span></div>
        <div><strong>1.2 KLOC</strong><span>cakupan MVP</span></div>
        <div><strong>Rp2.5 jt</strong><span>batas anggaran</span></div>
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
        <a class="faq-link" href="<?= site_url('faq') ?>">FAQ</a>
    </section>
</main>
<?= $this->endSection() ?>
