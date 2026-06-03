<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MonthHelperTest extends CIUnitTestCase
{
    public function testMonthHelpersAreConsistent(): void
    {
        helper('chili');

        $this->assertSame('2026-06', month_key('2026-06'));
        $this->assertSame('2026-05', previous_month_key('2026-06'));
        $this->assertSame(['2026-06-01', '2026-06-30'], month_period('2026-06'));
        $this->assertSame('Juni 2026', month_label('2026-06'));
    }

    public function testDashboardPeriodHelpersSupportAllAndThreeMonths(): void
    {
        helper('chili');

        $selectedPeriod = dashboard_period_key('all');
        $this->assertSame('all', $selectedPeriod);
        $this->assertSame('All', dashboard_period_label('all'));

        $options = dashboard_period_options(date('Y-m'));

        $this->assertCount(4, $options);
        $this->assertSame('all', $options[0]['value']);
        $this->assertSame('All', $options[0]['label']);
        $this->assertSame(date('Y-m'), $options[1]['value']);
        $this->assertSame(previous_month_key(date('Y-m')), $options[2]['value']);
        $this->assertSame(previous_month_key(previous_month_key(date('Y-m'))), $options[3]['value']);
    }
}
