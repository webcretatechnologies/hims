<?php
/**
 * Class Faqcategory
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Controller\Index;

use Sparsh\Faq\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Faqcategory
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Faqcategory extends \Magento\Framework\App\Action\Action
{
    /**
     * PageFactory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * ResultJsonFactory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * HelperData
     *
     * @var Data
     */
    protected $helperData;

    /**
     * Faq constructor.
     *
     * @param Context     $context           context
     * @param PageFactory $resultPageFactory resultPageFactory
     * @param JsonFactory $resultJsonFactory resultJsonFactory
     * @param Data        $helperData        helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Data $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        return parent::__construct($context);
    }

    /**
     * Faq action execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create(ResultFactory::TYPE_PAGE);
        $result = $this->resultJsonFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock('Sparsh\Faq\Block\Widget\Faq')
            ->setTemplate('Sparsh_Faq::widget/faqcategory.phtml')
            ->toHtml();

        $result->setData(['output' => $block]);
        return $result;
    }
}
