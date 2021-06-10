<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace RelayPay\Payment\Controller\Index;

use Magento\Framework\App\Action\Context as Context;

class Auth extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    protected $curlFactory;

    protected $_storeManager;

    protected $_checkoutSession;
    
    protected $_currency;

    protected $messageManager;
    
    protected $quoteManagement;

    protected $customerFactory;

    protected $customerRepository;

    protected $orderService;

    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \RelayPay\Payment\Helper\Data $helper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->helper = $helper;
        $this->curl = $curl;
        $this->curlFactory = $curlFactory;
        $this->jsonHelper = $jsonHelper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_currency = $currency;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $is_enable = $this->helper->isEnabled();
        
        $environment = $this->helper->getEnvironment();
        
        $create_transaction_request = $this->helper->getCreateTransactionRequest();
        
        if ($environment == "sandbox") {
            $merchant_id = $this->helper->getSandboxMerchantID();
            $public_key = $this->helper->getSandboxPublicKey();
            $secret_key = $this->helper->getSandboxSecretKey();
            $api_url = $this->helper->getSandboxApiUrl();
            
            /*
             * Here We set currency AUD because relaypay sandbox provide AUD currency,
             * if we use other currency like our store currency transaction will not succeed
             */
            $currency= "AUD";
        } else {
            $merchant_id = $this->helper->getProductionMerchantID();
            $public_key = $this->helper->getProductionPublicKey();
            $secret_key = $this->helper->getProductionSecretKey();
            $api_url = $this->helper->getProductionApiUrl();
            $currency= $this->getCurrentCurrencyCode();
        }
        if ($is_enable == 1) {

            $storeName = $this->getStoreName();
            // Get quote
            $quote = $this->_checkoutSession->getQuote();
            
            // Get shipping address
            $getShippingAddressData = $quote->getShippingAddress()->getData();

            // Get billling address
            $getBillingAddress = $quote->getBillingAddress();
            $firstName = $getBillingAddress->getFirstName();
            $lastName = $getBillingAddress->getLastName();
            $customerName = $firstName.' '.$lastName;
            $customerEmail = $getBillingAddress->getEmail();
            $grandTotal = $quote->getGrandTotal();
            
            // Get quote id
            $quote_id = $this->getQuoteId();

            // Create Order From Quote
            $orderdata = $this->quoteManagement->submit($quote);
            $orderdata->setEmailSent(0);
            $increment_id = $orderdata->getRealOrderId();
            
            // Get Base Url
            $baseUrl = $this->getBaseUrl();
            
            // CallBackUrl
            $successUrl = $baseUrl.'relaypay/index/success/?order_id='.$increment_id;
            
            // Create an array
            $params = [
                "amount" => $grandTotal, // Get from Magento Order Data
                "customerName" => $customerName, // Get from Magento Order Data
                "customerEmail" => $customerEmail,
                "storeName" => $storeName, // Coming from Magento configuration
                "merchantId" => $merchant_id, // Coming from Magento configuration
                "currency" => $currency, // Get from Magento Order Data
                "orderId" => $increment_id,
                "callbackUrlRedirect" => $successUrl,
            ];
            $privateKey = $secret_key;

            // generate Sign
            $sign = $this->getSignOfApiHeader($params, $privateKey);

            // Create transaction api endpoint
            $api_request_url = $api_url."".$create_transaction_request;

            // Set header
            $setHeader = [
                "Content-Type:application/json",
                "Authorization:".$public_key,
                "Sign:".$sign,
                "publicKey:".$public_key
            ];

            /* Create curl factory */
            $httpAdapter = $this->curlFactory->create();
            
            // Initiate request
            $httpAdapter->write(
                \Zend_Http_Client::POST, // POST method
                $api_request_url, // api url
                '1.1', // curl http client version
                $setHeader, // set header
                json_encode($params) // pass parameter with json format
            );
            
            // execute api request
            $result = $httpAdapter->read();

            // get response
            $body = \Zend_Http_Response::extractBody($result);

            /* convert JSON to Array */
            $response = $this->jsonHelper->jsonDecode($body);
            
            // If redirect url get in response it will redirect to relaypay site
            if (array_key_exists("redirectionUrl", $response)) {
                $redirectionUrl = $response['redirectionUrl'];
                $resultRedirect->setUrl($redirectionUrl);
                return $resultRedirect ;

            } else {
                // If redirect url not get in response and get error message it will redirect to cart page
                $this->messageManager->addError(
                    __("We are facing issue to create an order. Please contact to store owner")
                );
                $this->_redirect('checkout/cart');
            }
        } else {
            // if module disabled from backend it will redirect to checkout cart page
            $this->messageManager->addError(
                __("Something went wrong.Please try again later or choose another payment method")
            );
            $this->_redirect('checkout/cart');
        }
    }

    /*
    * Common function for get Sign
    * This is use to get Sign
    * This sign must set into Header
    */
    public function getSignOfApiHeader($magentoOrderData, $privateKey)
    {
        $stringifyPayload = json_encode($magentoOrderData);
        $inputValOfSign = $stringifyPayload ."". $privateKey;
        return  hash('sha256', $inputValOfSign);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
    /**
     * Get website identifier
     *
     * @return string|int|null
     */
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
    
    /**
     * Get Store code
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
    
    /**
     * Get Store name
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }
    
    /**
     * Get current url for store
     *
     * @param bool|string $fromStore Include/Exclude from_store parameter from URL
     * @return string
     */
    public function getStoreUrl($fromStore = true)
    {
        return $this->_storeManager->getStore()->getCurrentUrl($fromStore);
    }
    
    /**
     * Check if store is active
     *
     * @return boolean
     */
    public function isStoreActive()
    {
        return $this->_storeManager->getStore()->isActive();
    }

    /**
     * Get default store currency code
     *
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        return $this->_storeManager->getStore()->getDefaultCurrencyCode();
    }
    
    /**
     * Get store base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
    
    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get currency symbol for current locale and currency code
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     * Checkout quote id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return (int)$this->_checkoutSession->getQuote()->getId();
    }

    /**
     * Checkout quote id
     *
     * @return int
     */
    public function getBaseurl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB
        );
    }
}
