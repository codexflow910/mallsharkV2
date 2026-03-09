<?php

namespace Packages\Order\Config;

class OrderConstants
{
    const REQUEST_CANCEL_ORDER_BUYER_SUBJECT = 'You have applied for a refund order: #';
    const REQUEST_CANCEL_ORDER_SELLER_SUBJECT = ' has applied for a refund order: #';

    const ORDER_PENDING = 'pending';
    const ORDER_SHIPPED = 'shipped';
    const ORDER_DISPUTED = 'disputed';
    const ORDER_REFUNDED = 'refunded';
    const ORDER_CANCELLED = 'cancelled';
    const PAYMENT_METHOD_STRIPE = 'stripe';
    const PAYMENT_METHOD_CRYPTO = 'crypto';
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const ORDER_PARTIAL_SHIPPED = 'partial_shipped';
}
