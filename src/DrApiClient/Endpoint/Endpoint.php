<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Endpoint;

use App\DrApiClient\Exception\EndpointUnsupportedMethodException;

abstract class Endpoint {

    public static $ID_REPLACEMENT_KEY = "%ID%";

    public static $LIST = "list";
    public static $READ = "read";
    public static $UPDATE = "update";
    public static $CREATE = "create";
    public static $DELETE = "delete";

    protected $name = "ENDPOINT";

    protected $endpoints = [
        "list" => false,
        "create" => false,
        "read" => false,
        "update" => false,
        "delete" => false
    ];

    public function getName() {
        return $this->name;
    }

    public function getEndpoint($action = "list", $id = false) : string {
        if(!array_key_exists($action, $this->endpoints) || !$this->endpoints[$action]) {
            throw new EndpointUnsupportedActionException("This endpoint doesn't support the action $action.");
        }
        $endpoint = $this->endpoints[$action];
        return str_replace(self::$ID_REPLACEMENT_KEY, $id, $endpoint);
    }

    public function getResource() {
        $class = get_class($this);
        return str_replace("\\Endpoint\\", "\\Resource\\", $class);
    }
}

