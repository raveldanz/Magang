<?php

namespace Tests\Unit;

use App\Support\Grade;
use PHPUnit\Framework\TestCase;

class GradeTest extends TestCase
{
    public function test_grade_letter_scale(): void
    {
        $this->assertSame('A', Grade::letter(100));
        $this->assertSame('A', Grade::letter(85));
        $this->assertSame('AB', Grade::letter(84.99));
        $this->assertSame('AB', Grade::letter(75));
        $this->assertSame('B', Grade::letter(74.99));
        $this->assertSame('B', Grade::letter(65));
        $this->assertSame('BC', Grade::letter(64.99));
        $this->assertSame('BC', Grade::letter(55));
        $this->assertSame('C', Grade::letter(54.99));
        $this->assertSame('C', Grade::letter(40));
        $this->assertSame('E', Grade::letter(39.99));
        $this->assertSame('E', Grade::letter(0));
        $this->assertSame('-', Grade::letter(null));
    }

    public function test_grade_label(): void
    {
        $this->assertStringContainsString('A', Grade::label(90));
        $this->assertStringContainsString('AB', Grade::label(80));
        $this->assertStringContainsString('B', Grade::label(70));
        $this->assertStringContainsString('BC', Grade::label(60));
        $this->assertStringContainsString('C', Grade::label(50));
        $this->assertStringContainsString('E', Grade::label(30));
        $this->assertSame('-', Grade::label(null));
    }
}
