<?php

namespace Packages\Core\Config;

class CoreConstants
{
    const ORDER_PENDING = 'pending';
    const ORDER_SHIPPED = 'shipped';
    const ORDER_ON_HOLD = 'on_hold';
    const SHIPSTATION_ORDER_CREATE = 'awaiting_shipment';
    const SHIPSTATION_STORE_IDS = [145];

    const USPS_MAIL_CLASSES = [
        "USPS_GROUND_ADVANTAGE", "PRIORITY_MAIL", "PRIORITY_MAIL_EXPRESS"
    ];
    
    const USPS_RATE_INDICATORS  = [
        "3D", "3N", "3R", "5D", "BA", "BB", "CP", "CM", "DC", "DE", "DF", "DN", "DR",
        "E4", "E6", "FA", "FB", "FE", "FP", "FS", "LC", "LF", "LL", "LO", "LS", "NP",
        "OS", "P5", "P6", "P7", "P8", "P9", "Q6", "Q7", "Q8", "Q9", "Q0", "PA", "PL",
        "PM", "PR", "SN", "SP", "SR"
    ];

    const USPS_RESTRICTED_PROCESSING_CATEGORIES = [
        "OPEN_AND_DISTRIBUTE"
    ];
}
