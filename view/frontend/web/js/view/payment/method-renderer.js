define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'relaypay',
                component: 'RelayPay_Payment/js/view/payment/method-renderer/relaypaypayment'
            }
        );
        return Component.extend({});
    }
);