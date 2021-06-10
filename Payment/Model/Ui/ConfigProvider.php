<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace RelayPay\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use RelayPay\Payment\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'relaypay';
    
    private $config;

    public function __construct(
        Config $config,
        \Magento\Framework\UrlInterface $urlBuilder,
        ResolverInterface $localeResolver
    ) {
    
        $this->config = $config;
        $this->_urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isActive(),
                    'redirectUrl'=> $this->getRedirectUrl(),
                    'title' => $this->config->getTitle(),
                    'environment' => $this->config->getEnvironment()
                ]
            ]
        ];
    }
    public function getRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('relaypay/index/auth');
    }
}
