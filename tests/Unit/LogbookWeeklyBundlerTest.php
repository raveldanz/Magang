<?php

namespace Tests\Unit;

use App\Services\LogbookWeeklyBundler;
use PHPUnit\Framework\TestCase;

class LogbookWeeklyBundlerTest extends TestCase
{
    public function test_calculate_counts_summary(): void
    {
        $bundler = new LogbookWeeklyBundler;

        $items = collect([
            (object) ['status' => 'pending'],
            (object) ['status' => 'pending'],
            (object) ['status' => 'approved'],
            (object) ['status' => 'rejected'],
            (object) ['status' => 'revision'],
        ]);

        $counts = $bundler->calculateCounts($items, 'status');

        $this->assertEquals(2, $counts['pending']);
        $this->assertEquals(1, $counts['approved']);
        $this->assertEquals(2, $counts['rejected']);
        $this->assertEquals(5, $counts['total']);
    }
}
