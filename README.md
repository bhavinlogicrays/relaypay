Magento2-Relaypay Payment module overview 
=========================================

RelayPay helps boost sales by giving your customers the ability to pay by credit card or from the security of their RelayPay accounts.

During checkout, the customer is redirected to the secure RelayPay site to complete the payment
information.

The customer is then returned to your store to complete the remainder of the checkout process.

This extension is allows payments using the RelayPay (https://www.relaypay.com/) payment
gateway.


How install module 
==================

1. First download the Magento module (RelayPay_Payment) from the store and unzip the
module

2. Use the Ftp client to upload or copy to extension in your [magento_root]/app/code directory

3. After upload module path will be [magento_root]/app/code/RelayPay/Payment

4. Open the command line (connect ssh) and go to the Magento root directory then execute
one by one below commands:

    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:static-content:deploy -f
    php bin/magento indexer:reindex
    php bin/magento cache:clean
    php bin/magento cache:flush
    chmod -R 777 var/ pub/ generated/
    ```
5. Enable and configure RelayPay in Magento Admin under Stores/Configuration/Payment Methods/RelayPay

System Configuration
====================
1. open Magento 2 Admin and Go Store > Configuration > Sales > Payment Methods
and select RelayPay

2. Enable payement method

3. Here you can see multiple configuration setting

    Enabled :  Enable payment method
    Title : Specify a custom title for the payment method for checkout
    Environment : Choose sandbox or production
    Merchant ID (Sandbox) : Specify Merchant ID for sandbox environment
    Public Key (Sandbox) : Specify Public Key for sandbox
    Secret Key / Private Key (Sandbox) : Specify Secret Key or Private Key for sandbox environment
    Merchant ID (Production) : Specify Merchant ID for production environment
    Public Key (Production) : Specify Public Key for production environment
    Secret Key / Private Key (Production) : Specify Secret Key or Private key for production environment

4. Specify title for the payment method for frontend checkout

5. Choose your environment sandbox or production from the dropdown

6. Specify Merchant ID, publicKey, and secretKey or privateKey which you can get from
Merchant Dashboard

7. Fill the Merchant ID, publicKey, and secretKey or privateKey in the input fields

8. Save Config

9. Clear the cache

Copyright © 2021 RelayPay Development Inc. All rights reserved.

