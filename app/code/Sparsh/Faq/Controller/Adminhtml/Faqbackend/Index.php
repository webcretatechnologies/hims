<?php
/**
 * Class Index
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Controller\Adminhtml\Faqbackend;

/**
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Index extends \Sparsh\Faq\Controller\Adminhtml\Faq
{
    /**
     * Faq Model
     *
     * @param \Sparsh\Faq\Model\Faq
     */
    protected $model;

    /**
     * ResultPageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Sparsh\Faq\Model\FaqFactory $model
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Sparsh\Faq\Model\FaqFactory $model,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->model = $model;
        parent::__construct($context, $model);
    }

    /**
     * Index action execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Manage FAQ'));
        $resultPage->setActiveMenu('Magento_Backend::faq_settings');
        return $resultPage;
    }
}
