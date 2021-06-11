<?php
/**
 * Copyright © 2021 RelayPay Development Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace RelayPay\Payment\Controller\Index;

use Magento\Framework\App\Action\Context as Context;

class Success extends \Magento\Framework\App\Action\Action
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
    
    protected $customerFactory;

    protected $customerRepository;

    protected $_logger;

    protected $request;

    protected $transactionBuilder;

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
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \RelayPay\Payment\Logger\Logger $logger,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
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
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->request = $request;
        $this->transactionBuilder = $transactionBuilder;
        
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
        } else {
            $merchant_id = $this->helper->getProductionMerchantID();
            $public_key = $this->helper->getProductionPublicKey();
            $secret_key = $this->helper->getProductionSecretKey();
            $api_url = $this->helper->getProductionApiUrl();
        }
        if ($is_enable == 1) {
          
            $storeName = $this->getStoreName();
            // Get current currency code
            $currency= $this->getCurrentCurrencyCode();

            // $this->request->getParams(); // all params
            $orderIncrementId = $this->request->getParam('order_id');
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            
            // it's require for redirect order success page
            $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
            $this->_checkoutSession->setLastQuoteId($order->getQuoteId());
            $this->_checkoutSession->setLastOrderId($order->getEntityId());

            // Get transaction detail api endpoint
            $api_endpoint = "api/v1/merchant/transaction";
            $getTransactionendpont = $api_endpoint."/?merchantId=".$merchant_id."&orderId=".$orderIncrementId;
            $api_request_url = $api_url."".$getTransactionendpont;

            // Set Header
            $setHeader = [
                "Authorization:".$public_key
            ];

            /* Create curl factory */
            $httpAdapter = $this->curlFactory->create();
            
            // Initiate request
            $httpAdapter->write(
                \Zend_Http_Client::GET, // POST method
                $api_request_url, // api url
                '1.1', // curl http client version
                $setHeader // set header
            );
            
            // execute api request
            $result = $httpAdapter->read();

            // get response
            $body = \Zend_Http_Response::extractBody($result);

            /* convert JSON to Array */
            $response = $this->jsonHelper->jsonDecode($body);
            
            // Generate relaypay transaction log from response
            $this->_logger->info(json_encode($response));

            $responseData = $paymentData = [];
            
            if ((isset($response['orderStatus']) && $response['orderStatus'] === 'Pending') ||
                (isset($response['orderStatus']) && $response['orderStatus'] === 'Success')) {
               
                $responseData['transactionId'] = $response['transactionId'];
                $responseData['orderId'] = $response['orderId'];
                $responseData['orderStatus'] = $response['orderStatus'];
                $responseData['customerName'] = $response['customerName'];
                $responseData['customerEmail'] = $response['customerEmail'];
                $responseData['amount'] = $response['amount'];

                $order->setAdditionalData($responseData);
                $paymentData['transactionId']=$response['transactionId'];

                // Prepare payment object
                $payment = $order->getPayment();
                $payment->setMethod('relaypay');
                $method = $payment->getMethodInstance();
                $methodTitle = $method->getTitle();
                $paymentData['method_title'] = $methodTitle;
                $order->getPayment()->setAdditionalInformation($paymentData);
                $payment->setLastTransId($response['transactionId']);
                $payment->setTransactionId($response['transactionId']);

                // Formatted price
                $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
                
                // Prepare transaction
                $transaction = $this->transactionBuilder->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($response['transactionId'])
                    ->setAdditionalInformation($paymentData)
                    ->setFailSafe(true)
                    ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);

                // Add transaction to payment
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    __('The authorized amount is %1.', $formatedPrice)
                );
                $payment->setParentTransactionId($paymentData);

                // Change order status pending to processing order
                $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

                // Save payment, transaction and order
                $payment->save();
                $order->save();
                $transaction->save();
            }
            if ($order) {
                // it's require for get original order id to order success page
                $this->_checkoutSession->setLastOrderId($order->getId())
                                   ->setLastRealOrderId($order->getIncrementId())
                                   ->setLastOrderStatus($order->getStatus());
            }
            $this->messageManager->addSuccess(__("Thank you for your purchase!"));
            // Redirect to order success page
            $resultRedirect->setPath('checkout/onepage/success');
            return $resultRedirect;
        } else {
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
}
