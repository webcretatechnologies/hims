/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery',
    'prototype',
], function(jQuery){
    return function(config) {
        jQuery('#generatewebhook').click(function () {
            jQuery('body').trigger('processStart');
            new Ajax.Request(config.ajax_url, {
                loaderArea:     false,
                asynchronous:   true,
                onSuccess: function(transport) {
                    jQuery('body').trigger('processStop');
                    location.reload();
                }
            });
        });
    }
});