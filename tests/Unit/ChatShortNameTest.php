<?php

namespace Tests\Unit;

use App\Services\Chat\ChatPresenter;
use PHPUnit\Framework\TestCase;

class ChatShortNameTest extends TestCase
{
    public function test_short_name_skips_academic_titles(): void
    {
        $this->assertSame('Siti', ChatPresenter::shortName('Ir. Siti Aminah, M.Kom (Mentor CSIRT)'));
        $this->assertSame('Erina', ChatPresenter::shortName('Dr. Erina Nur Azizah, S.Kom., M.Cs'));
        $this->assertSame('Agus', ChatPresenter::shortName('Prof. Dr. Agus Widodo, M.Pd'));
        $this->assertSame('Aditya', ChatPresenter::shortName('Aditya Nugraha (Aktif Magang)'));
        $this->assertSame('', ChatPresenter::shortName(null));
    }
}
