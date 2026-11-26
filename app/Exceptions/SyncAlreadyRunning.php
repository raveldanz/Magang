<?php

namespace App\Exceptions;

use RuntimeException;

class SyncAlreadyRunning extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Sinkron masih berjalan.');
    }
}
