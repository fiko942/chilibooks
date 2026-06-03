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
}
