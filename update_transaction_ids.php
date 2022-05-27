<?php
/*
 * Copyright (c) 2021 Höfer Chemie GmbH
 */

require_once "bootstrap.php";

$from = (new DateTime())->sub(new DateInterval("P2D"));
$to = new DateTime();

$client = Hc\DrApiClient\Component\DrApiClient::getClient();

$currentPage = 1;
$messages = [];
$hasResults = true;

while ($hasResults) {
    $orders = $client->getList(\Hc\DrApiClient\Endpoint\Order::class, [
        "filter" => [
            "date" => [
                "from" => $from->format("Y-m-d"),
                "to" => $to->format("Y-m-d"),
            ],
            "platform" => [
                "operator" => "!=",
                "value" => [
                    "Amazon-Shop",
                    "Amazon-Business",
                    "eBay",
                    "Shopware",
                    "",
                    "b2b-CSV",
                    "dr_offer_app",
                    "real",
                ],
            ],
        ],
        "pagination" => [
            "page" => $currentPage++,
            "limit" => 200,
        ],
    ]);
    foreach ($orders as $order) {
        /* @var $order \Hc\DrApiClient\Resource\Order */
        $drOrderID = substr($order->id, 0, strlen($order->id) - 8);
        try {
            $result = $client->update(\Hc\DrApiClient\Endpoint\Order::class, $order->id, [
                "order" => [
                    "paypal_transaction_id" => $drOrderID,
                ],
            ]);
        } catch (RuntimeException $ex) {
            $messages[] = $ex->getMessage();
        }
    }
    $hasResults = (count($orders) > 0);
}

foreach ($messages as $message) {
    echo "$message\n";
}

