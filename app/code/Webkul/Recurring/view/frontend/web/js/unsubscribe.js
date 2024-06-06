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
    'mage/template',
    'uiComponent',
    'mage/validation',
    'ko',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/calendar'
    ], function (
        $,
        mageTemplate,
        Component,
        validation,
        ko,
        modal,
        $t
    ) {
    'use strict';
    return Component.extend({
        
        initialize: function () {           
            $('[id="unSubscribe"]').click(function() {
                $('#flag-form input,#flag-form textarea').removeClass('mage-error');
                $('.sidebar-main, .product.info.detailed, .actions').addClass('fade');          
                $('.wk-mp-model-flag-popup').addClass('_show');
                $('#wk-mp-flag-data').show();
                if ($('a[name="unSubscribe"]').length) {
                    var id = $(this).attr("data-id");
                    unsubscribeUrl = unsubscribeUrl + 'id/' + id;
                }
            });
            $('.wk-product-flag-close').click(function() {
                $('.sidebar-main, .product.info.detailed, .actions').removeClass('fade');        
                $('.wk-mp-model-flag-popup').removeClass('_show');
                $('#wk-mp-flag-data').addClass('display-none');        
            });  
            $("input[type='radio'][name='reason']").click(function() {
                $(".wk-flagreasons").each(function(){
                    $(this).find("label").removeClass("bold");
                })
                $("label[for='"+this.id +"']").addClass("bold");
            });  

            $('#flagbtn').click(function(){
                var unsubscribeUrl = window.unsubscribeUrl;
                let total=$('.wk-flag-form input[type="radio"]:checked').length;
                if (total == 0){
                    $('.error').text('Please select the cancellation reason').css('color','red');
                   return false;
                } else{
                    $('.error').text('');
                    $.ajax({
                        url:unsubscribeUrl,
                        data:$('#flag-form').serialize(),
                        type:'post',               
                        success:function(response) {
                            if(response.error){
                                alert({
                                    content: $t('Something went wrong.')
                                });
                            }
                        }
                    })
                }
        
            });  
        },
    });
    
    
});