<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Plugin;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Exception\LocalizedException;

class TotalsCollector
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    /**
     * @var ResponseHttp
     */
    private $response;
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $itemModel;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;
    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param responseHttp $response
     * @param \Magento\Quote\Model\Quote\Item $itemModel
     * @param \Webkul\Recurring\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResponseHttp $response,
        \Magento\Quote\Model\Quote\Item $itemModel,
        \Webkul\Recurring\Logger\Logger $logger
    ) {
        $this->messageManager   = $messageManager;
        $this->itemModel        = $itemModel;
        $this->jsonHelper       = $jsonHelper;
        $this->response         = $response;
        $this->logger           = $logger;
    }

    /**
     * Plugin executes before collect rates
     *
     * @param \Magento\Quote\Model\Quote\TotalsCollector $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeCollect(
        \Magento\Quote\Model\Quote\TotalsCollector $subject,
        \Magento\Quote\Model\Quote $quote
    ) {
        $cartData = $quote->getAllVisibleItems();
        $startDate = '';
        $proUrl = '';
        $itemId = 0;
        try {
            foreach ($cartData as $item) {
                $proUrl = $item->getProduct()->getProductUrl();
                if ($customAdditionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                    $allOptions = $this->jsonHelper->jsonDecode(
                        $customAdditionalOptionsQuote->getValue()
                    );
                    foreach ($allOptions as $option) {
                        $itemId = $item->getItemId();
                        if ($option['label'] == 'Start Date') {
                            $startDate = $option['value'];
                        }
                    }
                }
            }
            if ($startDate != "") {
                if (!$this->isValid($startDate)) {
                    if ($itemId) {
                        $this->itemModel->load($itemId);
                    }
                    throw new LocalizedException(
                        __('Start Date is Invalid for the Subscription. Please Add Again')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('beforeCollect'.$e->getMessage());
            $this->messageManager->addError(__('Something went wrong.'));
            $this->response->setRedirect($proUrl);
        }
        return [$quote];
    }

    /**
     * Check the date is invalid or not
     *
     * @param string $date
     * @return bool
     */
    private function isValid($date)
    {
        return (strtotime($date) >= strtotime('today'));
    }
}
