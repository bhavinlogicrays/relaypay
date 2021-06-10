<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace RelayPay\Payment\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;
    protected $fileName = '/var/log/relaypay_payment.log';
}
