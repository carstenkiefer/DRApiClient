<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Endpoint;

use Hc\DrApiClient\Endpoint\Endpoint;

class PaymentMethod extends Endpoint {

    protected string $name = "payment_method";

    protected array $endpoints = [
        "list" => "system/payment_method",
        "create" => false,
        "read" => false,
        "update" => false,
        "delete" => false
    ];

}