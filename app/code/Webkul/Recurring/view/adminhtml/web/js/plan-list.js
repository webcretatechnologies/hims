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
    "uiRegistry",
], function (
    $,
    mageTemplate,
    Component,
    validation,
    ko,
    modal,
    $t,
    uiRegistry
) {
    "use strict";

    return Component.extend({
        defaults: {
            template: "Webkul_Recurring/plan-list",
            chlidProductsMatrix: [],
            productSku: 0,
            subscriptionCharge: 0,
            selectedPlans: [],
            configProductPlanList: [],
        },
        initObservable: function () {
            this._super().observe(
                "chlidProductsMatrix productSku subscriptionCharge selectedPlans configProductPlanList"
            );

            return this;
        },

        allPlanList: ko.observableArray([]),
        storeId: ko.observable("0"),
        isConfigurableProduct: ko.observable(false),
        notConfigurableProduct: ko.observable(false),
        eachRowPlanList: {},

        initialize: function () {
            var self = this;
            if (parseInt(window.planData.storeId) == 0) {
                self.storeId(true);
            } else {
                self.storeId(false);
            }
            var buttonId = "#planButton";
            var checkBox = ".wkcheckbox";

            var modelOptions = {
                type: "slide",
                responsive: true,
                innerScroll: true,
                title: $t("Subscriptions Types"),
                buttons: [
                    {
                        text: $t("Continue"),
                        class: "",
                        click: function () {
                            var modal = this;
                            var planInfo = $("#planid");
                            var feeArray = $(".plan_initial_fee");
                            var bodyArray = $(".wk-pricebody");
                            var dataString = "";

                            bodyArray.each(function (key, value) {
                                var eleCheckBox = $(value).find(checkBox);
                                if (eleCheckBox.is(":checked")) {
                                    self.checkValue(value, "plan_name");
                                    self.checkValue(value, "discount_type");
                                    self.checkValue(value, "plan_initial_fee");
                                }
                            });
                            if ($(".shouldFill").length == 0) {
                                modal.closeModal();
                            }
                        },
                    },
                ],
            };

            /**
             *  this block will select and deselect all rows.
             */
            $("body").on("click", ".wkmasscheck", function () {
                if ($(this).is(":checked")) {
                    $.each($(checkBox), function () {
                        if (!$(this).is(":checked")) {
                            $(this).click();
                        }
                    });
                } else {
                    $.each($(checkBox), function () {
                        $(this).click();
                    });
                }
            });
            $("body").on("change", ".plan_subscription_charge", function () {
                var charge = this.value;
                var itemPrice = $('[name="product[price]"]').val();
                var id = $(this).attr('plan_id');
                var type = $('[name="plans['+id +'][discount_type]"]').val().trim();
                if (charge >= itemPrice-1 && type == 2) {
                    this.value = itemPrice - 1;
                }
            });
            $("body").on("change", ".discount_type", function () {
                let parentEle = $(this).parent().parent().parent();
                let discountType = $(this).val().trim();
                let charge = parentEle.find(".plan_subscription_charge");
                var itemPrice = $('[name="product[price]"]').val();
                if (discountType == '2' && charge.val() >= itemPrice-1) {
                    charge.val(itemPrice - 1);
                }
                if (discountType == "1" || discountType == "") {
                    parentEle
                        .find(".plan_subscription_charge")
                        .attr("disabled", "disabled");
                } else {
                    parentEle
                        .find(".plan_subscription_charge")
                        .removeAttr("disabled");
                }
            });
            /**
             *  this block validates configuration values.
             */
            $("body").on("click", checkBox, function () {
                var parentEle = $(this).parent().parent().parent();
                if ($(this).is(":checked")) {
                    if (parentEle.find(".plan_initial_fee").length) {
                        parentEle
                            .find(".plan_initial_fee")
                            .removeAttr("disabled");
                    }
                    parentEle.find(".plan_engine").removeAttr("disabled");
                } else {
                    parentEle
                        .find(".wkrmcolor")
                        .css("border", "grey 1px solid");
                    parentEle.find(".wkrmcolor").removeClass("shouldFill");
                    if (parentEle.find(".plan_initial_fee").length) {
                        parentEle
                            .find(".plan_initial_fee")
                            .attr("disabled", "disabled");
                    }
                    parentEle.find(".plan_engine").attr("disabled", "disabled");
                    parentEle
                        .find(".plan_subscription_charge")
                        .attr("disabled", "disabled");
                }
            });

            /**
             *  this function validates integer values
             */
            $("body").on("keyup", ".plan_initial_fee", function () {
                self.validateValues($(this));
            });

            /**
             *  this function validates integer values
             */
            $("body").on("keyup", ".plan_subscription_charge", function () {
                self.validateValues($(this));
            });

            $("body").on(
                "click",
                "[data-index=subscription-configuration]",
                function () {
                    if (
                        $(this)
                            .find('select[name="product[subscription]')
                            .val() == 1
                    ) {
                        $(buttonId).show();
                    }
                    if (!$(this).find(buttonId).length) {
                        $(this)
                            .find('select[name="product[subscription]')
                            .parent()
                            .after($(buttonId));
                    }
                }
            );

            /**
             *  this block fill in the subscripiton grid values.
             */
            if (
                typeof window.planData.allPlansData != "undefined" &&
                window.planData.allPlansData.length
            ) {
                $.each(window.planData.allPlansData, function (i, v) {
                    self.allPlanList.push(v);
                });
            }
            this._super();

            /**
             *  this function is responsible for toogling the subscriptions configurations options
             */
            $("body").on(
                "change",
                'select[name="product[subscription]"]',
                function () {
                    if ($(this).val() == 1) {
                        $(buttonId).show();
                    } else {
                        $(buttonId).hide();
                    }
                }
            );

            /**
             *  this function is responsible for opening the subscription modal
             */
            $("body").on("click", buttonId, function () {
                self.chlidProductsMatrix([]);
                let stepsWizard = uiRegistry.get(
                    "variation-steps-wizard_step4"
                );
                if (typeof stepsWizard != "undefined") {
                    self.isConfigurableProduct(true);
                    self.notConfigurableProduct(false);
                    uiRegistry.get(
                        "variation-steps-wizard_step4",
                        function (bulkOptions) {
                            let variation = bulkOptions.variations;
                            self.getProductMatrix(variation);
                        }
                    );
                } else if (
                    typeof window.planData.productMatrix != "undefined" &&
                    window.planData.productMatrix.length
                ) {
                    self.isConfigurableProduct(true);
                    self.notConfigurableProduct(false);
                    let productMatrix = window.planData.productMatrix;
                    self.getProductMatrix(productMatrix);
                } else {
                    self.notConfigurableProduct(true);
                    self.isConfigurableProduct(false);
                    var popup = modal(modelOptions, $("#wkplanlist"));
                    $("#wkplanlist").modal("openModal");
                }
            });
        },

        getProductMatrix: function (variation) {
            let self = this;
            let chosenOptions = [];
            variation.forEach((chosenData) => {
                chosenOptions.push({
                    options: chosenData.options,
                    productId: chosenData.productId,
                    image: chosenData.images.preview,
                    sku: chosenData.sku,
                    price: chosenData.price,
                    status: chosenData.status,
                    name: chosenData.name,
                });
            });
            let savedConfiPlans = window.planData.savedConfigPlan;
            if (Object.keys(savedConfiPlans).length > 0) {
                $.each(chosenOptions, function (k, v) {
                    let i = 0;
                    $.each(savedConfiPlans, function (inx, val) {
                        if (
                            typeof savedConfiPlans[inx][i] != "undefined" &&
                            inx == chosenOptions[k].sku
                        ) {
                            chosenOptions[k]["subscriptionInfo"] = [];
                            chosenOptions[k]["subscriptionInfo"].push({
                                initialFee: savedConfiPlans[inx][i].initial_fee,
                                plan: savedConfiPlans[inx][i].durationTitle,
                                charge: savedConfiPlans[inx][i]
                                    .subscription_charge,
                            });
                        }
                    });
                    i++;
                });
            }

            self.chlidProductsMatrix(chosenOptions);
            let options = {
                type: "slide",
                responsive: true,
                innerScroll: true,
                title: $t("Configurable product"),
                buttons: [
                    {
                        class: "display-none",
                    },
                ],
            };
            $(document).ready(function () {
                let popup = modal(options, $("#wkplanlist"));
                $("#wkplanlist").modal("openModal");
            });
        },

        openPlanModel: function (data, e) {
            let self = this;

            self.productSku(data.sku);
            self.fillPlanDataInFields(data.sku);
            let modelOptions = {
                type: "slide",
                responsive: true,
                innerScroll: true,
                title: $t("Subscriptions Types"),
                buttons: [
                    {
                        text: $t("Continue"),
                        class: "wk-continue-" + data.sku,
                        click: function () {
                            var modal = this;
                            var bodyArray = $(".wk-config-product-pricebody");
                            self.eachRowPlanList[data.sku] = {};
                            bodyArray.each(function (key, value) {
                                var eleCheckBox =
                                    $(value).find(".wkConfigCheckbox");
                                if (eleCheckBox.is(":checked")) {
                                    let entity_id = eleCheckBox.val();
                                    let planName = self.checkValue(
                                        value,
                                        "plan_name"
                                    );
                                    let plan_id = self.checkValue(
                                        value,
                                        "plan_id"
                                    );
                                    self.eachRowPlanList[data.sku][plan_id] =
                                        {};
                                    let discountType = self.checkValue(
                                        value,
                                        "discount_type"
                                    );
                                    let initialFee = self.checkValue(
                                        value,
                                        "plan_initial_fee"
                                    );

                                    self.eachRowPlanList[data.sku][plan_id][
                                        "id"
                                    ] = entity_id;
                                    self.eachRowPlanList[data.sku][plan_id][
                                        "plan_id"
                                    ] = plan_id;
                                    self.eachRowPlanList[data.sku][plan_id][
                                        "name"
                                    ] = planName;
                                    self.eachRowPlanList[data.sku][plan_id][
                                        "discount_type"
                                    ] = discountType;
                                    self.eachRowPlanList[data.sku][plan_id][
                                        "initial_fee"
                                    ] = initialFee;
                                    self.eachRowPlanList[data.sku][plan_id][
                                        "subscription_charge"
                                    ] = self.subscriptionCharge();
                                    self.eachRowPlanList[data.sku][plan_id][
                                        "checked"
                                    ] = true;
                                }
                                self.selectedPlans(
                                    JSON.stringify(self.eachRowPlanList)
                                );
                            });

                            if ($(".shouldFill").length == 0) {
                                modal.closeModal();
                            }
                        },
                    },
                ],
            };
            var popup = modal(modelOptions, $("#wk-config-planlist"));
            $("#wk-config-planlist").modal("openModal");

            var bodyArray = $(".wk-config-product-pricebody");
            bodyArray.each(function (key, value) {
                var eleCheckBox = $(value).find(".wkConfigCheckbox");
                if (eleCheckBox.is(":checked")) {
                    $(value).find(".plan_initial_fee").removeAttr("disabled");
                    let discount_type = $(value).find(".discount_type").val();
                    if (discount_type != "1" && discount_type != "") {
                        $(value)
                            .find(".plan_subscription_charge")
                            .removeAttr("disabled");
                    }
                }
            });
        },

        fillPlanDataInFields: function (sku) {
            let self = this;
            self.configProductPlanList([]);
            if (
                typeof window.planData.allPlanList != "undefined" &&
                window.planData.allPlanList.length
            ) {
                let savedConfiPlans = window.planData.savedConfigPlan;
                $.each(window.planData.allPlanList, function (i, v) {
                    v["checked"] = "";
                    if (Object.keys(savedConfiPlans).length > 0) {
                        $.each(savedConfiPlans, function (inx, val) {
                            if (
                                typeof savedConfiPlans[inx][i] != "undefined" &&
                                sku == inx
                            ) {
                                if (
                                    v.durationId ==
                                    savedConfiPlans[inx][i].durationId
                                ) {
                                    v["checked"] = true;
                                    v.initial_fee =
                                        savedConfiPlans[inx][i].initial_fee;
                                    v.discount_type =
                                        savedConfiPlans[inx][i].discount_type;
                                    v.subscription_charge =
                                        savedConfiPlans[inx][
                                            i
                                        ].subscription_charge;
                                } else {
                                    v["checked"] = "";
                                    v.discount_type = "";
                                    v.initial_fee = "";
                                    v.subscription_charge = "";
                                }
                            }
                        });
                    }

                    if (Object.keys(self.eachRowPlanList).length > 0) {
                        let wholeData = self.eachRowPlanList;
                        Object.keys(wholeData).forEach(function (key, value) {
                            if (key == sku) {
                                Object.keys(wholeData[key]).forEach(function (
                                    e,
                                    o
                                ) {
                                    if (v.durationId == e) {
                                        v["checked"] =
                                            wholeData[key][e].checked;
                                        v.initial_fee =
                                            wholeData[key][e].initial_fee;
                                        v.discount_type =
                                            wholeData[key][e].discount_type;
                                        v.subscription_charge =
                                            wholeData[key][
                                                e
                                            ].subscription_charge;
                                    }
                                });
                            } else {
                                if (!(sku in wholeData)) {
                                    v["checked"] = "";
                                    v.discount_type = "";
                                    v.initial_fee = "";
                                    v.subscription_charge = "";
                                }
                            }
                        });
                    }
                    self.configProductPlanList.push(v);
                });
            }
        },

        checkValue: function (element, wkclass) {
            let self = this;
            if (
                $(element)
                    .find("." + wkclass)
                    .val()
            ) {
                $(element)
                    .find("." + wkclass)
                    .removeClass("shouldFill");
                $(element)
                    .find("." + wkclass)
                    .css("border", "grey 1px solid");
                if (wkclass == "discount_type") {
                    let discountType = $(element).find(".discount_type").val();
                    if (discountType == "" || discountType == "1") {
                        $(element)
                            .find(".plan_subscription_charge")
                            .removeClass("shouldFill");
                        $(element)
                            .find(".plan_subscription_charge")
                            .css("border", "grey 1p solid");
                        self.subscriptionCharge(0);
                    } else {
                        let rate = $(element)
                            .find(".plan_subscription_charge")
                            .val();
                        if (rate == "") {
                            $(element)
                                .find(".plan_subscription_charge")
                                .css("border", "1px solid #FF0B17");
                            $(element)
                                .find(".plan_subscription_charge")
                                .addClass("shouldFill");
                            return "0";
                        } else {
                            $(element)
                                .find(".plan_subscription_charge")
                                .removeClass("shouldFill");
                            $(element)
                                .find(".plan_subscription_charge")
                                .css("border", "grey 1px solid");
                            self.subscriptionCharge(rate);
                        }
                    }
                }
                return $(element)
                    .find("." + wkclass)
                    .val();
            } else {
                $(element)
                    .find("." + wkclass)
                    .css("border", "1px solid #FF0B17");
                $(element)
                    .find("." + wkclass)
                    .addClass("shouldFill");
                return "0";
            }
        },
        validateValues: function (element) {
            if (element.val()) {
                if (!$.isNumeric(element.val())) {
                    element.val("");
                }
            }
        },
        canVisibleth: function (element) {
            var storeId = self.storeId();
            if (parseInt(storeId) == 0) {
                return true;
            }
            return false;
        },
    });
});
