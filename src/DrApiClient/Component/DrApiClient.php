<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Component;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use Hc\DrApiClient\Endpoint\Endpoint;

class DrApiClient {

    private static $client = false;

    protected static $MSG_SERVER_ERROR = "Fehler bei der Verbindung mit DreamRobot";

    private $user;
    private $pass;
    private $cache;

    private static $KEY_TOKEN = "DRAPICLIENT_TOKEN";

    private function __construct() {
        $this->user = $_ENV["HC_DRAPICLIENT_API_USER"];
        $this->pass = $_ENV["HC_DRAPICLIENT_API_PASS"];
        $this->cache = Cache::getInstance();
    }

    protected function getResourceHeaders(): array {
        return [
            "Content-Type" => "application/json encoding=" . $_ENV["HC_DRAPICLIENT_CHARSET"],
            "Accept" => "application/json",
            "Accept-Charset" => $_ENV["HC_DRAPICLIENT_CHARSET"],
            "Authorization" => "Bearer " . $this->getToken()
        ];
    }

    protected function getDefaultOptions(): array {
        return [
            "headers" => $this->getResourceHeaders()
        ];
    }

    protected function getOptions($json = []): array {
        return array_merge_recursive(
            $this->getDefaultOptions(),
            ["body" => "[".json_encode($json)."]"]
        );
    }

    public function getToken(): string {
        if ($this->cache->contains(self::$KEY_TOKEN)) return $this->cache->fetch(self::$KEY_TOKEN);
        $http = new Client(['base_uri' => $_ENV["HC_DRAPICLIENT_AUTHHOST"]]);
        $response = $http->post("token.php", [
            "json" => ["grant_type" => "client_credentials"],
            "headers" => [
                "Content-Type" => "application/json encoding=" . $_ENV["HC_DRAPICLIENT_CHARSET"]
            ],
            "auth" => [$_ENV["HC_DRAPICLIENT_API_USER"], $_ENV["HC_DRAPICLIENT_API_PASS"]]
        ]);
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents());
            $this->cache->save(self::$KEY_TOKEN, $data->access_token, 3600);
            return $data->access_token;
        }
        throw new ServerException(self::$MSG_SERVER_ERROR, null, $response);
    }

    public function create(string $endpointClass, array $data, array $options = []) {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $http = new Client(['base_uri' => $_ENV["HC_DRAPICLIENT_HOST"]]);
        if (!isset($data["portal_account_id"])) {
            $data["portal_account_id"] = $_ENV["HC_DRAPICLIENT_PORTAL_ACCOUNT_ID"];
        }
        $response = $http->post($endpoint->getEndpoint(Endpoint::$CREATE), array_merge($this->getDefaultOptions(), $options, ["json" => $data]));
        if ($response->getStatusCode() === 200) {
            $json = json_decode($response->getBody()->getContents(), false);
            return array_shift($json);
        }
        return false;
    }

    public function read(string $endpointClass, $id, array $options = []) {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $query = http_build_query($options);
        $request = new Request(
            "GET",
            $_ENV["HC_DRAPICLIENT_HOST"].$endpoint->getEndpoint(Endpoint::$READ)."?".$query,
            $this->getResourceHeaders()
        );
        $http = new Client(['base_uri' => $_ENV["HC_DRAPICLIENT_HOST"]]);
        $response = $http->send($request);
        if ($response->getStatusCode() === 200) {
            $json = json_decode($response->getBody()->getContents(), false);
            print_r($json);
            $data = $json->{$endpoint->getName()};
            $resourceClass = $endpoint->getResource();
            /* @var \Hc\DrApiClient\Resource\Resource $obj */
            $obj = new $resourceClass();
            $obj->setData($data);
            return $obj;
        }
        return false;
    }

    public function getList(string $endpointClass, array $options = []): array {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $query = http_build_query($options);
        $request = new Request(
            "GET",
            $_ENV["HC_DRAPICLIENT_HOST"].$endpoint->getEndpoint(Endpoint::$LIST)."?".$query,
            $this->getResourceHeaders()
        );
        $http = new Client(['base_uri' => $_ENV["HC_DRAPICLIENT_HOST"]]);
        $response = $http->send($request);
        if ($response->getStatusCode() === 200) {
            $list = [];
            $json = json_decode($response->getBody()->getContents(), false);
            $data = $json->{$endpoint->getName()};
            foreach ($data as $d) {
                $resourceClass = $endpoint->getResource();
                /* @var \Hc\DrApiClient\Resource\Resource $obj */
                $obj = new $resourceClass();
                $obj->setData($d);
                $list[] = $obj;
            }
            return $list;
        }
        return false;
    }

    public static function getClient(): DrApiClient {
        if (!self::$client) {
            self::$client = new DrApiClient();
        }
        return self::$client;
    }

}
