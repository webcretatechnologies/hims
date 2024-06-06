/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Webkul_Recurring/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData,
        setBillingAddressAction,
        globalMessageList
    ) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_Recurring/payment/paypal/recurringpaypal',
                    billingAgreement: ''
                },
                /**
                 * Redirect to paypal
                 */
                afterPlaceOrder: function () {
                    if (additionalValidators.validate()) {
                        //update payment method information if additional data was changed
                        this.selectPaymentMethod();
                        setPaymentMethodAction(this.messageContainer).done(
                            function () {
                                customerData.invalidate(['cart']);
                                $.mage.redirect(
                                    window.checkoutConfig.payment.recurringpaypal.redirectUrl.recurringpaypal
                                );
                            }
                        );

                        return false;
                    }
                },

                paymentexpresscheckout: function () {
                    this.updateAddresses();

                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect(
                                window.checkoutConfig.payment.recurringpaypal.redirectUrl.recurringpaypal
                            );
                            return false;
                        }
                    );
                },

                /**
                 * Trigger action to update shipping and billing addresses
                 */
                updateAddresses: function () {
                    if (window.checkoutConfig.reloadOnBillingAddress ||
                        !window.checkoutConfig.displayBillingOnPaymentMethod
                    ) {
                        setBillingAddressAction(globalMessageList);
                    }
                },
            }
        );
    }
);
