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
                type: 'recurringpaypal',
                component: 'Webkul_Recurring/js/view/payment/method-renderer/recurringpaypal'
            }
        );

        rendererList.push(
            {
                type: 'recurringstripe',
                component: 'Webkul_Recurring/js/view/payment/method-renderer/recurringstripe'
            }
        );

        return Component.extend({});
    }
);
