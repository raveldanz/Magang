<?php

namespace Tests\Unit;

use App\Support\PublicUrl;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicUrlTest extends TestCase
{
    public function test_loopback_url_is_replaced_by_public_url_when_configured(): void
    {
        URL::forceRootUrl('http://127.0.0.1:8000');
        config(['app.public_url' => 'http://192.168.1.10:8000/']);

        $this->assertSame(
            'http://192.168.1.10:8000/verify-certificate/abc123',
            PublicUrl::route('verify.certificate', 'abc123')
        );
    }

    public function test_url_is_untouched_without_public_url(): void
    {
        URL::forceRootUrl('http://localhost:8000');
        config(['app.public_url' => null]);

        $this->assertSame('http://localhost:8000/verify-letter/tok', PublicUrl::route('verify.letter', 'tok'));
    }

    public function test_non_loopback_request_host_is_kept(): void
    {
        URL::forceRootUrl('http://192.168.5.7:8000');
        config(['app.public_url' => 'http://192.168.1.10:8000']);

        $this->assertSame('http://192.168.5.7:8000/verify-letter/tok', PublicUrl::route('verify.letter', 'tok'));
    }
}
