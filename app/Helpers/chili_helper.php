<?php

function rupiah(float|int|string|null $value): string
{
    return 'Rp' . number_format((float) ($value ?? 0), 0, ',', '.');
}

function active_nav(string $current, string $target): string
{
    return $current === $target ? 'active' : '';
}

function period_range(?string $start, ?string $end): array
{
    $start = $start ?: date('Y-m-01');
    $end = $end ?: date('Y-m-d');

    return [$start, $end];
}

function month_key(?string $month): string
{
    if ($month && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
        [$year, $monthNumber] = explode('-', $month);

        if (checkdate((int) $monthNumber, 1, (int) $year)) {
            return $month;
        }
    }

    return date('Y-m');
}

function dashboard_period_key(?string $period): string
{
    if ($period === 'all') {
        return 'all';
    }

    return month_key($period);
}

function previous_month_key(string $month): string
{
    return (new DateTimeImmutable($month . '-01'))->modify('-1 month')->format('Y-m');
}

function month_period(string $month): array
{
    $firstDay = new DateTimeImmutable($month . '-01');

    return [$firstDay->format('Y-m-01'), $firstDay->format('Y-m-t')];
}

function month_label(string $month): string
{
    static $labels = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    [$year, $monthNumber] = explode('-', $month);

    return ($labels[$monthNumber] ?? $monthNumber) . ' ' . $year;
}

function dashboard_period_label(string $period): string
{
    return $period === 'all' ? 'All' : month_label($period);
}

function dashboard_period_options(string $selectedPeriod): array
{
    $currentMonth = date('Y-m');
    $previousMonth = previous_month_key($currentMonth);
    $twoMonthsAgo = previous_month_key($previousMonth);

    $periods = array_values(array_unique([
        'all',
        $currentMonth,
        $previousMonth,
        $twoMonthsAgo,
    ]));

    return array_map(static function (string $period) use ($selectedPeriod): array {
        return [
            'value' => $period,
            'label' => dashboard_period_label($period),
            'selected' => $period === $selectedPeriod,
        ];
    }, $periods);
}

function normalize_indonesian_phone(?string $phone): string
{
    $digits = preg_replace('/\D+/', '', trim((string) $phone));

    if ($digits === '') {
        return '';
    }

    if (str_starts_with($digits, '62')) {
        return '+' . $digits;
    }

    if (str_starts_with($digits, '0')) {
        return '+62' . substr($digits, 1);
    }

    return '+62' . $digits;
}

function is_valid_indonesian_phone(?string $phone): bool
{
    return preg_match('/^\+62\d{8,13}$/', normalize_indonesian_phone($phone)) === 1;
}

function customer_whatsapp_url(?string $phone, ?string $name = null, ?string $location = null): ?string
{
    if (! is_valid_indonesian_phone($phone)) {
        return null;
    }

    $messageParts = ['Halo, saya ingin menindaklanjuti data pelanggan'];

    if ($name !== null && trim($name) !== '') {
        $messageParts[] = 'untuk ' . trim($name);
    }

    if ($location !== null && trim($location) !== '') {
        $messageParts[] = 'lokasi ' . trim($location);
    }

    $text = rawurlencode(trim(implode(' ', $messageParts)) . '.');

    return 'https://wa.me/' . ltrim(normalize_indonesian_phone($phone), '+') . '?text=' . $text;
}

function customer_can_contact_whatsapp(string $name, ?string $phone): bool
{
    return mb_strlen(trim($name)) >= 3 && is_valid_indonesian_phone($phone);
}
