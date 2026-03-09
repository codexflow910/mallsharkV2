<?php

/*
 * Gamification constants are defined here 
 */

return [
    'shopzit_gamification' => [
        'DEFAULT_CASHBACK' => 2, //percent
        'STANDARD_CASHBACK' => 20, //percent
        'CHOOSEN_CASHBACK' => 75, //percent
        'CHOOSEN_CASHBACK_LIMIT' => 12, //hours
        'FLASH_SALE_TARGET' => 100000, //zit
        'CASHBACK_TARGET_TIME_LIMIT' => 30, //days
        'SELLER_REVENUE_START_PERCENTGE' => 50, //percent
        'SELLER_REVENUE_END_PERCENTGE' => 70, //percent
        'CASHBACK_POOL_SHARE_START_PECENTAGE' => 2, //percent
        'CASHBACK_POOL_SHARE_END_PECENTAGE' => 2, //percent
        'SUPERSUPPORTER_POOL_SHARE_START_PECENTAGE' => 25, //percent
        'SUPERSUPPORTER_POOL_SHARE_END_PECENTAGE' => 45, //percent
        'SHOPZIT_PERCENTAGE' => 5, //percent in form of transaction charges
        'TRANSACTION_CHARGES_PERCENTAGE_PP' => 5, //percent
        'TRANSACTION_CHARGES_PERCENTAGE_PP_OP' => 3, //parts
        'TRANSACTION_CHARGES_PERCENTAGE_PP_CP' => 2, //parts
        'TRANSACTION_CHARGES_PERCENTAGE_IP' => 3, //percent
        'TRANSACTION_CHARGES_PERCENTAGE_IP_OP' => 2, //parts
        'TRANSACTION_CHARGES_PERCENTAGE_IP_BP' => 1, //parts
        'TRANSACTION_CHARGES_PERCENTAGE_P' => 3, //percent
        'TRANSACTION_CHARGES_PERCENTAGE_P_OP' => 2, //parts
        'TRANSACTION_CHARGES_PERCENTAGE_P_BP' => 1, //parts

        /*
          // models structure start
          'CASHBACK_MODEL_A_PERCENTAGE_START_LIMIT' => 3, //percent
          'CASHBACK_MODEL_A_PERCENTAGE_END_LIMIT' => 25, //percent
          'CASHBACK_MODEL_B_PERCENTAGE_START_LIMIT' => 16, //percent
          'CASHBACK_MODEL_B_PERCENTAGE_END_LIMIT' => 95, //percent
          'CASH_BACK_FROM_SHOPZIT_MODEL_A' => 10, //percent
          'CASH_BACK_FROM_SHOPZIT_MODEL_B' => 20, //percent
          // models structure end
         * 
         */
        'AIP_PRICE_INCREASE_PERCENT' => 1, //percent+
        'PLEDGE_AMOUNT_GO_TO_CASHBACK_POOL' => 97, //percemnt
        //'PLEDGE_AMOUNT_GO_TO_BURN_POOL' => 1, //percemnt
        'AIP_PRICE_CONNECTED_INCREASE_PERCENT' => 100, //percent
        'DEFAULT_CASHBACK_TO_BUYER' => 2, //percent
        'MAXIMUM_CASHBACK_TO_BUYER' => 96, //percent
        'PLEDGES_IN_ONE_CREDIT' => 1000,
        'ONE_PLEDGE_PRICE' => 1, //ZIT
        'DEFAULT_CAMPAING_CASHBACK_TO_USER' => 3, // percent
        'ICON_PURCHASE_USER_WALLET_AMOUNT' => 97, // percet
        'ICON_PURCHASE_BURN_POOL' => 1, // percet
        'ICON_PURCHASE_CASHBACK_POOL' => 1, // percet
        'CAMPAIGN_RUN_DURATION' => 1, //campaign run duration
        'CAMPAIGN_SUPPORT_DURATION' => 2, //campaign run duration
        'SUPERSUPPORTER_PERCENT' => 1,	//min user icon price needed to become super supporter
		'CASHBACK_POOL_ID' => 1, //cashback pool id
        'SUPERSUPPORTER_POOL_ID' => 2, //supersupporter pool id
        'BURN_POOL_ID' => 3, //burn pool id
        'OPERATION_POOL_ID' => 4, //operation pool id
        'TRANSACTION_POOL_ID' => 5, //shopzit pool id
        'PAGINATION_LIMIT' => 15
    ]
        ];
?>