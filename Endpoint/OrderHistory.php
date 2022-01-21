<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Endpoint;

class OrderHistory extends Endpoint {

    protected string $name = "history";

    protected array $endpoints = [
        "list" => false,
        "create" => false,
        "read" => "order/%ID%/history",
        "update" => "order/%ID%/history",
        "delete" => false
    ];

}