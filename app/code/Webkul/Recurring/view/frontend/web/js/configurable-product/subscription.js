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
                
        selectedConfigProduct: [],

        initialize: function () {
            let self = this;

            var wkAddToCart = $("#product-addtocart-button");
            var wkUpdateCart = $("#product-updatecart-button");
            let initialFee = priceUtils.formatPrice(
                "00.00",
                window.currencySymbol
            );

            $("#product-options-wrapper").click(function () {
                $(
                    ".error-msg, #config_subscription_form, .subscribe-form"
                ).addClass("display-none");
                self.selectedChildProduct();
            
                if (window.subscriptionData.onlyForSubscription == true) {
                    $("#only-subscribe").trigger("click");
                }

            });

            if (window.subscriptionData.onlyForSubscription == true) {
                $(
                    "#product-addtocart-button, .field.qty, .product-social-links"
                ).addClass("display-none");
                $(".subscribe-form").removeClass("display-none");
            }

            $("#only-subscribe").on("click", function () {
                self.callAjax(self.selectedConfigProduct);
            });

            /*
             * this function is used to add data related term.
             */
            $("body").on("click", "#product-subscribe-button", function () {
                $(
                    "#product-addtocart-button, .field.qty, .product-social-links"
                ).addClass("display-none");

                if (window.subscriptionData.isCustomerloggedIn == true) {
                    if (Object.keys(self.selectedConfigProduct).length > 0) {
                        $(".error-msg").addClass("display-none");
                        self.callAjax(self.selectedConfigProduct);
                    } else if (window.subscriptionData.productType == 'downloadable') {
                        $(".error-msg").addClass("display-none");
                        self.callAjax(window.subscriptionData.currentProductId);
                    } else {
                        $(".error-msg").text(
                            $t("You need to choose options for your item")
                        );
                        $(".error-msg, #config_subscription_form").removeClass(
                            "display-none"
                        );
                    }
                } else {
                    $(".subscribe-form").removeClass("display-none");
                }
            });

            $("body").on("click", "#one-time-purchase", function () {
                $(
                    "#product-addtocart-button, .product-social-links, .field.qty"
                ).removeClass("display-none");
                $(
                    ".error-msg, #config_subscription_form, .subscribe-form"
                ).addClass("display-none");
            });
            /*
             * this function is used to login page redirection.
             */
            $("body").on("click", "#login-to-subscribe", function () {
                var dataUrl = $(this).attr("data-url");
                location.href = dataUrl;
            });

            $("body").on("click", ".wk-start-date", function () {
                $(this).parent().find(".wk-terminput-text").datepicker("show");
            });

            $("body").on("click", ".wk-end-date", function () {
                $(this).parent().find(".wk-terminput-text").datepicker("show");
            });

            $("body").on("click", ".wkstyle", function () {
                $(this).parent().find(".wk-terminput-text").datepicker("show");
            });

            $("body").on("change", "#product-plan", function () {
                $(".subscripption_fee").text(initialFee);
                $(".subscripption_charge").text(initialFee);
                $(".current-term").val("");
                let selectedPlanId = $(this).val().trim();
                let entityId = $(this).find("option:selected").data('entity_id');
                let prodId = self.selectedConfigProduct;
                let prodPrice = "";
                if (window.subscriptionData.productType == 'downloadable') {
                    prodId = window.subscriptionData.currentProductId;
                    prodPrice = window.subscriptionData.currentProductPrice;
                }
                if (selectedPlanId != 0) {
                    $(".current-term").val(entityId);
                    $.ajax({
                        url: window.subscriptionData.getPlanData,
                        type: "POST",
                        data: {
                            planId: selectedPlanId,
                            productId: prodId,
                            productPrice: prodPrice,
                        },
                        success: function (response) {
                            if (response.success) {
                                initialFee = priceUtils.formatPrice(
                                    response.initialFee,
                                    window.subscriptionData.currencySymbol
                                );
                                let charge = priceUtils.formatPrice(
                                    response.subscriptionCharge,
                                    window.subscriptionData.currencySymbol
                                );
                                $(".subscripption_fee").text(initialFee);
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
                                    $(".trails").removeClass("display-none");
                                    $(".trailDays").text(
                                        response.trailDay + " days free"
                                    );
                                    let durationType = response.durationType;
                                    $(".subscriptionCharge").text(
                                        charge + " /" + durationType + " after"
                                    );
                                }else {
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
                let selectedplan = $(document).find("#product-plan");
                if (selectedplan.val() == 0) {
                    selectedplan.css("animation", "shake 0.5s");
                    selectedplan.css("border-color", "palevioletred");
                    selectedplan
                        .parent()
                        .append(window.subscriptionData.planRequired);
                } else {
                    selectedplan.css("border-color", "lightgrey");
                    $("#term_id").val(selectedplan.val().trim());
                }
                let qty = $(document).find(".subscription_qty");

                if (qty.val() == "" || qty.val() == 0) {
                    qty.css("animation", "shake 0.5s");
                    qty.css("border-color", "palevioletred");
                    qty.parent().append(window.subscriptionData.qtyRequired);
                } else {
                    qty.css("border-color", "lightgrey");
                }
                startDate.css("border-color", "lightgrey");
                var dateValidate = 1;
                var myDate = new Date(startDate.val());
                myDate.setHours(0, 0, 0, 0);
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                if (myDate < today) {
                    dateValidate = 0;
                }
                if (startDate.val() == "" || dateValidate == 0) {
                    startDate.css("animation", "shake 0.5s");
                    startDate.css("border-color", "palevioletred");
                    startDate
                        .parent()
                        .append(window.subscriptionData.requiredTitle);
                } else {
                    startDate.css("border-color", "lightgrey");
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
                    initialFee = priceUtils.formatPrice(
                        "00.00",
                        window.subscriptionData.currencySymbol
                    );
                    $(".subscripption_fee").text(initialFee);
                    $(".subscripption_charge").text(initialFee);
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
                                startDate
                                    .parent()
                                    .append(
                                        window.subscriptionData.requiredTitle
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
                                endDate
                                    .parent()
                                    .append(
                                        window.subscriptionData.requiredTitle
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

        selectedChildProduct: function () {
            let self = this;
            self.selectedConfigProduct = [];
            var selected_options = {};
            $("div.swatch-attribute").each(function (k, v) {
                var attribute_id = $(v).attr("data-attribute-id");
                var option_selected = $(v).attr("data-option-selected");
                if (!attribute_id || !option_selected) {
                    return;
                }
                selected_options[attribute_id] = option_selected;
            });

            let product_id_index = $("[data-role=swatch-options]").data(
                "mage-SwatchRenderer"
            );
            if(typeof(product_id_index) != 'undefined'){
                product_id_index =  product_id_index.options.jsonConfig.index;
                $.each(product_id_index, function (product_id, attributes) {
                    var productIsSelected = function (
                        attributes,
                        selected_options
                    ) {
                        return _.isEqual(attributes, selected_options);
                    };
                    if (productIsSelected(attributes, selected_options)) {
                        self.selectedConfigProduct.push(product_id);
                    }
                });  
                if (Object.keys(self.selectedConfigProduct).length > 0) {
                    if ($("#product-subscribe-button").is(":checked")) {
                        $("#product-subscribe-button").trigger("click");
                    }
                }     
            }else{
                let data = [];
                let $el=$(".product-options-wrapper select[id^='attribute']");
                let confProductId = window.subscriptionData.currentProductId;
                $el.each(function(){
                    data.push({selectedValue:$(this).val(),selectedAttributeId:$(this).attr('id').replace('attribute', '')});
                });
                $.ajax({
                    url: window.subscriptionData.getAssociatedProduct,
                    type: "POST",
                    data: { confProductId:confProductId,params: data },
                    showLoader: false,
                    cache: false,
                    success: function(response){
                        if(response.success){
                            self.selectedConfigProduct.push(response.associateProductId);
                        }    
                        if (Object.keys(self.selectedConfigProduct).length > 0) {
                            if ($("#product-subscribe-button").is(":checked")) {
                                $("#product-subscribe-button").trigger("click");
                            }
                        } 
                                    
                    }
                });
            }  
            
        },

        callAjax: function (productId) {
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
                                    let id = key.split(".");
                                    $("#product-plan").append(
                                        $("<option></option>")
                                            .val(id[0])
                                            .text(value)
                                            .attr('data-entity_id',id[1])
                                    );
                                });
                            });
                        } else {
                            $(".subscribe-form").addClass("display-none");
                            $(".error-msg").text(
                                $t(
                                    'Subscription is not available for the selected option, please choose "One time purchase".'
                                )
                            );
                            $(
                                ".error-msg, #config_subscription_form"
                            ).removeClass("display-none");
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
