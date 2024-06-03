<?php
namespace Webcreta\Hims\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;

class GetAttributeSet extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements OptionSourceInterface
{
    protected $attributeSetCollectionFactory;

    public function __construct(
        AttributeSetCollectionFactory $attributeSetCollectionFactory
    ) {
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [];
        $attributeSetCollection = $this->attributeSetCollectionFactory->create();
        foreach ($attributeSetCollection as $attributeSet) {
            $options[] = ['label' => __($attributeSet->getAttributeSetName()), 'value' => $attributeSet->getAttributeSetId()];
        }

        return $options;
    }
}
