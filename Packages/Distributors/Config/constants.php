<?php

namespace Packages\Distributors\Config;

class DistributorsConstants
{
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    const DISTRIBUTOR_CREATE_REQUEST = 'A new distributor request has been submitted and is awaiting your review.';
    const DISTRIBUTOR_UPDATE_REQUEST = 'A distributor request has been updated and requires your review.';
    const DISTRIBUTOR_DOCUMENT_REJECT = 'Your distributor request has been rejected.';
    const DISTRIBUTOR_DOCUMENT_APPROVE = 'Your distributor request has been approved.';
}
