<?php
/**
 * Class DefaultFaqCategory
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class DefaultFaqCategory
    implements DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $data[] = ['faq_category_name' => 'General', 'faq_category_description' => '<p>This is a default category and can not be deleted or disabled. You can change the name according to your requirement.</p>', 'is_active' => '1', 'sort_order' => '1'];

         $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sparsh_faq_category'),
            ['faq_category_name', 'faq_category_description', 'is_active', 'sort_order'],
            $data
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [   
        ];
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        /**
         * This internal Magento method, that means that some patches with time can change their names,
         * but changing name should not affect installation process, that's why if we will change name of the patch
         * we will add alias here
         */
        return [];
    }
}