<?php

$transactionDateRaw = (string) old('transaction_date', date('Y-m-d'));
$transactionDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDateRaw) === 1 ? $transactionDateRaw : date('Y-m-d');
$customerId = (string) old('customer_id', '');
$paymentMethod = (string) old('payment_method', 'cash');
$deliveryType = (string) old('delivery_type', 'diantar');
$status = (string) old('status', 'completed');
$discountValue = old('discount', 0);
$extraFeeValue = old('extra_fee', 0);

$productIds = old('product_id');
$qtys = old('qty');

if (! is_array($productIds)) {
    $productIds = [];
}

if (! is_array($qtys)) {
    $qtys = [];
}

$productPriceMap = [];
foreach ($products as $product) {
    $productPriceMap[(string) $product['id']] = (float) $product['selling_price'];
}

$rowCount = max(count($productIds), count($qtys), 1);
$rows = [];
for ($index = 0; $index < $rowCount; $index++) {
    $selectedProductId = (string) ($productIds[$index] ?? '');
    $selectedQty = array_key_exists($index, $qtys) ? (int) $qtys[$index] : 1;
    $rows[] = [
        'product_id' => $selectedProductId,
        'qty' => $selectedQty,
    ];
}

$customerOptions = '<option value="">Pilih pelanggan</option>';
foreach ($customers as $customer) {
    $labelParts = [$customer['name']];
    if (! empty($customer['location'])) {
        $labelParts[] = $customer['location'];
    }
    if (! empty($customer['phone'])) {
        $labelParts[] = $customer['phone'];
    }

    $customerOptions .= '<option value="' . esc((string) $customer['id'], 'attr') . '"' . ((string) $customer['id'] === $customerId ? ' selected' : '') . '>' . esc(implode(' • ', $labelParts)) . '</option>';
}

$productOptions = '<option value="">Pilih produk</option>';
foreach ($products as $product) {
    $productOptions .= '<option value="' . esc((string) $product['id'], 'attr') . '" data-price="' . esc((string) $product['selling_price'], 'attr') . '">' . esc($product['name']) . ' • ' . rupiah($product['selling_price']) . '</option>';
}

$dateLabel = new \DateTimeImmutable($transactionDate);
$displayedDate = $dateLabel->format('d') . ' ' . month_label($dateLabel->format('Y-m')) . ' ' . $dateLabel->format('Y');
?>
<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Input Transaksi',
    'heading' => 'Pencatatan penjualan baru',
    'copy' => 'Satu transaksi bisa berisi banyak produk, tanggal dipilih dari kalender sendiri, dan bukti pembayaran hanya muncul saat dibutuhkan.',
]) ?>

