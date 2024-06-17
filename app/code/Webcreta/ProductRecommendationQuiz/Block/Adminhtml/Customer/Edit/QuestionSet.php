<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit;

/**
 * Adminhtml block action item renderer
 */
class QuestionSet extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
        $this->order = $order;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context, $data);
    }

    /**
     * Render data
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $attributeId = json_decode($row->getData('question_set')); // Correcting the key name to match the expected data
        $questionNames = [];
        
        if ($attributeId) {
            foreach ($attributeId as $attributeId => $optionIds) {
                $attributeLabel = $this->getAttributeLabel($attributeId);
                $attributeType = $this->getAttributeType($attributeId);

                if ($attributeType == "select" || $attributeType == "multiselect") {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $attribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)->load($attributeId);

                    $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
                    $optionLabels = [];

                    foreach ($optionIds as $optionId) {
                        $options = $attribute->getSource()->getOptionText($optionId);
                        $optionLabels[] = $options ? $options : ''; // Get option label if it exists, otherwise set it to empty string
                    }

                    $optionLabel = implode(', ', $optionLabels);
                } else {
                    if (is_array($optionIds)) {
                        $optionLabel = json_encode($optionIds);
                    } else {
                        $optionLabel = (string) $optionIds;
                    }
                }

                $questionNames[] = "$attributeLabel : $optionLabel<br>";
            }
        }

        return implode(' ', $questionNames);
    }

    protected function getAttributeLabel($attributeId)
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeId);
            return $attribute->getDefaultFrontendLabel();
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    protected function getAttributeType($attributeId)
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeId);
            return $attribute->getFrontendInput();
        } catch (\Exception $e) {
            return null; // Handle error, maybe log it or return a default value
        }
    }
}
