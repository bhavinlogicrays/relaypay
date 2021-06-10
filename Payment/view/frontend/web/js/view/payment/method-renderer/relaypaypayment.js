define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/place-order',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data'
    ],
    function (Component, $, v, i,fullScreenLoader,placeOrderAction,messageList,quote, customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RelayPay_Payment/payment/relaypay-payment'
            },
             /**
             * @returns {exports.initialize}
             */
            initialize: function () {
                this._super();
                return this;
            },
            /** Returns send check to info */
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            getCode: function () {
                return 'relaypay';
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode()
                };
                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
                return data;
            },

            isActive: function () {
                return true;
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            /**
             * Prepare data to place order
             * @param {Object} data
             */
            PlaceOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this;
                if (this.validate()) {
                    self.isPlaceOrderActionAllowed(false);
                    $.mage.redirect(
                        window.checkoutConfig.payment.relaypay.redirectUrl
                    );
                } else {
                    return false
                }
            },

            /**
             * Show error message
             * @param {String} errorMessage
             */
            showError: function (errorMessage) {
                messageList.addErrorMessage({
                    message: errorMessage
                });
            }
        });
    }
);