<section class="panel transaction-shell">
    <?php if (! empty($formError)): ?>
        <div class="alert error"><?= esc($formError) ?></div>
    <?php endif ?>
    <form method="post" action="<?= site_url('transactions') ?>" enctype="multipart/form-data" class="transaction-form" data-transaction-form>
        <?= csrf_field() ?>
        <section class="txn-card">
            <div class="section-title">
                <h2>Customer & jadwal</h2>
                <p>Pilih pelanggan, tanggal transaksi, dan jenis pengantaran.</p>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="customer_id">Pelanggan</label>
                    <select id="customer_id" name="customer_id" class="field-select" required>
                        <?= $customerOptions ?>
                    </select>
                    <div class="field-note">
                        <a class="link-action" href="<?= site_url('customers/new') ?>">Tambah pelanggan</a>
                    </div>
                </div>

                <div class="field">
                    <label>Tanggal transaksi</label>
                    <div class="date-picker" data-date-picker>
                        <button type="button" class="date-trigger" data-date-trigger>
                            <span data-date-label><?= esc($displayedDate) ?></span>
                            <small>Klik untuk pilih tanggal</small>
                        </button>
                        <input type="hidden" name="transaction_date" value="<?= esc($transactionDate) ?>" data-date-value>
                        <div class="date-popover" hidden data-date-popover>
                            <div class="date-popover-head">
                                <button type="button" class="ghost small" data-date-prev>&lsaquo;</button>
                                <strong data-date-month></strong>
                                <button type="button" class="ghost small" data-date-next>&rsaquo;</button>
                            </div>
                            <div class="date-weekdays">
                                <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                            </div>
                            <div class="date-grid" data-date-grid></div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label for="delivery_type">Jenis pengantaran</label>
                    <select id="delivery_type" name="delivery_type" class="field-select">
                        <option value="diantar"<?= $deliveryType === 'diantar' ? ' selected' : '' ?>>Diantar</option>
                        <option value="diambil_di_tempat"<?= $deliveryType === 'diambil_di_tempat' ? ' selected' : '' ?>>Diambil di tempat</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="txn-card">
            <div class="section-title section-title-row">
                <div>
                    <h2>Produk</h2>
                    <p>Tambah baris sebanyak yang kamu butuhkan.</p>
                </div>
                <button type="button" class="btn ghost" data-add-product>Tambah Produk</button>
            </div>

            <div class="product-lines" data-product-lines>
                <?php foreach ($rows as $index => $row): ?>
                    <article class="product-line" data-product-line>
                        <div class="field">
                            <label>Produk</label>
                            <select name="product_id[]" class="field-select" data-product-select>
                                <?= $productOptions ?>
                            </select>
                        </div>
                        <div class="field field-qty">
                            <label>Qty</label>
                            <input name="qty[]" type="number" min="1" step="1" value="<?= esc((string) $row['qty']) ?>" data-qty-input inputmode="numeric">
                        </div>
                        <div class="line-total">
                            <span>Subtotal</span>
                            <strong data-line-total>Rp0</strong>
                        </div>
                        <button type="button" class="btn danger line-remove" data-remove-product>Hapus</button>
                    </article>
                <?php endforeach ?>
            </div>

            <template id="product-line-template">
                <article class="product-line" data-product-line>
                    <div class="field">
                        <label>Produk</label>
                        <select name="product_id[]" class="field-select" data-product-select>
                            <?= $productOptions ?>
                        </select>
                    </div>
                    <div class="field field-qty">
                        <label>Qty</label>
                        <input name="qty[]" type="number" min="1" step="1" value="1" data-qty-input inputmode="numeric">
                    </div>
                    <div class="line-total">
                        <span>Subtotal</span>
                        <strong data-line-total>Rp0</strong>
                    </div>
                    <button type="button" class="btn danger line-remove" data-remove-product>Hapus</button>
                </article>
            </template>
        </section>

        <section class="txn-card">
            <div class="section-title">
                <h2>Pembayaran</h2>
                <p>Upload bukti pembayaran hanya untuk QRIS atau transfer.</p>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="payment_method">Metode pembayaran</label>
                    <select id="payment_method" name="payment_method" class="field-select" data-payment-method>
                        <option value="cash"<?= $paymentMethod === 'cash' ? ' selected' : '' ?>>Cash</option>
                        <option value="qris"<?= $paymentMethod === 'qris' ? ' selected' : '' ?>>QRIS</option>
                        <option value="transfer"<?= $paymentMethod === 'transfer' ? ' selected' : '' ?>>Transfer</option>
                    </select>
                </div>

                <div class="field payment-proof" data-proof-field<?= in_array($paymentMethod, ['qris', 'transfer'], true) ? '' : ' hidden' ?>>
                    <label for="payment_proof">Bukti pembayaran</label>
                    <input id="payment_proof" name="payment_proof" type="file" accept="image/*" class="field-file" data-proof-input>
                    <p class="field-hint">Muncul saat metode QRIS atau transfer dipilih.</p>
                </div>
            </div>
        </section>

        <section class="txn-card">
            <div class="section-title">
                <h2>Status & catatan</h2>
                <p>Gunakan status pesanan dan catatan tambahan bila perlu.</p>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="status">Status pesanan</label>
                    <select id="status" name="status" class="field-select">
                        <option value="completed"<?= $status === 'completed' ? ' selected' : '' ?>>Selesai</option>
                        <option value="canceled"<?= $status === 'canceled' ? ' selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>

                <div class="field">
                    <label for="discount">Diskon manual</label>
                    <input id="discount" name="discount" type="number" value="<?= esc((string) $discountValue) ?>" min="0" step="1" inputmode="numeric" data-discount>
                </div>

                <div class="field">
                    <label for="extra_fee">Biaya tambahan</label>
                    <input id="extra_fee" name="extra_fee" type="number" value="<?= esc((string) $extraFeeValue) ?>" min="0" step="1" inputmode="numeric" data-extra-fee>
                </div>

                <div class="field field-full">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Info tambahan, alamat, atau arahan khusus"><?= esc((string) old('notes', '')) ?></textarea>
                </div>
            </div>
        </section>

        <section class="txn-card summary-card">
            <div class="section-title">
                <h2>Ringkasan</h2>
                <p>Total dihitung otomatis dari semua baris produk.</p>
            </div>
            <div class="summary-grid">
                <div class="amount"><span>Subtotal</span><b data-summary-subtotal>Rp0</b></div>
                <div class="amount"><span>Diskon</span><b data-summary-discount>- Rp0</b></div>
                <div class="amount"><span>Biaya tambahan</span><b data-summary-extra>Rp0</b></div>
                <div class="divider"></div>
                <div class="amount strong"><span>Total bayar</span><b data-summary-total>Rp0</b></div>
            </div>
        </section>

        <button class="primary full" type="submit">Simpan Transaksi</button>
    </form>
