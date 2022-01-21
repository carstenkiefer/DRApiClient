<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Endpoint;

use Hc\DrApiClient\Exception\EndpointUnsupportedActionException;

abstract class Endpoint {

    public static string $ID_REPLACEMENT_KEY = "%ID%";

    public static string $LIST = "list";
    public static string $READ = "read";
    public static string $UPDATE = "update";
    public static string $CREATE = "create";
    public static string $DELETE = "delete";

    protected string $name = "ENDPOINT";

    protected array $endpoints = [
        "list" => false,
        "create" => false,
        "read" => false,
        "update" => false,
        "delete" => false
    ];

    public function getName() : string {
        return $this->name;
    }

    public function getEndpoint($action = "list", $id = false) : string {
        if(!array_key_exists($action, $this->endpoints) || !$this->endpoints[$action]) {
            throw new EndpointUnsupportedActionException("This endpoint doesn't support the action $action.");
        }
        $endpoint = $this->endpoints[$action];
        return str_replace(self::$ID_REPLACEMENT_KEY, $id, $endpoint);
    }

    public function getResource() : string {
        $class = get_class($this);
        return str_replace("\\Endpoint\\", "\\Resource\\", $class);
    }
}

