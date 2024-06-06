#Installation

Magento2 Recurring module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and Recurring(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/Recurring/ folder.

Run Following Command via terminal
-----------------------------------
composer require stripe/stripe-php
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cron:run --group default

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

Magento2 Recurring module's working process follow user guide - https://webkul.com/blog/magento2-recurring-subscription-extension/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/