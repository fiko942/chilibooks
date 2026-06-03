<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<main class="auth-shell faq-shell">
    <section class="login-card faq-card">
        <p class="eyebrow">FAQ</p>
        <h1>Pertanyaan yang sering ditanya</h1>
        <p class="helper">Baca jawaban singkat sebelum masuk ke ChiliBooks.</p>

        <details class="faq-item">
            <summary>Apa itu ChiliBooks?</summary>
            <p>ChiliBooks adalah sistem pembukuan internal untuk memantau transaksi, stok, dan ringkasan usaha Chili Oil Gen Z.</p>
        </details>

        <details class="faq-item">
            <summary>Siapa yang bisa login?</summary>
            <p>Hanya owner atau pihak internal yang memiliki akses akun resmi.</p>
        </details>

        <details class="faq-item">
            <summary>Bagaimana kalau lupa password?</summary>
            <p>Hubungi pengelola sistem internal untuk reset akses.</p>
        </details>

        <a class="secondary full faq-back-link" href="<?= site_url('login') ?>">Kembali ke Login</a>
    </section>
</main>
<?= $this->endSection() ?>
