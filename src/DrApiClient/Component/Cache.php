<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Component;

use Doctrine\Common\Cache\PhpFileCache;

class Cache {

    private static $cache = false;

    /**
     * @return PhpFileCache
     */
    public static function getInstance() {
        if(!self::$cache) {
            self::$cache = new PhpFileCache(
                $_ENV["HC_DRAPICLIENT_CACHE_DIR"]
            );
        }
        return self::$cache;
    }
}

