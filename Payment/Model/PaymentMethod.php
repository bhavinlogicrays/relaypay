<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */
 
namespace RelayPay\Payment\Model;
 
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
 
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'relaypay';
}
