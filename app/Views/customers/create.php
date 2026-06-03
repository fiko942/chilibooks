<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Pelanggan',
    'heading' => 'Tambah pelanggan baru',
    'copy' => 'Masukkan nama, lokasi, dan nomor WhatsApp yang valid.',
    'actions' => '<a class="btn ghost" href="'.site_url('customers').'">Lihat Pelanggan</a>',
]) ?>

<section class="panel">
    <form method="post" action="<?= site_url('customers') ?>" class="customer-form">
        <?= csrf_field() ?>
        <label for="name">Nama</label>
        <input id="name" name="name" value="<?= esc(old('name')) ?>" required minlength="3" placeholder="Contoh: Toko Jaya">

        <label for="location">Lokasi</label>
        <input id="location" name="location" value="<?= esc(old('location')) ?>" placeholder="Contoh: Malang">

        <label for="phone">Kontak WhatsApp</label>
        <input id="phone" name="phone" value="<?= esc(old('phone')) ?>" placeholder="+6281234567890" inputmode="tel">

        <div class="toolbar customer-form-actions">
            <button class="btn primary" type="submit" name="submit_mode" value="save">Tambah</button>
            <button class="btn ghost" type="submit" name="submit_mode" value="keep_adding">Tambah dan input lagi</button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
