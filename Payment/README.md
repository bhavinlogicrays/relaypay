Magento2-Relaypay
======================

Magento2 extension to allow payments using the [RelayPay](https://www.relaypay.com/) payment gateway.


Install
=======

1. Go to Magento2 root folder

3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable RelayPay_Payment --clear-static-content
    php bin/magento setup:upgrade
    ```
4. Enable and configure RelayPay in Magento Admin under Stores/Configuration/Payment Methods/RelayPay

Copyright © 2021 RelayPay Development Inc. All rights reserved.

