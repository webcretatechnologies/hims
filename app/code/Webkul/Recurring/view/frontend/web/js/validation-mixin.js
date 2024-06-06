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
        'jquery/ui',
        'jquery/validate',
        'mage/translate'
    ], function ($) {
    'use strict' 
    return function () {
        $.validator.addMethod(
            'qty-validator-message',
            function (value) {                
                if(value == ''  || value == 0){
                    return false;
                }
                return true;
            },
            $.mage.__('Contact number should be between 6 and 15 digits.')
        );
    }
 });
