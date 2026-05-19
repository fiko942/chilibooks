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
