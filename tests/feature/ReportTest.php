<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ReportTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testReportIndexRendersCustomPeriodPicker(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('reports?start=2026-06-01&end=2026-06-30');

        $result->assertOK();
        $result->assertSee('data-report-range');
        $result->assertSee('Cara membaca angka');
        $result->assertSee('Cash-in per metode');
        $result->assertSee('Export PDF');
        $result->assertSee('Export Excel');
        $result->assertDontSee('type="date"');
    }

    public function testReportExcelUsesExcelContentType(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('reports/excel?start=2026-06-01&end=2026-06-30');

        $result->assertOK();
        $result->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $result->assertSee('Laporan ChiliBooks');
    }

    public function testReportPdfUsesPdfContentType(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('reports/pdf?start=2026-06-01&end=2026-06-30');

        $result->assertOK();
        $result->assertHeader('Content-Type', 'application/pdf');
        $result->assertSee('LAPORAN CHILIBOOKS');
    }
}
