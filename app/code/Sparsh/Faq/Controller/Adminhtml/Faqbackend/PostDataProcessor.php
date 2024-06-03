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
namespace Sparsh\Faq\Controller\Adminhtml\Faqbackend;

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
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * Validation Factory
     *
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * Message Manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PostDataProcessor Class constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date               $dateFilter       DateFiletr
     * @param \Magento\Framework\Message\ManagerInterface                  $messageManager   MessageManager
     * @param \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory ValidationFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
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
        $errorNo = $this->validateRequireEntry($data);
        
        if ($errorNo) {
            if (!in_array($data['is_active'], [0,1]) || $data['is_active'] == '' || $data['is_active'] === null) {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __("Please enter valid status.")
                );
            }
        }

        return $errorNo;
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
            'faq_question' => __('Question'),
            'is_active' => __('Status'),
            'faq_answer' => __('Answer'),
            'store_id' => __('Storeview')
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill valid value to required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }
}