</section>

<script>
(function () {
    const productPrices = <?= json_encode($productPriceMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const paymentMethod = document.querySelector('[data-payment-method]');
    const proofField = document.querySelector('[data-proof-field]');
    const proofInput = document.querySelector('[data-proof-input]');
    const lineContainer = document.querySelector('[data-product-lines]');
    const template = document.getElementById('product-line-template');
    const addProductButton = document.querySelector('[data-add-product]');
    const discountInput = document.querySelector('[data-discount]');
    const extraFeeInput = document.querySelector('[data-extra-fee]');
    const statusSelect = document.getElementById('status');
    const summarySubtotal = document.querySelector('[data-summary-subtotal]');
    const summaryDiscount = document.querySelector('[data-summary-discount]');
    const summaryExtra = document.querySelector('[data-summary-extra]');
    const summaryTotal = document.querySelector('[data-summary-total]');
    const datePicker = document.querySelector('[data-date-picker]');

    const moneyFormatter = new Intl.NumberFormat('id-ID');
    const dayFormatter = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
    const weekdayFormatter = new Intl.DateTimeFormat('id-ID', { weekday: 'short' });

    function formatMoney(value) {
        return 'Rp' + moneyFormatter.format(Math.max(0, Math.round(value || 0)));
    }

    function parseNumber(input) {
        const parsed = parseFloat(input);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function getRows() {
        return Array.from(document.querySelectorAll('[data-product-line]'));
    }

    function updateRow(row) {
        const select = row.querySelector('[data-product-select]');
        const qtyInput = row.querySelector('[data-qty-input]');
        const subtotalNode = row.querySelector('[data-line-total]');
        const productId = select.value;
        const qty = Math.max(0, parseInt(qtyInput.value || '0', 10));
        const price = parseFloat(productPrices[productId] || 0);
        const subtotal = price * qty;

        subtotalNode.textContent = formatMoney(subtotal);
        row.dataset.subtotal = String(subtotal);

        updateSummary();
    }

    function updateSummary() {
        const subtotal = getRows().reduce((sum, row) => sum + parseFloat(row.dataset.subtotal || '0'), 0);
        const discount = parseNumber(discountInput.value);
        const extraFee = parseNumber(extraFeeInput.value);
        const total = statusSelect.value === 'canceled' ? 0 : Math.max(0, subtotal - discount + extraFee);

        summarySubtotal.textContent = formatMoney(subtotal);
        summaryDiscount.textContent = '- ' + formatMoney(discount);
        summaryExtra.textContent = formatMoney(extraFee);
        summaryTotal.textContent = formatMoney(total);
    }

    function updateProofField() {
        const needsProof = ['qris', 'transfer'].includes(paymentMethod.value);
        proofField.hidden = ! needsProof;
        proofInput.required = needsProof;
        if (! needsProof) {
            proofInput.value = '';
        }
    }

    function bindRow(row) {
        row.querySelector('[data-product-select]').addEventListener('change', () => updateRow(row));
        row.querySelector('[data-qty-input]').addEventListener('input', () => updateRow(row));
        row.querySelector('[data-remove-product]').addEventListener('click', () => {
            const rows = getRows();
            if (rows.length > 1) {
                row.remove();
            } else {
                row.querySelector('[data-product-select]').value = '';
                row.querySelector('[data-qty-input]').value = '1';
            }

            getRows().forEach(updateRow);
            refreshRemoveButtons();
        });
    }

    function refreshRemoveButtons() {
        const rows = getRows();
        rows.forEach((row) => {
            const removeButton = row.querySelector('[data-remove-product]');
            removeButton.hidden = rows.length === 1;
        });
    }

    function addRow(productId = '', qty = '1') {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('[data-product-line]');
        const select = row.querySelector('[data-product-select]');
        const qtyInput = row.querySelector('[data-qty-input]');

        select.value = productId;
        qtyInput.value = qty;
        bindRow(row);
        lineContainer.appendChild(fragment);
        updateRow(lineContainer.lastElementChild);
        refreshRemoveButtons();
    }

    addProductButton.addEventListener('click', () => addRow());
    paymentMethod.addEventListener('change', updateProofField);
    discountInput.addEventListener('input', updateSummary);
    extraFeeInput.addEventListener('input', updateSummary);
    statusSelect.addEventListener('change', updateSummary);

    getRows().forEach((row) => {
        bindRow(row);
        updateRow(row);
    });
    refreshRemoveButtons();
    updateProofField();
    updateSummary();

    const dateTrigger = datePicker.querySelector('[data-date-trigger]');
    const datePopover = datePicker.querySelector('[data-date-popover]');
    const dateInput = datePicker.querySelector('[data-date-value]');
    const dateLabel = datePicker.querySelector('[data-date-label]');
    const dateGrid = datePicker.querySelector('[data-date-grid]');
    const monthLabel = datePicker.querySelector('[data-date-month]');
    const prevButton = datePicker.querySelector('[data-date-prev]');
    const nextButton = datePicker.querySelector('[data-date-next]');

    const today = new Date();
    let currentView = parseDate(dateInput.value) || new Date(today.getFullYear(), today.getMonth(), today.getDate());

    function parseDate(value) {
        if (! value) {
            return null;
        }

        const parts = value.split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) {
            return null;
        }

        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatIso(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function setSelectedDate(date) {
        dateInput.value = formatIso(date);
        dateLabel.textContent = dayFormatter.format(date);
        currentView = new Date(date.getFullYear(), date.getMonth(), 1);
        renderCalendar(currentView);
        closeCalendar();
    }

    function openCalendar() {
        datePopover.hidden = false;
        renderCalendar(currentView);
    }

    function closeCalendar() {
        datePopover.hidden = true;
    }

    function renderCalendar(anchorDate) {
        const year = anchorDate.getFullYear();
        const month = anchorDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startPadding = firstDay.getDay();
        const daysInMonth = lastDay.getDate();

        monthLabel.textContent = monthFormatter.format(firstDay);
        dateGrid.innerHTML = '';

        for (let i = 0; i < startPadding; i += 1) {
            const filler = document.createElement('span');
            filler.className = 'calendar-filler';
            dateGrid.appendChild(filler);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const current = new Date(year, month, day);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'calendar-day';
            button.textContent = String(day);
            button.title = weekdayFormatter.format(current) + ', ' + dayFormatter.format(current);

            if (formatIso(current) === dateInput.value) {
                button.classList.add('active');
            }

            button.addEventListener('click', () => setSelectedDate(current));
            dateGrid.appendChild(button);
        }
    }

    dateTrigger.addEventListener('click', () => {
        if (datePopover.hidden) {
            openCalendar();
        } else {
            closeCalendar();
        }
    });

    prevButton.addEventListener('click', () => {
        currentView = new Date(currentView.getFullYear(), currentView.getMonth() - 1, 1);
        renderCalendar(currentView);
    });

    nextButton.addEventListener('click', () => {
        currentView = new Date(currentView.getFullYear(), currentView.getMonth() + 1, 1);
        renderCalendar(currentView);
    });

    document.addEventListener('click', (event) => {
        if (! datePicker.contains(event.target)) {
            closeCalendar();
        }
    });

    if (! dateInput.value) {
        setSelectedDate(today);
    } else {
        dateLabel.textContent = dayFormatter.format(parseDate(dateInput.value));
        renderCalendar(currentView);
    }
})();
</script>
<?= $this->endSection() ?>
