<?php

namespace App\Controllers;

use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTimeImmutable;

class DashboardController extends BaseController
{
    public function index()
    {
        $selectedMonth = month_key($this->request->getGet('month'));
        $currentMonth   = date('Y-m');
        $previousMonth  = previous_month_key($selectedMonth);

        [$start, $end] = month_period($selectedMonth);
        [$previousStart, $previousEnd] = month_period($previousMonth);

        $monthOptions = [];
        foreach (array_values(array_unique([$selectedMonth, $currentMonth, $previousMonth])) as $month) {
            $monthOptions[] = [
                'value' => $month,
                'label' => month_label($month),
                'selected' => $month === $selectedMonth,
            ];
        }

        try {
            $db = db_connect();

            $summary = $this->summaryForPeriod($db, $start, $end);
            $previousSummary = $this->summaryForPeriod($db, $previousStart, $previousEnd);
            $trend = $this->salesTrend($db, $start, $end);
            $loyalCustomers = $this->loyalCustomers($db, $start, $end);
            $latestTransactions = $this->latestTransactions($db, $start, $end);
            $metrics = $this->metrics($summary, $previousSummary, $previousMonth);

            return view('dashboard/index', [
                'title' => 'Dashboard',
                'active' => 'dashboard',
                'selectedMonth' => $selectedMonth,
                'selectedMonthLabel' => month_label($selectedMonth),
                'previousMonthLabel' => month_label($previousMonth),
                'monthOptions' => $monthOptions,
                'metrics' => $metrics,
                'summary' => $summary,
                'previousSummary' => $previousSummary,
                'trend' => $trend,
                'loyalCustomers' => $loyalCustomers,
                'latestTransactions' => $latestTransactions,
                'dashboardError' => null,
            ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'Dashboard data failed: {message}', ['message' => $exception->getMessage()]);

            return view('dashboard/index', [
                'title' => 'Dashboard',
                'active' => 'dashboard',
                'selectedMonth' => $selectedMonth,
                'selectedMonthLabel' => month_label($selectedMonth),
                'previousMonthLabel' => month_label($previousMonth),
                'monthOptions' => $monthOptions,
                'metrics' => [],
                'summary' => null,
                'previousSummary' => null,
                'trend' => [],
                'loyalCustomers' => [],
                'latestTransactions' => [],
                'dashboardError' => 'Data dashboard belum bisa dimuat karena koneksi database bermasalah.',
            ]);
        }
    }

    private function summaryForPeriod($db, string $start, string $end): array
    {
        $completed = $db->table('transactions')
            ->select('COALESCE(SUM(total),0) total_sales, COALESCE(SUM(profit),0) total_profit, COUNT(*) completed_transactions')
            ->where('status', 'completed')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->get()->getRowArray() ?? [];

        $canceled = $db->table('transactions')
            ->select('COUNT(*) canceled_transactions')
            ->where('status', 'canceled')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->get()->getRowArray() ?? [];

        $all = $db->table('transactions')
            ->select('COUNT(*) total_transactions')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->get()->getRowArray() ?? [];

        return [
            'total_sales' => (float) ($completed['total_sales'] ?? 0),
            'total_profit' => (float) ($completed['total_profit'] ?? 0),
            'completed_transactions' => (int) ($completed['completed_transactions'] ?? 0),
            'canceled_transactions' => (int) ($canceled['canceled_transactions'] ?? 0),
            'total_transactions' => (int) ($all['total_transactions'] ?? 0),
        ];
    }

    private function metrics(array $summary, array $previousSummary, string $previousMonth): array
    {
        return [
            [
                'label' => 'Total Penjualan',
                'value' => rupiah($summary['total_sales']),
                'note' => 'vs ' . month_label($previousMonth) . ' ' . rupiah($previousSummary['total_sales']),
            ],
            [
                'label' => 'Keuntungan',
                'value' => rupiah($summary['total_profit']),
                'note' => 'vs ' . month_label($previousMonth) . ' ' . rupiah($previousSummary['total_profit']),
            ],
            [
                'label' => 'Transaksi Selesai',
                'value' => (string) $summary['completed_transactions'],
                'note' => 'vs ' . month_label($previousMonth) . ' ' . (string) $previousSummary['completed_transactions'],
            ],
            [
                'label' => 'Transaksi Batal',
                'value' => (string) $summary['canceled_transactions'],
                'note' => 'vs ' . month_label($previousMonth) . ' ' . (string) $previousSummary['canceled_transactions'],
            ],
        ];
    }

    private function salesTrend($db, string $start, string $end): array
    {
        $dailySales = $db->table('transactions')
            ->select('transaction_date, COALESCE(SUM(total),0) daily_sales')
            ->where('status', 'completed')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->groupBy('transaction_date')
            ->orderBy('transaction_date', 'ASC')
            ->get()->getResultArray();

        $salesByDate = [];
        foreach ($dailySales as $row) {
            $salesByDate[$row['transaction_date']] = (float) $row['daily_sales'];
        }

        $firstDay = new DateTimeImmutable($start);
        $daysInMonth = (int) $firstDay->format('t');
        $bucketCount = 7;
        $bucketSize = (int) ceil($daysInMonth / $bucketCount);
        $trend = [];

        for ($bucket = 0; $bucket < $bucketCount; $bucket++) {
            $startDay = ($bucket * $bucketSize) + 1;
            $endDay = min($daysInMonth, $startDay + $bucketSize - 1);
            $bucketTotal = 0.0;

            foreach (range($startDay, $endDay) as $day) {
                $date = sprintf('%s-%02d', substr($start, 0, 7), $day);
                $bucketTotal += $salesByDate[$date] ?? 0;
            }

            $trend[] = [
                'label' => $startDay === $endDay ? (string) $startDay : $startDay . '-' . $endDay,
                'value' => $bucketTotal,
            ];
        }

        $max = max(array_column($trend, 'value') ?: [0]);

        foreach ($trend as $index => $point) {
            $trend[$index]['height'] = $max > 0 ? (int) round(($point['value'] / $max) * 100) : 0;
        }

        return $trend;
    }

    private function loyalCustomers($db, string $start, string $end): array
    {
        return $db->table('transactions t')
            ->select('c.id, c.name, COUNT(t.id) transaction_count, COALESCE(SUM(t.total),0) total_spent')
            ->join('customers c', 'c.id = t.customer_id')
            ->where('t.status', 'completed')
            ->where('t.transaction_date >=', $start)
            ->where('t.transaction_date <=', $end)
            ->groupBy('c.id, c.name')
            ->orderBy('transaction_count', 'DESC')
            ->orderBy('total_spent', 'DESC')
            ->limit(3)
            ->get()->getResultArray();
    }

    private function latestTransactions($db, string $start, string $end): array
    {
        return $db->table('transactions t')
            ->select('t.*, c.name customer_name')
            ->join('customers c', 'c.id = t.customer_id')
            ->where('t.transaction_date >=', $start)
            ->where('t.transaction_date <=', $end)
            ->orderBy('t.transaction_date', 'DESC')
            ->orderBy('t.id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
    }
}
