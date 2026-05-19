<?php

namespace App\Controllers;

class ReportController extends BaseController
{
    public function index()
    {
        [$start, $end] = period_range($this->request->getGet('start'), $this->request->getGet('end'));

        return view('reports/index', $this->reportData($start, $end) + [
            'title' => 'Laporan',
            'active' => 'reports',
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function excel()
    {
        [$start, $end] = period_range($this->request->getGet('start'), $this->request->getGet('end'));
        $data = $this->reportData($start, $end);

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel')
            ->setHeader('Content-Disposition', 'attachment; filename="laporan-chilibooks.xls"')
            ->setBody(view('reports/export_excel', $data + ['start' => $start, 'end' => $end]));
    }

    public function pdf()
    {
        [$start, $end] = period_range($this->request->getGet('start'), $this->request->getGet('end'));
        $data = $this->reportData($start, $end);
        $text = "ChiliBooks\nLaporan {$start} s/d {$end}\nOmzet: " . rupiah($data['summary']['total_sales']) . "\nProfit: " . rupiah($data['summary']['total_profit']);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="laporan-chilibooks.pdf"')
            ->setBody($this->simplePdf($text));
    }

    private function reportData(string $start, string $end): array
    {
        $db = db_connect();
        $summary = $db->table('transactions')
            ->select('COALESCE(SUM(total),0) total_sales, COALESCE(SUM(profit),0) total_profit, COUNT(*) total_transactions')
            ->where('status', 'completed')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->get()->getRowArray();

        $transactions = $db->table('transactions t')
            ->select('t.*, c.name customer_name')
            ->join('customers c', 'c.id = t.customer_id')
            ->where('t.transaction_date >=', $start)
            ->where('t.transaction_date <=', $end)
            ->orderBy('t.transaction_date', 'DESC')
            ->get()->getResultArray();

        $topProducts = $db->table('transaction_items ti')
            ->select('p.name, SUM(ti.qty) sold_qty, SUM(ti.subtotal) revenue, SUM(ti.profit) profit')
            ->join('products p', 'p.id = ti.product_id')
            ->join('transactions t', 't.id = ti.transaction_id')
            ->where('t.status', 'completed')
            ->where('t.transaction_date >=', $start)
            ->where('t.transaction_date <=', $end)
            ->groupBy('p.id, p.name')
            ->orderBy('sold_qty', 'DESC')
            ->get()->getResultArray();

        return compact('summary', 'transactions', 'topProducts');
    }

    private function simplePdf(string $text): string
    {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 18 Tf 72 760 Td (" . str_replace("\n", ") Tj T* (", $escaped) . ") Tj ET";
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj\n",
            "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
            "5 0 obj << /Length " . strlen($stream) . " >> stream\n{$stream}\nendstream endobj\n",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        return $pdf . "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
