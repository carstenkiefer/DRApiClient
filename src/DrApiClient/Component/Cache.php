<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Component;

use Doctrine\Common\Cache\PhpFileCache;

class Cache extends PhpFileCache {

    private static $cache = false;

    /**
     * @return PhpFileCache
     */
    public static function getInstance(array $configuration = []) {
        if(!self::$cache) {
            self::$cache = new PhpFileCache(
                $configuration["HC_DRAPICLIENT_CACHE_DIR"]
            );
        }
        return self::$cache;
    }
}

