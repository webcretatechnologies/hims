/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Webkul_Recurring/checkout/summary/initial-fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                return this.isFullMode();
            },
            getValue: function () {
                var price = 0;
                if (this.totals() && totals.getSegment('initial_fee') != null ) {
                    price = totals.getSegment('initial_fee').value;
                    return this.getFormattedPrice(price);
                }
                return 0;
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_initial_fee;
                    return priceUtils.formatPrice(price, quote.getBasePriceFormat());
                }
                return 0;
            }
        });
    }
);