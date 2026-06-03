<?php

namespace App\Controllers;

use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTimeImmutable;

class DashboardController extends BaseController
{
    public function index()
    {
        $selectedPeriod = dashboard_period_key($this->request->getGet('month'));
        $selectedPeriodLabel = dashboard_period_label($selectedPeriod);
        $monthOptions = dashboard_period_options($selectedPeriod);
        $isAllPeriod = $selectedPeriod === 'all';

        $currentMonth = date('Y-m');
        $previousMonth = previous_month_key($currentMonth);
        $twoMonthsAgo = previous_month_key($previousMonth);

        try {
            $db = db_connect();

            if ($isAllPeriod) {
                $summary = $this->summaryForPeriod($db);
                $comparisonSummary = null;
                $trend = $this->salesTrend($db);
                $loyalCustomers = $this->loyalCustomers($db);
                $latestTransactions = $this->latestTransactions($db);
                $paymentBreakdown = $this->paymentBreakdown($db);
                $cashInTotal = array_sum(array_column($paymentBreakdown, 'total_amount'));
                $trendBadge = 'Transaksi sepanjang waktu';
            } else {
                [$start, $end] = month_period($selectedPeriod);
                [$comparisonStart, $comparisonEnd] = month_period(previous_month_key($selectedPeriod));

                $summary = $this->summaryForPeriod($db, $start, $end);
                $comparisonSummary = $this->summaryForPeriod($db, $comparisonStart, $comparisonEnd);
                $trend = $this->salesTrend($db, $start, $end);
                $loyalCustomers = $this->loyalCustomers($db, $start, $end);
                $latestTransactions = $this->latestTransactions($db, $start, $end);
                $paymentBreakdown = $this->paymentBreakdown($db, $start, $end);
                $cashInTotal = array_sum(array_column($paymentBreakdown, 'total_amount'));
                $trendBadge = month_label(previous_month_key($selectedPeriod));
            }

            return view('dashboard/index', [
                'title' => 'Dashboard',
                'active' => 'dashboard',
                'selectedPeriod' => $selectedPeriod,
                'selectedPeriodLabel' => $selectedPeriodLabel,
                'monthOptions' => $monthOptions,
                'trendBadge' => $trendBadge,
                'metrics' => $this->metrics($summary, $comparisonSummary, $isAllPeriod),
                'trend' => $trend,
                'loyalCustomers' => $loyalCustomers,
                'latestTransactions' => $latestTransactions,
                'paymentBreakdown' => $paymentBreakdown,
                'cashInTotal' => $cashInTotal,
                'businessNotes' => business_transparency_notes(),
                'dashboardError' => null,
                'referenceMonths' => [
                    'current' => month_label($currentMonth),
                    'previous' => month_label($previousMonth),
                    'twoMonthsAgo' => month_label($twoMonthsAgo),
                ],
            ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'Dashboard data failed: {message}', ['message' => $exception->getMessage()]);

            return view('dashboard/index', [
                'title' => 'Dashboard',
                'active' => 'dashboard',
                'selectedPeriod' => $selectedPeriod,
                'selectedPeriodLabel' => $selectedPeriodLabel,
                'monthOptions' => $monthOptions,
                'trendBadge' => $isAllPeriod ? 'Transaksi sepanjang waktu' : month_label(previous_month_key($selectedPeriod)),
                'metrics' => [],
                'trend' => [],
                'loyalCustomers' => [],
                'latestTransactions' => [],
                'paymentBreakdown' => [],
                'cashInTotal' => 0,
                'businessNotes' => business_transparency_notes(),
                'dashboardError' => 'Data dashboard belum bisa dimuat karena koneksi database bermasalah.',
                'referenceMonths' => [
                    'current' => month_label($currentMonth),
                    'previous' => month_label($previousMonth),
                    'twoMonthsAgo' => month_label($twoMonthsAgo),
                ],
            ]);
        }
    }

    private function metrics(array $summary, ?array $comparisonSummary, bool $allTime): array
    {
        $comparisonText = $allTime || empty($comparisonSummary)
            ? 'Sejak awal pencatatan'
            : 'vs bulan sebelumnya';

        return [
            [
                'label' => 'Total Penjualan',
                'value' => rupiah($summary['total_sales']),
                'note' => $allTime
                    ? $comparisonText
                    : $comparisonText . ' ' . rupiah($comparisonSummary['total_sales'] ?? 0),
            ],
            [
                'label' => 'Total Keuntungan',
                'value' => rupiah($summary['total_profit']),
                'note' => $allTime
                    ? $comparisonText
                    : $comparisonText . ' ' . rupiah($comparisonSummary['total_profit'] ?? 0),
            ],
            [
                'label' => 'Transaksi',
                'value' => (string) $summary['total_transactions'],
                'note' => $allTime
                    ? $comparisonText
                    : $comparisonText . ' ' . (string) ($comparisonSummary['total_transactions'] ?? 0),
            ],
            [
                'label' => 'Pesanan',
                'value' => (string) $summary['completed_transactions'],
                'note' => $allTime
                    ? $comparisonText
                    : $comparisonText . ' ' . (string) ($comparisonSummary['completed_transactions'] ?? 0),
            ],
        ];
    }

    private function summaryForPeriod($db, ?string $start = null, ?string $end = null): array
    {
        $completed = $this->applyDateRange(
            $db->table('transactions')
                ->select('COALESCE(SUM(total),0) total_sales, COALESCE(SUM(profit),0) total_profit, COUNT(*) completed_transactions')
                ->where('status', 'completed'),
            $start,
            $end
        )->get()->getRowArray() ?? [];

        $canceled = $this->applyDateRange(
            $db->table('transactions')
                ->select('COUNT(*) canceled_transactions')
                ->where('status', 'canceled'),
            $start,
            $end
        )->get()->getRowArray() ?? [];

        $all = $this->applyDateRange(
            $db->table('transactions')
                ->select('COUNT(*) total_transactions'),
            $start,
            $end
        )->get()->getRowArray() ?? [];

        return [
            'total_sales' => (float) ($completed['total_sales'] ?? 0),
            'total_profit' => (float) ($completed['total_profit'] ?? 0),
            'completed_transactions' => (int) ($completed['completed_transactions'] ?? 0),
            'canceled_transactions' => (int) ($canceled['canceled_transactions'] ?? 0),
            'total_transactions' => (int) ($all['total_transactions'] ?? 0),
        ];
    }

    private function paymentBreakdown($db, ?string $start = null, ?string $end = null): array
    {
        $builder = $db->table('transactions')
            ->select('payment_method, COALESCE(SUM(total),0) total_amount')
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->orderBy('payment_method', 'ASC');

        $rows = $this->applyDateRange($builder, $start, $end)->get()->getResultArray();

        $totals = [
            'cash' => 0.0,
            'qris' => 0.0,
            'transfer' => 0.0,
        ];

        foreach ($rows as $row) {
            $method = (string) ($row['payment_method'] ?? '');
            if (! array_key_exists($method, $totals)) {
                continue;
            }

            $totals[$method] = (float) ($row['total_amount'] ?? 0);
        }

        return array_map(static fn (string $method): array => [
            'payment_method' => $method,
            'label' => transaction_payment_method_label($method),
            'total_amount' => $totals[$method],
        ], array_keys($totals));
    }

    private function salesTrend($db, ?string $start = null, ?string $end = null): array
    {
        if ($start === null || $end === null) {
            $bounds = $db->table('transactions')
                ->select('MIN(transaction_date) first_date, MAX(transaction_date) last_date')
                ->where('status', 'completed')
                ->get()
                ->getRowArray() ?? [];

            if (empty($bounds['first_date']) || empty($bounds['last_date'])) {
                return [];
            }

            $start = $bounds['first_date'];
            $end = $bounds['last_date'];
        }

        $dailySales = $this->applyDateRange(
            $db->table('transactions')
                ->select('transaction_date, COALESCE(SUM(total),0) daily_sales')
                ->where('status', 'completed')
                ->groupBy('transaction_date')
                ->orderBy('transaction_date', 'ASC'),
            $start,
            $end
        )->get()->getResultArray();

        $salesByDate = [];
        foreach ($dailySales as $row) {
            $salesByDate[$row['transaction_date']] = (float) $row['daily_sales'];
        }

        return $this->bucketTrend($salesByDate, $start, $end);
    }

    private function loyalCustomers($db, ?string $start = null, ?string $end = null): array
    {
        return $this->applyDateRange(
            $db->table('transactions t')
                ->select('c.id, c.name, COUNT(t.id) transaction_count, COALESCE(SUM(t.total),0) total_spent')
                ->join('customers c', 'c.id = t.customer_id')
                ->where('t.status', 'completed')
                ->groupBy('c.id, c.name')
                ->orderBy('transaction_count', 'DESC')
                ->orderBy('total_spent', 'DESC')
                ->limit(3),
            $start,
            $end
        )->get()->getResultArray();
    }

    private function latestTransactions($db, ?string $start = null, ?string $end = null): array
    {
        return $this->applyDateRange(
            $db->table('transactions t')
                ->select('t.*, c.name customer_name')
                ->join('customers c', 'c.id = t.customer_id')
                ->orderBy('t.transaction_date', 'DESC')
                ->orderBy('t.id', 'DESC')
                ->limit(5),
            $start,
            $end
        )->get()->getResultArray();
    }

    private function applyDateRange($builder, ?string $start, ?string $end)
    {
        if ($start !== null && $end !== null) {
            $builder->where('transaction_date >=', $start)
                ->where('transaction_date <=', $end);
        }

        return $builder;
    }

    private function bucketTrend(array $salesByDate, string $start, string $end): array
    {
        $startDate = new DateTimeImmutable($start);
        $endDate = new DateTimeImmutable($end);
        $spanDays = max(1, $startDate->diff($endDate)->days + 1);
        $bucketCount = 7;
        $bucketSize = (int) ceil($spanDays / $bucketCount);
        $trend = [];

        for ($bucket = 0; $bucket < $bucketCount; $bucket++) {
            $bucketStart = $startDate->modify('+' . ($bucket * $bucketSize) . ' days');
            $bucketEnd = $bucketStart->modify('+' . max(0, $bucketSize - 1) . ' days');

            if ($bucketStart > $endDate) {
                $bucketStart = $endDate;
            }

            if ($bucketEnd > $endDate) {
                $bucketEnd = $endDate;
            }

            $bucketTotal = 0.0;
            $cursor = $bucketStart;

            while ($cursor <= $bucketEnd) {
                $bucketTotal += $salesByDate[$cursor->format('Y-m-d')] ?? 0;
                $cursor = $cursor->modify('+1 day');
            }

            $trend[] = [
                'label' => $this->bucketLabel($bucketStart, $bucketEnd),
                'value' => $bucketTotal,
            ];
        }

        $max = max(array_column($trend, 'value') ?: [0]);

        foreach ($trend as $index => $point) {
            $trend[$index]['height'] = $max > 0 ? (int) round(($point['value'] / $max) * 100) : 0;
        }

        return $trend;
    }

    private function bucketLabel(DateTimeImmutable $start, DateTimeImmutable $end): string
    {
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $start->format('j M');
        }

        if ($start->format('M Y') === $end->format('M Y')) {
            return $start->format('j') . '-' . $end->format('j');
        }

        return $start->format('j M') . '-' . $end->format('j M');
    }
}
