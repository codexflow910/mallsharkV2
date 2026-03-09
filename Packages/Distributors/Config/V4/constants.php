<?php

namespace Packages\Distributors\Config\V4;

class DistributorConstants
{
    const STATUS_DRAFT = 5;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_INACTIVE = 0;

    const SHIPPING_USPS = 'USPS';
    const SHIPPING_FEDEX = 'FEDEX';
}