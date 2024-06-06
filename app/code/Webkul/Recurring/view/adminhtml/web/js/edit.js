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
    "jquery"
], function ($) {
    'use strict';
    $.widget('recurring.edit', {
        options: {},
        _create: function () {
            $(document).ready(function () {
                $('#save').on('click',function(){
                    if($('#edit_form').valid()){
                        $('body').trigger('processStart');
                    }
                    
                });
            });
        }
    });
    return $.recurring.edit;
});
