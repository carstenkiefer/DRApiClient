<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Component;

use Hc\GuzzleHttp\Client;
use Hc\GuzzleHttp\Psr7\Request;
use Hc\DrApiClient\Endpoint\Endpoint;
use Hc\DrApiClient\Exception\ApiException;
use Hc\DrApiClient\Resource\Resource;

class DrApiClient {

    private static $client = false;

    protected static $MSG_SERVER_ERROR = "Fehler bei der Verbindung mit DreamRobot";

    private $user;
    private $pass;
    private $cache;

    private $config;

    private static $KEY_TOKEN = "DRAPICLIENT_TOKEN";

    private function __construct(array $config = [], \Doctrine\Common\Cache\Cache $cache = null) {
        $this->config = $config;
        $this->user = $config["HC_DRAPICLIENT_API_USER"];
        $this->pass = $config["HC_DRAPICLIENT_API_PASS"];
        $this->cache = $cache ?: Cache::getInstance($config);
    }

    protected function getResourceHeaders(): array {
        return [
            "Content-Type" => "application/json encoding=" . $this->config["HC_DRAPICLIENT_CHARSET"],
            "Accept" => "application/json",
            "Accept-Charset" => $this->config["HC_DRAPICLIENT_CHARSET"],
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
            ["body" => "[" . json_encode($json) . "]"]
        );
    }

    public function getToken(): string {
        if ($this->cache->contains(self::$KEY_TOKEN)) return $this->cache->fetch(self::$KEY_TOKEN);
        $http = new Client(['base_uri' => $this->config["HC_DRAPICLIENT_AUTHHOST"]]);
        $response = $http->post("token.php", [
            "json" => ["grant_type" => "client_credentials"],
            "headers" => [
                "Content-Type" => "application/json encoding=" . $this->config["HC_DRAPICLIENT_CHARSET"]
            ],
            "auth" => [$this->config["HC_DRAPICLIENT_API_USER"], $this->config["HC_DRAPICLIENT_API_PASS"]]
        ]);
        if ($response->getStatusCode() <= 300) {
            $data = json_decode($response->getBody()->getContents());
            $this->cache->save(self::$KEY_TOKEN, $data->access_token, 3600);
            return $data->access_token;
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public function create(string $endpointClass, array $data, array $options = []): string {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $http = new Client(['base_uri' => $this->config["HC_DRAPICLIENT_HOST"]]);
        if (!isset($data["portal_account_id"])) {
            $data["portal_account_id"] = $this->config["HC_DRAPICLIENT_PORTAL_ACCOUNT_ID"];
        }
        $response = $http->post($endpoint->getEndpoint(Endpoint::$CREATE), array_merge($this->getDefaultOptions(), $options, ["json" => $data]));
        if ($response->getStatusCode() <= 300) {
            $json = json_decode($response->getBody()->getContents(), true);
            return array_shift($json);
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public function delete(string $endpointClass, $id) {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $http = new Client(['base_uri' => $this->config["HC_DRAPICLIENT_HOST"]]);
        $response = $http->delete($endpoint->getEndpoint(Endpoint::$DELETE, $id), [
            "headers" => ["Authorization" => "Bearer " . $this->getToken()]
        ]);
        if ($response->getStatusCode() <= 300) {
            $json = json_decode($response->getBody()->getContents(), true);
            return array_shift($json);
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public function update(string $endpointClass, $id, array $data, array $options = []): string {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $http = new Client(['base_uri' => $this->config["HC_DRAPICLIENT_HOST"]]);
        if (!isset($data["portal_account_id"])) {
            $data["portal_account_id"] = $this->config["HC_DRAPICLIENT_PORTAL_ACCOUNT_ID"];
        }
        $response = $http->post($endpoint->getEndpoint(Endpoint::$UPDATE, $id), array_merge($this->getDefaultOptions(), $options, ["json" => $data]));
        if ($response->getStatusCode() <= 300) {
            $json = json_decode($response->getBody()->getContents(), true);
            return array_shift($json);
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public function read(string $endpointClass, $id, array $options = []): Resource {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $query = http_build_query($options);
        $request = new Request(
            "GET",
            $this->config["HC_DRAPICLIENT_HOST"] . $endpoint->getEndpoint(Endpoint::$READ) . "?" . $query,
            $this->getResourceHeaders()
        );
        $http = new Client(['base_uri' => $this->config["HC_DRAPICLIENT_HOST"]]);
        $response = $http->send($request);
        if ($response->getStatusCode() <= 300) {
            $json = json_decode($response->getBody()->getContents(), false);
            $data = $json->{$endpoint->getName()};
            $resourceClass = $endpoint->getResource();
            /* @var \Hc\DrApiClient\Resource\Resource $obj */
            $obj = new $resourceClass();
            $obj->setData($data);
            return $obj;
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public function getList(string $endpointClass, array $options = []): array {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $query = http_build_query($options);
        $request = new Request(
            "GET",
            $this->config["HC_DRAPICLIENT_HOST"] . $endpoint->getEndpoint(Endpoint::$LIST) . "?" . $query,
            $this->getResourceHeaders()
        );
        $http = new Client(['base_uri' => $this->config["HC_DRAPICLIENT_HOST"]]);
        $response = $http->send($request);
        if ($response->getStatusCode() <= 300) {
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
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public static function getClient($config = [], $cache = null): DrApiClient {
        if (!self::$client) {
            self::$client = new DrApiClient($config, null);
        }
        return self::$client;
    }

}
