<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Component;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Hc\DrApiClient\Endpoint\Endpoint;
use Hc\DrApiClient\Exception\ApiException;
use Hc\DrApiClient\Resource\Resource;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class DrApiClient {

    private static ?DrApiClient $client = null;

    private $user;
    private $pass;
    private AdapterInterface $cache;

    private static string $KEY_TOKEN = "DRAPICLIENT_TOKEN";

    private function __construct() {
        $this->user = $_ENV["DR_API_USER"];
        $this->pass = $_ENV["DR_API_PASS"];
        $this->cache = new FilesystemAdapter("DR_API", 3600);
    }

    protected function getResourceHeaders(): array {
        return [
            "Content-Type" => "application/json encoding=" . $_ENV["DR_CHARSET"],
            "Accept" => "application/json",
            "Accept-Charset" => $_ENV["DR_CHARSET"],
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
        $key = $this->cache->getItem(self::$KEY_TOKEN);
        if($key->isHit()) {
            return $key->get();
        }
        $http = new Client(['base_uri' => $_ENV["DR_AUTHHOST"]]);
        $response = $http->post("token.php", [
            "json" => ["grant_type" => "client_credentials"],
            "headers" => [
                "Content-Type" => "application/json encoding=" . $_ENV["DR_CHARSET"]
            ],
            "auth" => [$_ENV["DR_API_USER"], $_ENV["DR_API_PASS"]]
        ]);
        if ($response->getStatusCode() <= 300) {
            $data = json_decode($response->getBody()->getContents());
            $key->set($data->access_token);
            $this->cache->save($key);
            return $data->access_token;
        } else {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }

    public function create(string $endpointClass, array $data, array $options = []): string {
        /* @var \Hc\DrApiClient\Endpoint\Endpoint $endpoint */
        $endpoint = new $endpointClass();
        $http = new Client(['base_uri' => $_ENV["DR_HOST"]]);
        if (!isset($data["portal_account_id"])) {
            $data["portal_account_id"] = $_ENV["DR_PORTAL_ACCOUNT_ID"];
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
        $http = new Client(['base_uri' => $_ENV["DR_HOST"]]);
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
        $http = new Client(['base_uri' => $_ENV["DR_HOST"]]);
        if (!isset($data["portal_account_id"])) {
            $data["portal_account_id"] = $_ENV["DR_PORTAL_ACCOUNT_ID"];
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
            $_ENV["DR_HOST"] . $endpoint->getEndpoint(Endpoint::$READ) . "?" . $query,
            $this->getResourceHeaders()
        );
        $http = new Client(['base_uri' => $_ENV["DR_HOST"]]);
        $response = $http->send($request);
        if ($response->getStatusCode() <= 300) {
            $json = json_decode($response->getBody()->getContents(), false);
            $data = $json->{$endpoint->getName()};
            $resourceClass = $endpoint->getResource();
            /* @var \Hc\DrApiClient\Resource\Resource $obj */
            $obj = new $resourceClass();
            die(json_encode($data));
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
            $_ENV["DR_HOST"] . $endpoint->getEndpoint(Endpoint::$LIST) . "?" . $query,
            $this->getResourceHeaders()
        );
        $http = new Client(['base_uri' => $_ENV["DR_HOST"]]);
        $response = $http->send($request);
        if ($response->getStatusCode() <= 300) {
            $list = [];
            $json = \json_decode($response->getBody()->getContents(), false);
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

    public static function getClient($reset = false): DrApiClient {
        if($reset) {
            self::$client = false;
        }
        if (!self::$client) {
            self::$client = new DrApiClient();
        }
        return self::$client;
    }

}
