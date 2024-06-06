<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Controller\Adminhtml\Duration;

/**
 * Recurring Adminhtml terms Save Controller
 */
class Save extends \Webkul\Recurring\Controller\Adminhtml\AbstractRecurring
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::term';

    /**
     * This function is reponsible for the saving of plans and terms information
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams()['information'];
        $id =  $data['entity_id'] = isset($data['entity_id']) ? $data['entity_id'] : "";
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $this->updateRecurringProductPlans($data);
        $result = $this->saveTerms($data);
        
        if ($result['status']) {
            $this->messageManager->addSuccessMessage($result['message']);
        } else {
            $this->dataPersistor->set('recurring_duration', $result['data']);
            $this->messageManager->addErrorMessage($result['message']);
            return $resultRedirect->setPath('*/*/new');
        }
        $id = $result['id'];
        return $resultRedirect->setPath('*/*/edit', ['id' => $id ]);
    }

    /**
     * This function is used to filter the plan type as per duration
     *
     * @param array $data
     */
    private function updateRecurringProductPlans($data)
    {
        $coll = $this->plans->getCollection();
        $coll->addFieldToFilter("type", $data['entity_id']);
        
        foreach ($coll as $model) {
            $this->updateType($data["sort_order"], $model);
        }
    }

    /**
     * This function is used to update the sort order in all product plans
     *
     * @param integer $sortOrder
     * @param \Webkul\Recurring\Model\Subscription $model
     */
    private function updateType($sortOrder, $model)
    {
        $model->setSortOrder($sortOrder);
        $model->setId($model->getId())->save();
    }

    /**
     * This function saves the terms row wise
     *
     * @param array $row
     * @return array
     */
    private function saveTerms($row)
    {
        try {
            $time = date('Y-m-d H:i:s');
            $model = $this->terms;
            if (isset($row['duration'])) {
                $collection = $model->getCollection()
                            ->addFieldToFilter('duration', $row['duration'])
                            ->addFieldToFilter('duration_type', $row['duration_type'])
                            ->addFieldToFilter('entity_id', ["neq" => $row['entity_id']]);
                           
                if ($collection->getSize()) {
                    return [
                        'status' => false,
                        'id' => ($row['entity_id']) ? $row['entity_id'] : '',
                        'data' => $row,
                        'message' => __('This Duration already available')
                    ];
                }
            }
            
            $row['update_time'] = $time;
            if ($row['entity_id'] == 0 || $row['entity_id'] == "") {
                $row['created_time'] = $time;
                unset($row['entity_id']);
            } else {
                $model->setId($row['entity_id']);
            }
            $model->setData($row);
            $model->save();
            if ($model->getId()) {
                return [
                    'status' => true,
                    'id' => $model->getId(),
                    'data' => $model->getData(),
                    'message' => __('Record Saved Successfully')
                ];
            } else {
                return [
                    'status' => false,
                    'data' => $model->getData(),
                    'message' => __('Record not Saved Successfully. Try again!')
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'id' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
