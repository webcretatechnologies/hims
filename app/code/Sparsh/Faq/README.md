##FAQ Extension
FAQ extension for Magento 2 assists customers to find the common inquiries on the knowledge base and faq lists. The module allows the admin to add questions and answers and list them on the front end with Tabs / Categories.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the file
3. Create a folder [Magento_Root]/app/code/Sparsh/Faq
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/Faq'

#Enable Extension:
- php bin/magento module:enable Sparsh_Faq
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_Faq
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush