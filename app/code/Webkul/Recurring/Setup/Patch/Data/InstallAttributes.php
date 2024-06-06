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
namespace Webkul\Recurring\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class InstallAttributes implements DataPatchInterface
{
    public const ATTRIBUTE_GROUP = 'Subscription Configuration';
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    private $attributeManagement;
    /**
     * @var \Webkul\Recurring\Model\RecurringTerms
     */
    private $terms;
    /**
     * @var GroupFactory
     */
    private $attributeGroupFactory;
    /**
     * @var TypeFactory
     */
    private $eavTypeFactory;
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param EavSetupFactory $eavSetupFactory
     * @param TypeFactory $eavTypeFactory
     * @param GroupFactory $attributeGroupFactory
     * @param \Webkul\Recurring\Model\RecurringTerms $terms
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        EavSetupFactory $eavSetupFactory,
        TypeFactory $eavTypeFactory,
        GroupFactory $attributeGroupFactory,
        \Webkul\Recurring\Model\RecurringTerms $terms,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory       = $eavSetupFactory;
        $this->attributeSetFactory   = $attributeSetFactory;
        $this->attributeManagement   = $attributeManagement;
        $this->terms                 = $terms;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->eavTypeFactory        = $eavTypeFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->addDefaultData();
        $attributeGroup = self::ATTRIBUTE_GROUP;
        $attributes = $this->getAttributeData();

        /** @var entityType $entityType */
        $entityType = $this->eavTypeFactory->create()->loadByCode('catalog_product');
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection */
        $setCollection = $this->attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());
        $attributeGroupCode =  str_replace(' ', '-', strtolower($attributeGroup));
         /** @var Set $attributeSet */
        foreach ($setCollection as $attributeSet) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            /** @var Group $group */
            $eavSetup->addAttributeGroup(
                $entityType->getId(),
                $attributeSet->getId(),
                $attributeGroup,
                60
            );
            $group = $this->attributeGroupFactory->create()->getCollection()
                ->addFieldToFilter(
                    'attribute_group_code',
                    ['eq' => $attributeGroupCode]
                )
                ->addFieldToFilter(
                    'attribute_set_id',
                    ['eq' => $attributeSet->getId()]
                );

            $groupId = $attributeSet->getDefaultGroupId();
            foreach ($group as $grp) {
                $groupId = $grp->getId();
                break;
            }

            foreach ($attributes as $attribute_code => $attributeOptions) {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attribute_code,
                    $attributeOptions
                );
            }
            foreach ($attributes as $attribute_code => $attributeOptions) {
                // Assign:
                $this->attributeManagement->assign(
                    'catalog_product',
                    $attributeSet->getId(),
                    $groupId,
                    $attribute_code,
                    $attributeSet->getCollection()->getSize() * 10
                );
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get dependencies
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     */
    public function getAliases()
    {
        return [];
    }
    
    /**
     * Get attribute data
     *
     * @return array
     */
    private function getAttributeData()
    {
        $attributeGroup = self::ATTRIBUTE_GROUP;
        return [
            'subscription' => [
                'group'                      => $attributeGroup,
                'input'                      => 'select',
                'type'                       => 'int',
                'label'                      => __('Subscription'),
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => true,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'source'                     => \Webkul\Recurring\Model\Config\Source\Options::class,
                'frontend_class'             => '',
                'global'                     => ScopedAttributeInterface::SCOPE_WEBSITE,
                'unique'                     => false,
                'sort_order'                 => 5,
                'apply_to'                   => 'simple,,configurable,downloadable,virtual'
            ],
            'subscriptionOnlyProduct' => [
                'group'                      => $attributeGroup,
                'input'                      => 'select',
                'type'                       => 'int',
                'label'                      => __('Subscription Only Product'),
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => true,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'source'                     => \Webkul\Recurring\Model\Config\Source\Options::class,
                'frontend_class'             => '',
                'global'                     => ScopedAttributeInterface::SCOPE_WEBSITE,
                'unique'                     => false,
                'sort_order'                 => 15,
                'note'                       => __('Set "Yes" if you want to make this product
                "Only" available for Subscription'),
                'apply_to'                   => 'simple,configurable,downloadable,virtual'
            ]
        ];
    }

    /**
     * Add default data to recurring durations
     */
    private function addDefaultData()
    {
        $rows = [
                    [
                        'id' => '',
                        'title' => 'Weekly',
                        'duration_type' => 'week',
                        'duration' => '1',
                        'sort_order' => '1',
                        'status' => 1
                    ],
                    [
                        'id' => '',
                        'title' => 'Monthly',
                        'duration_type' => 'month',
                        'duration' => '1',
                        'sort_order' => '2',
                        'status' => 1
                    ],
                    [
                        'id' => '',
                        'title' => 'Yearly',
                        'duration_type' => 'year',
                        'duration' => '1',
                        'sort_order' => '3',
                        'status' => 1
                    ],
                ];
        foreach ($rows as $row) {
            if ($this->checkAvailability($row['duration_type'])) {
                $this->saveTerms($row);
            }
        }
    }

    /**
     * Check if durations are saved or not
     *
     * @param int $duration
     * @return bool
     */
    private function checkAvailability($duration)
    {
        $collection = $this->terms->getCollection()->addFieldToFilter('duration_type', $duration);
        if ($collection->getSize()) {
            return false;
        }
        return true;
    }

    /**
     * This function saves the terms row wise
     *
     * @param array $row
     */
    private function saveTerms($row)
    {
        $time = date('Y-m-d H:i:s');
        $model = $this->terms;
        $row['update_time'] = $time;
        if ($row['id'] == 0 || $row['id'] == "") {
            $row['created_time'] = $time;
        }
        $model->setData($row);
        if ($row['id']) {
            $model->setId($row['id']);
        }
        $model->save();
    }
}
