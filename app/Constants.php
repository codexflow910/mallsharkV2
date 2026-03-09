<?php

namespace App;

class Constants
{
    const WELCOME_MESSAGE = "Welcome to Mallshark! Enjoy exclusive deals and happy shopping!";
    const ACCOUNT_ACTIVATION_SUBJECT = 'Hooray! Your account activation is complete';
    // const ACCOUNT_ACTIVATION_DESCRIPTION = 'A new user has been activated with the name';
    const ORDER_CREATE_SUBJECT_BUYER = 'A new order has been placed: #';
    // const ORDER_CREATE_DESCRIPTION_BUYER = 'You placed a new order on Mallshark.';
    const ORDER_CREATE_SUBJECT_SELLER = 'A new order has been received: #';
    // const ORDER_CREATE_DESCRIPTION_SELLER = 'You have received a new order.';
    // const REQUEST_CANCEL_ORDER_BUYER_DESCRIPTION = 'You applied for a refund order';
    const REQUEST_CANCEL_ORDER_BUYER_SUBJECT = 'You have applied for a refund order: #';
    const REQUEST_CANCEL_ORDER_SELLER_SUBJECT = ' has applied for a refund order: #';
    // const REQUEST_CANCEL_ORDER_SELLER_DESCRIPTION = 'applied for a refund order';
    const ACCEPT_REFUND_REQUEST_SELLER_SUBJECT = 'Your order has been refunded: #';
    // const ACCEPT_REFUND_REQUEST_SELLER_DESCRIPTION = 'Order has been refunded';
    const CANCEL_ORDER_BUYER_SUBJECT = 'Your order has been refunded: #';
    const SHIPPED_ORDER_BUYER_SUBJECT = 'Your order has been shipped: #';
    const SHIPPED_ORDER_BUYER_DESCRIPTION = 'Order status changed to shipped';
    const DISPUTED_ORDER_BUYER_SUBJECT = 'Your refund request rejected for order: #';
    const STORE_DEACTIVATED_SUBJECT = 'Your store has been deactivated';
    const STORE_DISABLE_SUBJECT = 'Your store has been disabled';
    const STORE_APPROVED_SUBJECT = 'Your store has been approved';
    const STORE_REJECTED_SUBJECT = 'Your store has been rejected';
    const STORE_ACTIVATED_SUBJECT = 'Your store has been activated';
    const STORE_ENABLE_SUBJECT = 'Your store has been enabled';
    const SUPER_ADMIN_ID = '12';
    const EDIT_PRODUCT_SUBJECT = 'Super admin edited your product';
    const SUPER_ADMIN_DELETE_PRODUCT = 'Super admin deleted your product';
    const SUPER_ADMIN_RESTORE_PRODUCT = 'Super admin restored your product';
    const SUPER_ADMIN_REJECTE_PRODUCT = 'Super admin rejected your product';
    const SUPER_ADMIN_APPROVE_PRODUCT = 'Super admin approved your product';
    const SUPER_ADMIN_ACTIVE_PRODUCT = 'Super admin activated your product';
    const SUPER_ADMIN_DEACTIVE_PRODUCT = 'Super admin deactivated your product';
}