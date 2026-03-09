<?php
namespace Packages\Distributors\Config;
return [
    'status' => [
        'draft' => 5,
        'pending' => 1,
        'approved' => 2,
        'rejected' => 3
    ],
    'shipping' => [
        'usps' => 'USPS',
        'fedex' => 'FEDEX'
    ]
];