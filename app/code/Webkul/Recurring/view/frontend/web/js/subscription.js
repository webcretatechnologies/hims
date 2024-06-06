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
    "jquery",
    "mage/template",
    "uiComponent",
    "mage/validation",
    "ko",
    "Magento_Ui/js/modal/modal",
    "mage/translate",
    "Magento_Catalog/js/price-utils",
    "Magento_Ui/js/modal/alert",
    "mage/calendar",
], function (
    $,
    mageTemplate,
    Component,
    validation,
    ko,
    modal,
    $t,
    priceUtils,
    alert
) {
    "use strict";

    return Component.extend({        
        selectedPlan: ko.observable("0"),
        initialize: function () {
            let self = this;
            let wkAddToCart = $("#product-addtocart-button");
            let wkUpdateCart = $("#product-updatecart-button");
            
            if(window.subscriptionData.selectedPlan){
                self.selectedPlan(window.subscriptionData.selectedPlan)       ;
                $("#product-subscribe-button").prop("checked", true);
                $(
                    "#product-updatecart-button, .product-social-links, .field.qty"
                ).addClass("display-none");
                self.callAjax(window.subscriptionData.currentProductId); 
                $(".subscription_qty").val(window.subscriptionData.selectedQty)
                $(".wk-start-date").val(window.subscriptionData.startDate)
                $(".subscripption_fee").text(window.subscriptionData.initialFee);
                $(".subscripption_charge").text(window.subscriptionData.charge);
            }   

            let initilFee = priceUtils.formatPrice(
                "00.00",
                window.subscriptionData.currencySymbol
            );

            if (window.subscriptionData.onlyForSubscription == true) {
                $(
                    "#product-addtocart-button, .product-social-links, .field.qty"
                ).addClass("display-none");
                $(".subscribe-form").removeClass("display-none");
                self.callAjax(window.subscriptionData.currentProductId);
            }

            /*
             * this function is used to add data related term.
             */
            $("#product-subscribe-button").click(function () {
                $(
                    "#product-addtocart-button, .product-social-links, .field.qty"
                ).addClass("display-none");
                self.callAjax(window.subscriptionData.currentProductId);
            });

            $("#one-time-purchase").click(function () {
                $(
                    "#product-addtocart-button, #product-updatecart-button, .product-social-links, .field.qty"
                ).removeClass("display-none");
                $(".subscribe-form").addClass("display-none");
            });

            /*
             * this function is used to login page redirection.
             */
            $("#login-to-subscribe").click(function () {
                var dataUrl = $(this).attr("data-url");
                location.href = dataUrl;
            });

            $(".wk-start-date").click(function () {
                $(this).parent().find(".wk-terminput-text").datepicker("show");
            });

            $(".wk-end-date").click(function () {
                $(this).parent().find(".wk-terminput-text").datepicker("show");
            });

            $("body").on("click", ".wkstyle", function () {
                $(this).parent().find(".wk-terminput-text").datepicker("show");
            });

            $("body").on("change", "#product-plan", function () {
                $(".subscripption_fee").text(initilFee);
                $(".subscripption_charge").text(initilFee);
                $(".current-term").val("");
                let selectedPlanId = $(this).val().trim();
                let entityId = $(this).find("option:selected").data('entity_id');
                if (selectedPlanId != 0) {
                    $(".current-term").val(entityId);
                    $.ajax({
                        url: window.subscriptionData.getPlanData,
                        type: "POST",
                        data: {
                            planId: selectedPlanId,
                            productId: window.subscriptionData.currentProductId,
                            productPrice:
                                window.subscriptionData.currentProductPrice,
                        },
                        success: function (response) {
                            if (response.success) {
                                initilFee = priceUtils.formatPrice(
                                    response.initialFee,
                                    window.subscriptionData.currencySymbol
                                );
                                let charge = priceUtils.formatPrice(
                                    response.subscriptionCharge,
                                    window.subscriptionData.currencySymbol
                                );
                                $(".subscripption_fee").text(initilFee);
                                $(".subscripption_charge").text(charge);

                                $("#initial_fee").val(response.initialFee);
                                $("#subscription_charge").val(
                                    response.subscriptionCharge
                                );
                                if (response.enableEndDate) {
                                    $(".wk-end-date").removeAttr("disabled");
                                } else {
                                    $(".wk-end-date").attr('disabled', true);
                                }
                                if (response.trailStatus) {
                                    $("#free_trail_status").val(
                                        response.trailStatus
                                    );
                                    $("#free_trail_days").val(
                                        response.trailDay
                                    );
                                    $(".trails").removeClass("display-none");
                                    $(".trailDays").text(
                                        response.trailDay + " days free"
                                    );
                                    let durationType = response.durationType;
                                    $(".subscriptionCharge").text(
                                        charge + " /" + durationType + " after"
                                    );
                                } else {
                                    $(
                                        "#free_trail_status, #free_trail_days"
                                    ).val("");
                                    $(".trails").addClass("display-none");
                                }
                            } else {
                                if (response.error) {
                                    alert({
                                        content: $t("Something went wrong."),
                                    });
                                }
                            }
                        },
                    });
                }
            });

            /*
             * this function is used to add subscription.
             */
            $("body").on("click", ".subscribe-now", function () {
                if ($("body .wkrequired").length) {
                    $("body .wkrequired").remove();
                }
                var termType = $(document).find(".current-term");
                var startDate = $(document).find(".wk-start-date");
                let endDate = $(document).find(".wk-end-date");
                let selectedplan = $(document).find("#product-plan");
                if (selectedplan.val() == 0) {
                    selectedplan.addClass("error");
                    let text = $t('Please select a plan.');
                    selectedplan
                        .parent()
                        .append("<span class='wkrequired'>"+text+'</span>');
                } else {
                    selectedplan.removeClass("error");
                    $("#term_id").val(selectedplan.val().trim());
                }
                let qty = $(document).find(".subscription_qty");

                if (qty.val() == "" || qty.val() == 0) {
                    qty.addClass("error");
                    let text = $t('Please enter the qty.');
                    qty.parent().append("<span class='wkrequired'>"+text+'</span>');
                } else {
                    qty.removeClass("error");
                }
                
                var dateValidate = 1;
                var myDate = new Date(startDate.val());
                let end_date = new Date(endDate.val());
                myDate.setHours(0, 0, 0, 0);
                end_date.setHours(0, 0, 0, 0);
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                if (myDate < today) {
                    dateValidate = 0;
                }
                if(end_date < today || end_date < myDate){
                    endDate.addClass("error");
                    let text = $t('Please select valid end Date.');
                    endDate
                        .parent()
                        .append("<span class='wkrequired'>"+text+'</span>');
                }else{
                    endDate.removeClass("error");
                    $("#end_date").val(endDate.val());
                }
                
                if (startDate.val() == "" || dateValidate == 0) {
                    startDate.addClass("error");
                    let text = $t('Please select start Date.');
                    startDate
                        .parent()
                        .append("<span class='wkrequired'>"+text+'</span>');
                } else {
                    startDate.removeClass("error");
                    $("#start_date").val(startDate.val());
                }

                if (
                    termType.val() &&
                    startDate.val() &&
                    selectedplan.val() &&
                    dateValidate
                ) {
                    $("#plan_id").val(termType.val());
                    self.disableButton();
                    $("body").trigger("processStart");
                    var dataForm = $("#product_addtocart_form");
                    if (dataForm.validation("isValid")) {
                        wkAddToCart.click();
                        wkUpdateCart.click();
                    }
                    $(document).find("#product-plan").val(0);
                    $(document).find(".wk-start-date").val("");
                    $(document).find(".subscription_qty").val(1);
                    initilFee = priceUtils.formatPrice(
                        "00.00",
                        window.subscriptionData.currencySymbol
                    );
                    $(".subscripption_fee").text(initilFee);
                    $(".subscripption_charge").text(initilFee);
                    $("body").trigger("processStop");
                    self.enableButton();
                }
            });

            setTimeout(function () {
                $("body .wk-start-date").each(function () {
                    $(this)
                        .calendar({
                            dateFormat: "M/d/yy",
                            minDate: 0,
                            changeYear: true,
                            yearRange: "-100:+100",
                        })
                        .on("change", function () {
                            var startDate = $(this);
                            if ($("body .wk-start-date").length) {
                                $("body .wkrequired").remove();
                            }
                            var txtDate = startDate.val();
                            var currVal = txtDate;
                            var validCheck = true;
                            if (currVal == "") {
                                validCheck = false;
                            } else {
                                var regexDatePattern =
                                    /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
                                var dateArray = currVal.match(regexDatePattern);

                                if (dateArray == null) {
                                    validCheck = false;
                                } else {
                                    var dateMonth, dateDay, dateYear;
                                    dateMonth = dateArray[1];
                                    dateDay = dateArray[3];
                                    dateYear = dateArray[5];

                                    if (dateMonth < 1 || dateMonth > 12) {
                                        validCheck = false;
                                    } else if (dateDay < 1 || dateDay > 31) {
                                        validCheck = false;
                                    } else if (
                                        (dateMonth == 4 ||
                                            dateMonth == 6 ||
                                            dateMonth == 9 ||
                                            dateMonth == 11) &&
                                        dateDay == 31
                                    ) {
                                        validCheck = false;
                                    } else if (dateMonth == 2) {
                                        var isleap =
                                            dateYear % 4 == 0 &&
                                            (dateYear % 100 != 0 ||
                                                dateYear % 400 == 0);
                                        if (
                                            dateDay > 29 ||
                                            (dateDay == 29 && !isleap)
                                        ) {
                                            validCheck = false;
                                        }
                                    }
                                }
                            }
                            if (!validCheck) {
                                let text = $t('Please select start Date.');
                                startDate
                                    .parent()
                                    .append(
                                        "<span class='wkrequired'>"+text+'</span>'
                                    );
                                startDate.val("");                              
                            }
                        });
                });
                $("body .ui-datepicker-trigger").each(function () {
                    if (!$(this).hasClass("wkstyle")) {
                        $(this).hide();
                    }
                });
            }, 1000);

            setTimeout(function () {
                $("body .wk-end-date").each(function () {
                    $(this)
                        .calendar({
                            dateFormat: "M/d/yy",
                            minDate: 0,
                            changeYear: true,
                            yearRange: "-100:+100",
                        })
                        .on("change", function () {
                            var endDate = $(this);
                            if ($("body .wk-end-date").length) {
                                $("body .wkrequired").remove();
                            }
                            var txtDate = endDate.val();
                            var currVal = txtDate;
                            var validCheck = true;
                            if (currVal == "") {
                                validCheck = false;
                            } else {
                                var regexDatePattern =
                                    /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
                                var dateArray = currVal.match(regexDatePattern);

                                if (dateArray == null) {
                                    validCheck = false;
                                } else {
                                    var dateMonth, dateDay, dateYear;
                                    dateMonth = dateArray[1];
                                    dateDay = dateArray[3];
                                    dateYear = dateArray[5];

                                    if (dateMonth < 1 || dateMonth > 12) {
                                        validCheck = false;
                                    } else if (dateDay < 1 || dateDay > 31) {
                                        validCheck = false;
                                    } else if (
                                        (dateMonth == 4 ||
                                            dateMonth == 6 ||
                                            dateMonth == 9 ||
                                            dateMonth == 11) &&
                                        dateDay == 31
                                    ) {
                                        validCheck = false;
                                    } else if (dateMonth == 2) {
                                        var isleap =
                                            dateYear % 4 == 0 &&
                                            (dateYear % 100 != 0 ||
                                                dateYear % 400 == 0);
                                        if (
                                            dateDay > 29 ||
                                            (dateDay == 29 && !isleap)
                                        ) {
                                            validCheck = false;
                                        }
                                    }
                                }
                            }
                            if (!validCheck) {
                                let text = $t('Please select end Date.');
                                endDate
                                    .parent()
                                    .append(
                                        "<span class='wkrequired'>"+text+'</span>'
                                    );
                                endDate.val("");                                
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
        callAjax: function (productId) { 
            let self = this;           
            $.ajax({
                url: window.subscriptionData.getPlan,
                type: "POST",
                data: {
                    productId: productId,
                },
                success: function (response) {
                    if (response.success) {
                        let select = document.getElementById("product-plan");
                        select.innerHTML = "";
                        $("#product-plan").append(
                            $("<option></option>").val("0").text("Select Plan")
                        );

                        $(".subscribe-form")
                            .removeClass("display-none")
                            .addClass("active");
                        if (response.planName.length) {
                            $.each(response.planName, function (i, v) {
                                $.each(v, function (key, value) {
                                    let selected = '';
                                    if(self.selectedPlan() == key){
                                        selected = 'selected';
                                    }
                                    let id = key.split(".");
                                    $("#product-plan").append(
                                        $("<option"+' '+selected+"></option>")
                                            .val(id[0])
                                            .text(value)
                                            .attr('data-entity_id',id[1])
                                    );
                                });
                            });
                        }
                    } else {
                        if (response.error) {
                            alert({
                                content: $t("Something went wrong."),
                            });
                        }
                    }
                },
            });
        },

        disableButton: function (form) {
            let addToCartButtonTextWhileAdding = $t("Adding..."),
                SubscribeCartButton = $(document).find(".subscribe-now");

            SubscribeCartButton.addClass("disabled");
            SubscribeCartButton.find("span").text(
                addToCartButtonTextWhileAdding
            );
            SubscribeCartButton.attr("title", addToCartButtonTextWhileAdding);
        },

        enableButton: function (form) {
            var addToCartButtonTextAdded = $t("Subscribe Now"),
                SubscribeCartButton = $(document).find(".subscribe-now");

            setTimeout(function () {
                SubscribeCartButton.removeClass("disabled");
                SubscribeCartButton.find("span").text(addToCartButtonTextAdded);
                SubscribeCartButton.attr("title", addToCartButtonTextAdded);
            }, 1000);
        },
    });
});
