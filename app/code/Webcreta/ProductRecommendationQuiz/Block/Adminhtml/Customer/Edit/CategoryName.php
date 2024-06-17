<?php

namespace Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit;

/**
 * Adminhtml block action item renderer
 */
class CategoryName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        array $data = []
    ) {
        $this->product = $product;
        $this->attributeSetRepository = $attributeSetRepository;
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
        $attributeSetId = $row->getData('category'); // Assuming 'entity_id' is the product ID
        

        try {
            $attributeSet = $this->attributeSetRepository->get($attributeSetId);
            $attributeSetName = $attributeSet->getAttributeSetName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $attributeSetName = __('Unknown Attribute Set');
        }

        return $attributeSetName;
    }
}
