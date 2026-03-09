<?php
namespace Packages\Store\Config\V2;
return [
    'status' => [
        'draft' => 5,
        'pending' => 1,
        'approved' => 2,
        'rejected' => 3,
        'mission_day' => 14
    ],
    'shipping' => [
        'usps' => 'USPS',
        'fedex' => 'FEDEX'
    ]
];