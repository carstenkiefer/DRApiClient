<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Exception;


use Throwable;

class EndpointUnsupportedActionException extends ApiException {

    public function __construct($message = "Unsupported endpoint action", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}

