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
    'Webkul_Recurring/js/reports/Chart.bundle',
    'Webkul_Recurring/js/reports/utils'
], function ($) {
    'use strict';

    $.widget('mage.recurringReport', {
        options: {

        },
        _create: function () {

            var self = this;
            var MONTHS = [];
            var color = Chart.helpers.color;

            var churnRate = {
                labels: self.options.churnRateLabels,
                datasets: self.options.churnRateDatasets
            }
            var lineChartData = {
                labels: self.options.lineCharLabels,
                datasets: self.options.lineCharDatasets
            };
            console.log(self.options.lineCharLabels);
            console.log(self.options.lineCharDatasets);

            var doughnutChartData = {
                labels: self.options.doughnutChartLabels,
                datasets: self.options.doughnutChartDatasets
            };
            $(document).ready(function() {
                var lineChart = document.getElementById('canvas').getContext('2d');
                window.myBar = new Chart(lineChart, {
                    type: 'line',
                    data: lineChartData,
                    options: {
                        responsive: true,
                    }
                });

                var doughnut = document.getElementById('doughnut').getContext('2d');
                window.myBar = new Chart(doughnut, {
                    type: 'doughnut',
                    data: doughnutChartData,
                    options: {
                        responsive: true,
                    }
                });

                var churnChart = document.getElementById('churn-rate').getContext('2d');
                window.myBar = new Chart(churnChart, {
                    type: 'line',
                    data: churnRate,
                    options: {
                        responsive: true,
                    }
                });

            });
        }
    });
    return $.mage.recurringReport;
});