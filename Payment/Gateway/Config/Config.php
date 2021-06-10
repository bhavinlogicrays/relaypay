<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace RelayPay\Payment\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const TITLE = 'title';
    const ENVIRONMENT = 'environment';
    const RELAYPAY_SANDBOX_MERCHANT_ID = 'relaypay_sandbox_merchant_id';
    const RELAYPAY_SANDBOX_PUBLIC_KEY = 'relaypay_sandbox_public_key';
    const RELAYPAY_SANDBOX_SECRET_KEY = 'relaypay_sandbox_secret_key';
    const RELAYPAY_PRODUCTION_MERCHANT_ID = 'relaypay_production_merchant_id';
    const RELAYPAY_PRODUCTION_PUBLIC_KEY = 'relaypay_production_public_key';
    const RELAYPAY_PRODUCTION_SECRET_KEY = 'relaypay_production_secret_key';
    const CGI_URL_SANDBOX = 'cgi_url_sandbox';
    const CGI_URL_PRODUCTION = 'cgi_url_production';
    const CGI_URL_RELAYPAY_CREATE_TRANSCTION_REQUEST = 'relaypay_create_transction_request';

    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    public function getTitle()
    {
        return $this->getValue(self::TITLE);
    }

    public function getEnvironment()
    {
        return $this->getValue(self::ENVIRONMENT);
    }

    public function getSandboxMerchantID()
    {
        return $this->getValue(self::RELAYPAY_SANDBOX_MERCHANT_ID);
    }

    public function getSandboxPublicKey()
    {
        return $this->getValue(self::RELAYPAY_SANDBOX_PUBLIC_KEY);
    }

    public function getSandboxSecretKey()
    {
        return $this->getValue(self::RELAYPAY_SANDBOX_SECRET_KEY);
    }

    public function getProductionMerchantID()
    {
        return $this->getValue(self::RELAYPAY_PRODUCTION_MERCHANT_ID);
    }

    public function getProductionPublicKey()
    {
        return $this->getValue(self::RELAYPAY_PRODUCTION_PUBLIC_KEY);
    }

    public function getProductionSecretKey()
    {
        return $this->getValue(self::RELAYPAY_PRODUCTION_SECRET_KEY);
    }

    public function getSandboxApiUrl()
    {
        return $this->getValue(self::CGI_URL_SANDBOX);
    }

    public function getProductionApiUrl()
    {
        return $this->getValue(self::CGI_URL_PRODUCTION);
    }

    public function getCreateTransactionRequest()
    {
        return $this->getValue(self::CGI_URL_RELAYPAY_CREATE_TRANSCTION_REQUEST);
    }
}
