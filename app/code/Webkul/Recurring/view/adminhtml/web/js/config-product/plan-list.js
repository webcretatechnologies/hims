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
    "Webkul_Recurring/js/plan-list",
    "mageUtils",
    "Magento_Ui/js/modal/modal",
    "mage/translate",
    "ko",
], function ($, Component, utils, modal, $t, ko) {
    "use strict";

    return Component.extend({
        allPlanList: ko.observableArray([]),
        initialFee: ko.observableArray([]),
        selectedPlans: ko.observableArray(),
        storeId: ko.observable("0"),
        rowIndex: ko.observable(),
        rowSku: ko.observable(),
        eachRowPlanList: {},
        subscriptionCharge: ko.observable("0"),

        openSubscriptionModal: function (rowIndex) {
            let self = this;
            this.dataScope = "data";
            let productSource = this.source.get(
                this.dataScope + "." + this.index + "." + rowIndex
            );
            if (productSource != "undefined") {
                self.rowSku(productSource["sku"]);
                let product = {
                    id: productSource.id,
                    attributes: productSource["configurable_attribute"],
                };
            }

            self.rowIndex(rowIndex);
            self.allPlanList([]);
            if (
                typeof window.plansData != "undefined" &&
                window.plansData.length
            ) {
                $.each(window.plansData, function (i, v) {
                    v["checked"] = "";
                    if (Object.keys(self.eachRowPlanList).length > 0) {
                        let wholeData = self.eachRowPlanList;
                        Object.keys(wholeData).forEach(function (key, value) {
                            if (key == self.rowSku()) {
                                Object.keys(wholeData[key]).forEach(function (
                                    e,
                                    o
                                ) {
                                    if (v.durationTitle == e) {
                                        v["checked"] =
                                            wholeData[key][e].checked;
                                        v.discount_type =
                                            wholeData[key][e].discount_type;
                                        v.initial_fee =
                                            wholeData[key][e].plan_initial_fee;
                                        v.subscription_charge =
                                            wholeData[key][
                                                e
                                            ].plan_subscription_charge;
                                    }
                                });
                            } else {
                                if (!(self.rowSku() in wholeData)) {
                                    v["checked"] = "";
                                    v.discount_type = "";
                                    v.initial_fee = "";
                                    v.subscription_charge = "";
                                }
                            }
                        });
                    }
                    self.allPlanList.push(v);
                });
            }

            self.eachRowPlanList[self.rowSku()] = {};
            let options = {
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
                            var bodyArray = $(".wk-pricebody");

                            bodyArray.each(function (key, value) {
                                var isChecked = false;
                                var eleCheckBox = $(value).find(".wkcheckbox");
                                if (eleCheckBox.is(":checked")) {
                                    let entity_id = eleCheckBox.val();
                                    let planName = self.checkValue(
                                        value,
                                        "plan_name"
                                    );
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ] = {};

                                    let discountType = self.checkValue(
                                        value,
                                        "discount_type"
                                    );
                                    let initialFee = self.checkValue(
                                        value,
                                        "plan_initial_fee"
                                    );
                                    let plan_id = self.checkValue(
                                        value,
                                        "plan_id"
                                    );

                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["id"] = entity_id;
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["plan_id"] = plan_id;
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["name"] = planName;
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["discount_type"] = discountType;
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["initial_fee"] = initialFee;
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["subscription_charge"] =
                                        self.subscriptionCharge();
                                    self.eachRowPlanList[self.rowSku()][
                                        planName
                                    ]["checked"] = true;
                                }
                            });

                            self.selectedPlans(
                                JSON.stringify(self.eachRowPlanList)
                            );

                            if ($(".shouldFill").length == 0) {
                                modal.closeModal();
                            }
                        },
                    },
                ],
            };

            var popup = modal(options, $("#wk-config-planlist" + rowIndex));
            $("#wk-config-planlist" + rowIndex).modal("openModal");
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
                            .css("border", "grey 1px solid");
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
    });
});
