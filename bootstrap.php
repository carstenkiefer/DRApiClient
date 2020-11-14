<?php
/*
 * Copyright (c) 2020 cake.systems
 */

require_once "vendor/autoload.php";

use Dotenv\Dotenv;

$dotenvFile = file_exists(__DIR__.".env.local") ? ".env.local" : ".env";

$dotenv = Dotenv::createImmutable(__DIR__, $dotenvFile);
$dotenv->load();

