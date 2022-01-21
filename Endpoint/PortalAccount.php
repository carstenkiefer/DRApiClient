<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Endpoint;

use Hc\DrApiClient\Endpoint\Endpoint;

class PortalAccount extends Endpoint {

    protected string $name = "portal_account";

    protected array $endpoints = [
        "list" => "admin/portal_account",
        "create" => false,
        "read" => false,
        "update" => false,
        "delete" => false
    ];
}