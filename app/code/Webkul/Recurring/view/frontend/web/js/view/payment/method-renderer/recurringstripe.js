/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
/*browser:true*/
/*global define*/
define([
    "ko",
    "jquery",
    "Magento_Checkout/js/view/payment/default",
    "Magento_Checkout/js/action/set-payment-information",
    "Magento_Checkout/js/action/select-payment-method",
    "Magento_Checkout/js/checkout-data",
    "Magento_Checkout/js/model/full-screen-loader",
    "Magento_Checkout/js/model/quote",
    "Magento_Checkout/js/model/totals",
    "mage/translate",
    "mage/url",
    "Magento_Ui/js/modal/alert",
], function (
    ko,
    $,
    Component,
    setPaymentInformationAction,
    selectPaymentMethodAction,
    checkoutData,
    fullScreenLoader,
    quote,
    totals,
    $t,
    urlBuilder,
    alert
) {
    "use strict";
    /**
     * stripeConfig contains all the payment configuration
     */
    var stripeConfig = window.checkoutConfig.payment.recurringstripe;

    return Component.extend({
        defaults: {
            template: "Webkul_Recurring/payment/stripe/recurringstripe",
            stripeObject: null,
            logoUrl: stripeConfig.image_on_form,
        },

        /**
         * @override
         */
        initObservable: function () {
            var self = this;
            window.webkulRecurringStripeSelf = this;
            this._super();
            this.initStripeCheckout();
            return this;
        },

        initStripeCheckout: function () {
            this.stripeObject = Stripe(stripeConfig.api_publish_key, {
                betas: ["checkout_beta_4"],
            });
        },

        getCode: function () {
            return "recurringstripe";
        },

        /**
         * validate  to validate the payment method fields at checkout page
         *
         * @return boolean
         */
        validate: function () {
            return true;
        },

        afterPlaceOrder: function () {
            let that = this;
            that.redirectAfterPlaceOrder = false;
            let deferred = $.Deferred();
            $("body").trigger("processStart");
            $.ajax({
                url: stripeConfig.get_session_url,
                dataType: "json",
                contentType: "application/json",
                method: "get",
                success: function (response) {
                    if (response.id) {
                        deferred.resolve(response);
                    } else {
                        deferred.reject(response);
                    }
                },
                error: function (error) {
                    deferred.reject(error);
                },
            });

            deferred.promise().then(
                function (data) {
                    that.stripeObject.redirectToCheckout({
                        sessionId: data.id,
                    });
                },
                function (error) {
                    that.messageContainer.addErrorMessage({
                        message: $t("something went wrong"),
                    });
                }
            );
        },

        /**
         * selectPaymentMethod called when payment method is selected
         *
         * @return boolean
         */
        selectPaymentMethod: function address() {
            selectPaymentMethodAction(this.getData());
            checkoutData.setSelectedPaymentMethod(stripeConfig.method);
            return true;
        },

        /**
         * getData set payment method data for making it available in PaymentMethod Class
         *
         * @return string
         */
        getData: function () {
            var self = this;

            return {
                method: stripeConfig.method,
            };
        },
    });
});
