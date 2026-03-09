<?php

namespace Packages\Order\Contracts\V3;

class ProductConstants
{
    const STATUS_DEACTIVATED = '0';
    const STATUS_ACTIVE = '1';
    const STATUS_DELETED = '2';
    const STATUS_PENDING = '3';
    const STATUS_REJECTED = '4';
    const STATUS_DRAFT = '5';

    const SHIPPING_USPS = 'USPS';
    const SHIPPING_FEDEX = 'FEDEX';
}
