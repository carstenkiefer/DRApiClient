<?php
/*
 * Copyright (c) 2020 cake.systems
 */

namespace Hc\DrApiClient\Resource;

/**
 * Class Order
 *
 * @property string $id
 * @property string $portal_account_id_type
 * @property string $date
 * @property string $current_date
 * @property int $invoice_number
 * @property string $platform
 * @property array $history
 * @property string $infos
 * @property string $hidden_infos
 * @property string $payment_method_id
 * @property float $invoice_amount
 * @property float $paid_amount
 * @property int $currency_id
 * @property Customer $customer
 * @property Shipping $shipping
 * @property OrderStatus $status
 * @property OrderLine[] $line
 * @property string $bill_url
 * @property string $paypal_transaction_id
 * @property array $add_field
 * @package Hc\DrApiClient\Resource
 */
class Order extends Resource {

    // ignored
}

