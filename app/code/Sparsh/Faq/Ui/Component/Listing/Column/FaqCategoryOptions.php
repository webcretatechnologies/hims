<?php
/**
 * Class FaqCategoryOptions
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Ui\Component\Listing\Column;

/**
 * Class FaqCategoryOptions
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FaqCategoryOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource Resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getFaqCategory() as $field) {
            $options[] = [
                'label' => $field['faq_category_name'],
                'value' => $field['faq_category_id']
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }

    /**
     * Get States
     *
     * @return object
     */
    public function getFaqCategory()
    {
        $adapter = $this->resource->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );

        $select = $adapter->select()
            ->from($this->resource->getTableName("sparsh_faq_category"))
            ->where('is_active = 1');

        return $adapter->fetchAll($select);
    }
}
