<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace RelayPay\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLE = 'payment/relaypay/active';
    const XML_PATH_TITLE = 'payment/relaypay/title';
    const XML_PATH_ENVIRONMENT = 'payment/relaypay/environment';
    const XML_PATH_RELAYPAY_SANDBOX_MERCHANT_ID = 'payment/relaypay/relaypay_sandbox_merchant_id';
    const XML_PATH_RELAYPAY_SANDBOX_PUBLIC_KEY = 'payment/relaypay/relaypay_sandbox_public_key';
    const XML_PATH_RELAYPAY_SANDBOX_SECRET_KEY = 'payment/relaypay/relaypay_sandbox_secret_key';
    const XML_PATH_RELAYPAY_PRODUCTION_MERCHANT_ID = 'payment/relaypay/relaypay_production_merchant_id';
    const XML_PATH_RELAYPAY_PRODUCTION_PUBLIC_KEY = 'payment/relaypay/relaypay_production_public_key';
    const XML_PATH_RELAYPAY_PRODUCTION_SECRET_KEY = 'payment/relaypay/relaypay_production_secret_key';
    const XML_PATH_CGI_URL_SANDBOX = 'payment/relaypay/cgi_url_sandbox';
    const XML_PATH_CGI_URL_PRODUCTION = 'payment/relaypay/cgi_url_production';
    const XML_PATH_CGI_URL_RELAYPAY_CREATE_TRANSCTION_REQUEST = 'payment/relaypay/relaypay_create_transction_request';
    
    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /*
     * @return bool
     */
    public function isEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getTitle()
    {
        //for Store
        return $this->scopeConfig->getValue(
            self::XML_PATH_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * @return string
     */
    public function getEnvironment($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENVIRONMENT,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getSandboxMerchantID($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELAYPAY_SANDBOX_MERCHANT_ID,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getSandboxPublicKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELAYPAY_SANDBOX_PUBLIC_KEY,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getSandboxSecretKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELAYPAY_SANDBOX_SECRET_KEY,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getProductionMerchantID($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELAYPAY_PRODUCTION_MERCHANT_ID,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getProductionPublicKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELAYPAY_PRODUCTION_PUBLIC_KEY,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getProductionSecretKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELAYPAY_PRODUCTION_SECRET_KEY,
            $scope
        );
    }

    /*
     * @return string
     */
    public function getSandboxApiUrl()
    {   
        //for website
        return $this->scopeConfig->getValue(
            self::XML_PATH_CGI_URL_SANDBOX,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /*
     * @return string
     */
    public function getProductionApiUrl()
    {   
        //for website
        return $this->scopeConfig->getValue(
            self::XML_PATH_CGI_URL_PRODUCTION,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /*
     * @return string
     */
    public function getCreateTransactionRequest($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CGI_URL_RELAYPAY_CREATE_TRANSCTION_REQUEST,
            $scope
        );
    }
}
