<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Endpoint;


/**
 * Class Order Endpoint
 *
 * @package Hc\DrApiClient\Endpoint
 */
class Order extends Endpoint {

    protected $name = "order";

    protected $endpoints = [
        "list" => "order",
        "create" => "order",
        "read" => "order/%ID%",
        "update" => false,
        "delete" => false
    ];

}

