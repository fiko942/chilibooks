<?php

namespace App\Controllers;

use App\Models\TransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        [$start, $end] = period_range($this->request->getGet('start'), $this->request->getGet('end'));
        $db = db_connect();

        $summary = (new TransactionModel())
            ->select('COALESCE(SUM(total),0) total_sales, COALESCE(SUM(profit),0) total_profit, COUNT(*) total_transactions')
            ->where('status', 'completed')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->first();

        $canceled = (new TransactionModel())
            ->where('status', 'canceled')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->countAllResults();

        $loyalCustomers = $db->table('transactions t')
            ->select('c.id, c.name, COUNT(t.id) transaction_count, COALESCE(SUM(t.total),0) total_spent')
            ->join('customers c', 'c.id = t.customer_id')
            ->where('t.status', 'completed')
            ->where('t.transaction_date >=', $start)
            ->where('t.transaction_date <=', $end)
            ->groupBy('c.id, c.name')
            ->orderBy('transaction_count', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        $latest = $db->table('transactions t')
            ->select('t.*, c.name customer_name')
            ->join('customers c', 'c.id = t.customer_id')
            ->orderBy('t.transaction_date', 'DESC')
            ->orderBy('t.id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'start' => $start,
            'end' => $end,
            'summary' => $summary,
            'canceled' => $canceled,
            'loyalCustomers' => $loyalCustomers,
            'latest' => $latest,
        ]);
    }
}
