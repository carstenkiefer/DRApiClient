<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Resource;

class Resource {

    public function setData($data): void {
        $src = new \ReflectionObject($data);
        $props = $src->getProperties();
        foreach ($props as $prop) {
            $name = $prop->getName();
            $this->{$name} = $data->{$name};
        }
    }

    public function __get($name) {
        return new Resource();
    }

    public function __toString() {
        $src = new \ReflectionObject($this);
        $props = $src->getProperties();
        $export = [];
        foreach ($props as $prop) {
            $name = $prop->getName();
            if(!$prop->{$name} instanceof \stdClass) {
                $export[] = $prop->{$name};
            }
        }
        return implode(",", $export);
    }

}

