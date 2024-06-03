<?php
/**
 * Class PostDataProcessor
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Controller\Adminhtml\FaqCategoryBackend;

use Sparsh\Faq\Model\FaqCategoryFactory;
use Sparsh\Faq\Model\ResourceModel\FaqCategory\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;

/**
 * Class PostDataProcessor
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class PostDataProcessor
{
    /**
     * Date Filter
     *
     * @var Date
     */
    protected $dateFilter;

    /**
     * Validation Factory
     *
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * Message Manager
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * FaqCategoryModel
     *
     * @var FaqCategoryFactory
     */
    protected $faqCategoryFactory;

    /**
     * PostDataProcessor Class constructor
     *
     * @param Date               $dateFilter         DateFiletr
     * @param ManagerInterface   $messageManager     MessageManager
     * @param ValidatorFactory   $validatorFactory   ValidationFactory
     * @param FaqCategoryFactory $faqCategoryFactory faqCategoryFactory
     */
    public function __construct(
        Date $dateFilter,
        ManagerInterface $messageManager,
        ValidatorFactory $validatorFactory,
        CollectionFactory $faqCategoryFactory
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
        $this->faqCategoryFactory = $faqCategoryFactory;
    }

    /**
     * Validate post data
     *
     * @param array $data Datapost
     *
     * @return bool
     */
    public function validate($data)
    {
        $errorNo1 = $this->validateRequireEntry($data);
        $errorNo2 = $this->checkNameExist($data);
        $errorNo3 = true;

        if (!in_array($data['is_active'], [0,1]) || $data['is_active'] == '' || $data['is_active'] === null) {
            $errorNo3 = false;
            $this->messageManager->addErrorMessage(
                __("Please enter valid status.")
            );
        }

        return $errorNo1 && $errorNo2 && $errorNo3;
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data RequireFields
     *
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'faq_category_name' => __('Category Name')
        ];

        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __(
                        'To apply changes you should fill valid value to required "%1" field',
                        $requiredFields[$field]
                    )
                );
            }
        }
        return $errorNo;
    }

    /**
     * Check if name is already exist or not
     *
     * @param array $data RequireFields
     *
     * @return bool
     */
    public function checkNameExist(array $data)
    {
        $errorNo = true;
        if (isset($data['faq_category_id'])) {
            $faqCategoryCollection = $this->faqCategoryFactory->create()
                ->addFieldToFilter('faq_category_id', ['neq' => $data['faq_category_id']]);
        } else {
            $faqCategoryCollection = $this->faqCategoryFactory->create();
        }
        foreach ($faqCategoryCollection as $faqCategory) {
            $categoryName = trim(mb_strtolower(preg_replace('/\s+/', ' ', $faqCategory->getFaqCategoryName()), 'UTF-8'));
            if (trim(preg_replace('/\s+/', ' ', mb_strtolower($data['faq_category_name'], 'UTF-8'))) == $categoryName) {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('This name is already exist.')
                );
            }
        }
        return $errorNo;
    }
}
