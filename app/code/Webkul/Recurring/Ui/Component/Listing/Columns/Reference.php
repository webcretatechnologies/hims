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
namespace Webkul\Recurring\Ui\Component\Listing\Columns;

class Reference extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare Data Source for Grid Listing Columns
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['ref_profile_id']) && $item['ref_profile_id'] == "") {
                    $item['ref_profile_id'] ='-';
                }
            }
        }
        return $dataSource;
    }
}
