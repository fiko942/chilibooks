<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'ChiliBooks') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/chilibooks.css') ?>">
</head>
<body>
<div class="frame">
    <header class="topbar">
        <div class="brand">
            <span>CB</span>
            <div><strong>ChiliBooks</strong><small>Chili Oil Gen Z</small></div>
        </div>
        <form method="post" action="<?= site_url('logout') ?>"><?= csrf_field() ?><button class="link-btn">Keluar</button></form>
        <nav>
            <a class="<?= active_nav($active ?? '', 'dashboard') ?>" href="<?= site_url('dashboard') ?>">Dashboard</a>
            <a class="<?= active_nav($active ?? '', 'products') ?>" href="<?= site_url('products') ?>">Produk</a>
            <a class="<?= active_nav($active ?? '', 'customers') ?>" href="<?= site_url('customers') ?>">Pelanggan</a>
            <a class="<?= active_nav($active ?? '', 'transactions') ?>" href="<?= site_url('transactions') ?>">Transaksi</a>
            <a class="<?= active_nav($active ?? '', 'reports') ?>" href="<?= site_url('reports') ?>">Laporan</a>
        </nav>
    </header>
    <main class="content">
        <?php if (session('success')): ?><div class="alert success"><?= esc(session('success')) ?></div><?php endif ?>
        <?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif ?>
        <?= $this->renderSection('content') ?>
    </main>
</div>
</body>
</html>
