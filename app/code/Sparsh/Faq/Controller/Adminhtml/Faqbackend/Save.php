<?php
/**
 * Class Save
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

use Sparsh\Faq\Model\FaqFactory;
use Sparsh\Faq\Model\ResourceModel\Faq as FaqResource;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Save extends \Sparsh\Faq\Controller\Adminhtml\Faq
{
    /**
     * Admin Resource
     *
     * @param string
     */
    const ADMIN_RESOURCE = 'Sparsh_Faq::sparsh_faq';

    /**
     * Data Processor
     *
     * @var \Sparsh\Faq\Controller\Adminhtml\Faqbackend\PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * Data Persostor
     *
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Faq Model
     *
     * @var \Sparsh\Faq\Model\FaqFactory
     */
    protected $model;

    /**
     * Faq Resource Model
     *
     * @var \Sparsh\Faq\Model\ResourceModel\Faq
     */
    protected $faqResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param FaqFactory $model
     * @param FaqResource $faqResource
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PostDataProcessor $dataProcessor,
        FaqFactory $model,
        FaqResource $faqResource,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->model = $model;
        $this->faqResource = $faqResource;
        $this->date = $date;
        parent::__construct($context, $model);
    }

    /**
     * Save action execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (!$this->dataProcessor->validate($data)) {
                $this->dataPersistor->set('faqdata', $data);
                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'id' => $this->model->getId(),
                        '_current' => true
                    ]
                );
            }
            
            try {
                $data['store_id'] = json_encode($data['store_id']);
                $data['update_time'] =  $this->date->gmtDate();

                $faqModel = $this->model->create()->setData($data);
                $this->faqResource->save($faqModel);

                $this->messageManager->addSuccessMessage(
                    __('FAQ is saved successfully.')
                );
                $this->dataPersistor->clear('faqdata');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'id' => $faqModel->getId(),
                            '_current' => true
                        ]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Faq.')
                );
            }

            $this->dataPersistor->set('faqdata', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id' => $faqModel->getId()
                ]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
