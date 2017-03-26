/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Kevwis_HipayDirect/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData
    ) {

        'use strict';
        return Component.extend({
            defaults: {
                template: 'Kevwis_HipayDirect/payment/hipay',
                redirectAfterPlaceOrder: false,
                billingAgreement: ''
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                console.log(window.checkoutConfig);
                $.mage.redirect(
                    window.checkoutConfig.payment.hipay.redirectUrl[quote.paymentMethod().method]
                );
            },

            /** Init observable variables */
            initObservable: function () {
                this._super().observe('billingAgreement');
                return this;
            },

            /** Open window with  */
            showAcceptanceWindow: function (data, event) {
                window.open(
                    $(event.target).attr('href'),
                    'hipayacceptance',
                    'toolbar=no, location=no,' +
                    ' directories=no, status=no,' +
                    ' menubar=no, scrollbars=yes,' +
                    ' resizable=yes, ,left=0,' +
                    ' top=0, width=400, height=350'
                );

                return false;
            },

            /** Returns payment acceptance mark link path */
            getPaymentAcceptanceMarkHref: function () {
                return window.checkoutConfig.payment.hipay.paymentAcceptanceMarkHref;
            },

            /** Returns payment acceptance mark image path */
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.hipay.paymentAcceptanceMarkSrc;
            },

            /** Returns billing agreement data */
            getBillingAgreementCode: function () {
                return window.checkoutConfig.payment.hipay.billingAgreementCode[this.item.method];
            },

            /** Returns payment information data */
            getData: function () {
                var parent = this._super(),
                    additionalData = null;

                if (this.getBillingAgreementCode()) {
                    additionalData = {};
                    additionalData[this.getBillingAgreementCode()] = this.billingAgreement();
                }

                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            },

            /** Redirect to Hipay */
            continueToHiPay: function () {
                if (this.validate() && additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            console.log(window.checkoutConfig);
                            $.mage.redirect(
                                window.checkoutConfig.payment.hipay.redirectUrl[quote.paymentMethod().method]
                            );
                        }
                    );

                    return false;
                }
            }
        });
    }
);
