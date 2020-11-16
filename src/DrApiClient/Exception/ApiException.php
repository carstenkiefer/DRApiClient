<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Exception;

use Throwable;

class ApiException extends \RuntimeException {

    public function __construct($message = "API Exception", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

