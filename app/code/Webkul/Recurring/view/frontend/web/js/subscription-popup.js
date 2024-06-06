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
        allPlansList         : ko.observableArray([]),
        planId               : ko.observableArray(0),
        termId               : ko.observableArray(0),
        startDate            : ko.observableArray(),
        initialFee           : ko.observableArray(),
        startDate            : ko.observableArray(),
        subscriptionsCharge  : ko.observableArray(),
        initialize: function () {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: $t("Select Subscription plan"),
                modalClass: 'term-plans',
                buttons: [{
                    text: $t("Close"),
                    class: 'product-popup-hide',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            var self        = this ;
            if (typeof window.termId !== "undefined" && window.termId != "") {
                self.termId(window.termId);
            }
            if (typeof window.planId !== "undefined" && window.planId != "") {
                self.planId(window.planId);
            }
            if (typeof window.startDate !== "undefined" && window.startDate != "") {
                self.startDate(window.startDate);
            }
            if (typeof window.initialFee !== "undefined" && window.initialFee != "") {
                self.initialFee(window.initialFee);
            }
            if (typeof window.subscriptionsCharge !== "undefined" && window.subscriptionsCharge != "") {
                self.subscriptionsCharge(window.subscriptionsCharge);
            }

            var wkPopUp     = $('.wkmodal');
            var wkAddToCart = $('#product-addtocart-button');
            var wkUpdateCart = $('#product-updatecart-button');

            /*
             * this function is used to open the modal for subscription list.
             */
            $('body').on('click','#product-subscribe-button',function () {
                var dataForm = $('#product_addtocart_form');
                if (dataForm.validation('isValid')) {
                    wkPopUp     = $('.wkmodal');
                    var popup   = modal(options, wkPopUp);
                    wkPopUp.modal('openModal');
                }
            });
            /*
             * this function is used to login page redirection.
             */
            $('body').on('click','#login-to-subscribe',function () {
                var dataUrl = $(this).attr("data-url");
                location.href = dataUrl;
            });
            
            $("body").on("click", ".wk-date" ,function () {
                $(this).parent().find('.wk-terminput-text').datepicker("show");
            });
            
            $('body').on('click', '.wkstyle' , function () {
                $(this).parent().find('.wk-terminput-text').datepicker("show");
            });
            /*
             * this function is used to open the modal for subscription list.
             */
            $('body').on('click','.selected-ubscription',function () {
                if ($("body .wkrequired").length) {
                    $("body .wkrequired").remove();
                }
                var termType =  $(this).parent().parent().find('.current-term');
                var startDate =  $(this).parent().parent().find('.wk-date');
                startDate.css('border-color','lightgrey');
                var dateValidate = 1 ;
                var myDate = new Date(startDate.val());
                myDate.setHours(0,0,0,0)
                var today = new Date();
                today.setHours(0,0,0,0)
                if (myDate < today ) {
                   dateValidate = 0;
                }
                if (startDate.val() == "" || dateValidate == 0) {
                    startDate.css('animation','shake 0.5s');
                    startDate.css('border-color','palevioletred');
                    startDate.parent().append(window.requiredTitle);
                    $("body .wkrequired").fadeOut(10000);
                } else {
                    startDate.css('border-color','lightgrey');
                }
                
                if (termType.val() && startDate.val() && $(this).attr('plan_id') && dateValidate) {
                    self.termId(termType.val());
                    self.planId($(this).attr('plan_id'));
                    self.startDate(startDate.val());
                    self.initialFee($(this).attr('initial_fee'));
                    self.subscriptionsCharge($(this).attr('subscription_charge'));
                    $('body').trigger('processStart');
                    wkAddToCart.click();
                    wkUpdateCart.click();
                    setTimeout(function () {
                        wkPopUp     = $('.wkmodal');
                        wkPopUp.modal('closeModal');
                        $('body').trigger('processStop');
                        self.startDate('');
                        self.planId('');
                    }, 1000);
                }
            });
                    
            if (window.subscription) {
                $.each(window.subscription, function (i,v) {
                    self.allPlansList.push(v);
                });
            }
            setTimeout(function () {
                $("body .wk-date").each(function () {
                    $(this).calendar({
                        dateFormat: "M/d/yy",
                        minDate: 0,
                        changeYear: true,
                        yearRange: "-100:+100",
                    }).on('change', function () {
                        var startDate = $(this);
                        if ($("body .wkrequired").length) {
                            $("body .wkrequired").remove();
                        }
                        var txtDate = startDate.val()
                        var currVal = txtDate;
                        var validCheck = true;;
                        if (currVal == '') {
                            validCheck = false;
                        } else {
                            var regexDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
                            var dateArray = currVal.match(regexDatePattern);
                            
                            if (dateArray == null) {
                                validCheck = false;
                            } else {
                                var dateMonth,dateDay, dateYear ;
                                dateMonth = dateArray[1];
                                dateDay= dateArray[3];
                                dateYear = dateArray[5];
                                
                                if (dateMonth < 1 || dateMonth > 12) {
                                    validCheck = false;
                                } else if (dateDay < 1 || dateDay> 31) {
                                    validCheck = false;
                                } else if ((dateMonth==4 || dateMonth==6 || dateMonth==9 || dateMonth==11) && dateDay ==31) {
                                    validCheck = false;
                                } else if (dateMonth == 2) {
                                    var isleap = (dateYear % 4 == 0 && (dateYear % 100 != 0 || dateYear % 400 == 0));
                                    if (dateDay> 29 || (dateDay ==29 && !isleap)) {
                                            validCheck = false;
                                    }
                                }
                            }
                        }
                        if (!validCheck) {
                            startDate.parent().append(window.requiredTitle);
                            startDate.val("");
                            $("body .wkrequired").fadeOut(10000);
                        }
                    });
                });
                $("body .ui-datepicker-trigger").each(function () {
                    if (!$(this).hasClass("wkstyle")) {
                        $(this).hide();
                    }
                });
            }, 1000);
            this._super();
        },
        validatePlan : function (thisthis, event) {
            var currentObj = $(this);
        },
    })
});
