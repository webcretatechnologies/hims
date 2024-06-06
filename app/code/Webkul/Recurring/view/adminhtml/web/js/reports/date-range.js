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
 define([
    "jquery",
    "jquery/ui",
    'mage/calendar'
], function ($) {
    'use strict';
    $.widget('mage.earningDateRange', {
        _create: function () {
            var self = this;
            $(".wk-mp-design").dateRange({
                'dateFormat':'mm/dd/yy',               
                'from': {
                    'id': 'from-date'
                },
                'to': {
                    'id': 'to-date'
                }
            });

            $('#save-btn').on('click', function(){
                let fromDate = $('#from-date').val();
                let toDate = $('#to-date').val();
                if(toDate != '' && fromDate == "") {
                    $('#from-date').addClass('required-entry');
                }else{
                    $('#from-date').removeClass('required-entry');
                }
            })
        }
    });
    return $.mage.earningDateRange;
});
