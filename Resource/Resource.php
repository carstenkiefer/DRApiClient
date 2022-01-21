<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Resource;

use JetBrains\PhpStorm\Pure;

class Resource {

    public function setData($data): void {
        $src = new \ReflectionObject($data);
        $props = $src->getProperties();
        foreach ($props as $prop) {
            $name = $prop->getName();
            $this->{$name} = $data->{$name};
        }
    }

    #[Pure] public function __get($name) : Resource {
        return new Resource();
    }

    public function __toString(): string {
        $src = new \ReflectionObject($this);
        $props = $src->getProperties();
        $export = [];
        foreach ($props as $prop) {
            $name = $prop->getName();
            $value = $prop->getValue($this);
            if(!$value instanceof \stdClass and !is_array($value)) {
                $export[] = $value;
            }
        }
        return implode(",", $export);
    }

}

