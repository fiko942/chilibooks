<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Pelanggan',
    'heading' => 'Manajemen data pelanggan',
    'copy' => 'Cari pelanggan berdasarkan nama, lokasi, atau nomor WhatsApp.',
    'controls' => '<form method="get" action="'.site_url('customers').'" class="list-search"><input type="search" name="q" value="'.esc($query ?? '').'" placeholder="Cari pelanggan"><button class="ghost" type="submit">Cari</button></form>',
    'actions' => '<a class="btn primary" href="'.site_url('customers/new').'">Tambah Pelanggan</a>',
]) ?>

<?php if (! empty($listError)): ?>
    <div class="alert error"><?= esc($listError) ?></div>
<?php endif ?>

<section class="list compact-list">
    <?php if (empty($customers)): ?>
        <div class="empty-state">Tidak ada pelanggan yang cocok.</div>
    <?php endif ?>

    <?php foreach ($customers as $customer): ?>
        <?php
            $whatsappUrl = customer_whatsapp_url($customer['phone'] ?? null, $customer['name'] ?? null, $customer['location'] ?? null);
            $canContact = customer_can_contact_whatsapp((string) ($customer['name'] ?? ''), $customer['phone'] ?? null);
        ?>
        <article class="row-card customer-card">
            <h3><?= esc($customer['name']) ?></h3>
            <div class="meta"><span>Lokasi</span><b><?= esc($customer['location'] ?: '-') ?></b></div>
            <div class="meta"><span>Kontak</span><b><?= esc($customer['phone'] ?: '-') ?></b></div>
            <div class="meta"><span>Transaksi</span><b><?= esc($customer['transaction_count']) ?></b></div>
            <div class="meta"><span>Total</span><b><?= rupiah($customer['total_spent']) ?></b></div>
            <div class="toolbar customer-actions">
                <a class="btn ghost full" href="<?= site_url('customers/'.$customer['id']) ?>">Lihat Detail</a>
                <?php if ($canContact && $whatsappUrl !== null): ?>
                    <a class="btn ghost full customer-whatsapp" href="<?= esc($whatsappUrl) ?>" target="_blank" rel="noopener">Kontak melalui WhatsApp</a>
                <?php else: ?>
                    <span class="btn ghost full disabled" aria-disabled="true">Kontak melalui WhatsApp</span>
                <?php endif ?>
            </div>
        </article>
    <?php endforeach ?>
</section>

<?php if (! empty($pagerHtml)): ?>
    <div class="pager-wrap customer-pagination">
        <?= $pagerHtml ?>
    </div>
<?php endif ?>

<?= $this->endSection() ?>
