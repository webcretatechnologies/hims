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
namespace Webkul\Recurring\Block\Adminhtml\Subscriptions;

use Webkul\Recurring\Model\RecurringSubscriptionsFactory;

class Reports extends \Magento\Backend\Block\Template
{
    public const REVENUE = "revenue";
    public const ACTIVE_SUBSCRIPTION = "active_subscription";
    public const NEW_SUBSCRIPTION = "new_subscription";
    public const UNSUBSCRIBED = "unsubscribed";
    public const WEEKLY = "weekly";
    public const YEARLY = "yearly";
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $configProvider;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var RecurringSubscriptionsFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Webkul\Recurring\Model\RecurringTermsFactory
     */
    protected $term;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderModel;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory
     */
    protected $mappingFactory;

    /**
     * @var \Webkul\Recurring\Model\RecurringProductPlansFactory
     */
    protected $planFactory;

    /**
     *
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     *
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $helper;

    /**
     * Construct function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param RecurringSubscriptionsFactory $subscriptionFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory
     * @param \Webkul\Recurring\Model\RecurringTermsFactory $term
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory $mappingFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer = null
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        RecurringSubscriptionsFactory $subscriptionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory,
        \Webkul\Recurring\Model\RecurringTermsFactory $term,
        \Magento\Sales\Model\Order $orderModel,
        \Webkul\Recurring\Helper\Data $helper,
        \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory $mappingFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer = null,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->planFactory = $planFactory;
        $this->term        = $term;
        $this->orderModel  = $orderModel;
        $this->helper      = $helper;
        $this->mappingFactory  = $mappingFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Magento\Framework\Serialize\SerializerInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * GetParmasDetail function is used to get request parameters
     *
     * @return array
     */
    public function getParmasDetail()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Get json helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * Get helper
     *
     * @return \Webkul\Recurring\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Array to json
     *
     * @param array $data
     * @return string
     */
    public function arrayToJson($data)
    {
        return $this->serializer->serialize($data);
    }

     /**
      * Get data set
      *
      * @return array
      */
    public function getDataForSubscriptionType()
    {
        $dataSet = [];
        $dataLabel = [];
        list($dataSet, $dataLabel, $label) = $this->getDailyData();
        return [$this->prepareDataSet($dataSet, $label), $this->arrayToJson($dataLabel)];
    }

    /**
     * Get subscription data function
     *
     * @return array
     */
    public function subscriptionData()
    {
        return $this->subscriptionFactory->create()
        ->getCollection()
        ->addFieldToFilter(
            'main_table.status',
            ['neq' => 0]
        );
    }

    /**
     * Number of Active Subscription function
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getActiveSubscription($from, $to)
    {
        $activeSubscription = $this->subscriptionData();
        $activeSubscription
            ->addFieldToFilter(
                'main_table.created_at',
                ['datetime' => true, 'from' => $from, 'to' => $to]
            );
        return $activeSubscription;
    }

    /**
     * Get new subscription function
     *
     * @param string $from
     * @param string $to
     * @return int
     */
    public function getNewSubscription($from, $to)
    {
        $activeSubscription = $this->getActiveSubscription($from, $to);
        $totalCount = $this->getNewSubscriptionCount($activeSubscription);
        return $totalCount;
    }

    /**
     * Get new subscription count
     *
     * @param array $activeSubscription
     * @return int
     */
    public function getNewSubscriptionCount($activeSubscription)
    {
        $i = 0;
        foreach ($activeSubscription as $subs) {
            $subsId = $subs->getId();
            $mappingColl = $this->mappingFactory->create()
            ->addFieldToFilter('subscription_id', $subsId);
            if (!$mappingColl->getSize()) {
                $i++;
            }
            
        }
        return $i;
    }

    /**
     * Get pervious dates function
     *
     * @param string $period
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getPerviousDates($period, $from, $to)
    {
        if ($period == self::WEEKLY) {
            $fromDate = date("Y-m-d H:i:s", strtotime('-1 week', strtotime($from))) ;
            $toDate = date("Y-m-d H:i:s", strtotime('-1 week', strtotime($to))) ;
        } elseif ($period == self::YEARLY) {
            $fromDate = date("Y-m-d", strtotime('-1 year', strtotime($from))) ;
            $toDate = date("Y-m-d", strtotime('-1 year', strtotime($to))) ;
        } else {
            $fromDate = date("Y-m-d H:i:s", strtotime('-1 month', strtotime($from))) ;
            $toDate = date("Y-m-d H:i:s", strtotime('-1 month', strtotime($to))) ;
        }
        return [$fromDate, $toDate];
    }

    /**
     * Get total revenue
     *
     * @param string $from
     * @param string $to
     * @return int
     */
    public function getTotalRevenue($from, $to)
    {
        $subsColl = $this->subscriptionFactory->create()
        ->getCollection()
        ->addFieldToSelect('order_id')
        ->addFieldToFilter(
            'main_table.created_at',
            ['datetime' => true, 'from' => $from, 'to' => $to]
        );
        
        $revenue = 0;
        foreach ($subsColl as $coll) {
            $orderId = $coll->getOrderId();
            $order = $this->orderModel->load($orderId);
            $revenue += $order->getGrandTotal();
        }
        return $revenue;
    }
    /**
     * GetDailyData function is used to get data according to days
     *
     * @return array
     */
    protected function getDailyData()
    {
        $dataSet = [];
        $dataLabel = [];
       
        list($from, $to) = $this->getDateData();
        if ($to) {
            $toDate = date_create($to);
            $to = date_format($toDate, 'm/d/Y 23:59:59');
        }
        if (!$to) {
            $to = date('m/d/Y 23:59:59');
        }
        if ($from) {
            $fromDate = date_create($from);
            $from = date_format($fromDate, 'm/d/Y H:i:s');
        }
        if (!$from) {
            $from = date('m/d/Y 23:59:59');
        }
            
        $fromYear = $from ? date('Y', strtotime($from)) : date('Y');
        $fromMonth = $from ? date('m', strtotime($from)) : 1;
        $fromDay = $from ? date('d', strtotime($from)) : 1;
        $currYear = $to ? date('Y', strtotime($to)) : date('Y');
        $currMonth = $to ? date('m', strtotime($to)) : date('m');
        $currDay = $to ? date('d', strtotime($to)) : date('d');
        for ($startYear = $fromYear; $startYear <= $currYear; ++$startYear) {
            $months = 12;
            if ($startYear == $currYear) {
                $months = $currMonth;
            }
            $monthStart = ($startYear == $fromYear && $from) ? $fromMonth : 1;

            $dailyArrData = $this->getDailyArrData(
                $monthStart,
                $months,
                $fromYear,
                $fromMonth,
                $from,
                $fromDay,
                $startYear,
                $currDay,
                $currMonth,
                $currYear
            );

            $dataSet = $dailyArrData['data_set'];
            $dataLabel = $dailyArrData['data_label'];
            $label = $dailyArrData['label'];
        }
    
        return [$dataSet, $dataLabel, $label];
    }
     /**
      * Get date data
      *
      * @return array
      */
    public function getDateData()
    {
        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'main_table.status',
                ['neq' => 0]
            )->setOrder('created_at', 'ASC')->getFirstItem();
            $from = $collection->getCreatedAt();
            $to = date("m/d/Y");
        
        return [$from, $to];
    }

    /**
     * GetDailyArrData to calculate data_set and data_label.
     *
     * @param int $monthStart
     * @param int $months
     * @param int $fromYear
     * @param int $fromMonth
     * @param int $from
     * @param int $fromDay
     * @param int $startYear
     * @param int $currDay
     * @param int $currMonth
     * @param int $currYear
     * @return array
     */
    public function getDailyArrData(
        $monthStart,
        $months,
        $fromYear,
        $fromMonth,
        $from,
        $fromDay,
        $startYear,
        $currDay,
        $currMonth,
        $currYear
    ) {
        $dataSet = [];
        $dataLabel = [];
        for ($monthValue = $monthStart; $monthValue <= $months; ++$monthValue) {
            $dayStart = ($startYear == $fromYear && $monthValue == $fromMonth && $from) ? $fromDay : 1;
            $days = $this->getMonthDays($monthValue, $startYear);
            if ($startYear == $currYear && $monthValue == $currMonth) {
                $days = $currDay;
            }
            for ($dayValue = $dayStart; $dayValue <= $days; ++$dayValue) {
                $date1 = $startYear.'-'.$monthValue.'-'.$dayValue.' 00:00:00';
                $date2 = $startYear.'-'.$monthValue.'-'.$dayValue.' 23:59:59';
                
                $collection = $this->subscriptionFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'main_table.status',
                                ['neq' => 0]
                            )->addFieldToFilter(
                                'main_table.created_at',
                                ['datetime' => true, 'from' => $date1, 'to' => $date2]
                            );
                $temp = 0;
                $label = "";
                $post = $this->getRequest()->getParams();
               
                if (isset($post['revenue-action-select']) && $post['revenue-action-select'] == self::NEW_SUBSCRIPTION) {
                    $temp = $this->getNewSubscriptionCount($collection);
                    $label = __("New Subscription");
                } elseif (isset($post['revenue-action-select'])
                && $post['revenue-action-select'] == self::ACTIVE_SUBSCRIPTION) {
                    $temp = $collection->count();
                    $label = __("Active Subscription");
                } elseif (isset($post['revenue-action-select'])
                && $post['revenue-action-select'] == self::UNSUBSCRIBED) {
                    $collection = $this->subscriptionFactory->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'main_table.status',
                        ['eq' => 0]
                    )->addFieldToFilter(
                        'main_table.created_at',
                        ['datetime' => true, 'from' => $date1, 'to' => $date2]
                    );
                    $temp = $collection->count();
                    $label = __("UnSubscribed");
                } else {
                    $label = __("Revenue");
                    foreach ($collection as $record) {
                        $planId = $record->getPlanId();
                        $productId = $record->getProductId();
                        $plansCollection =  $this->planFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'main_table.type',
                            ['eq' => $planId]
                        )->addFieldToFilter(
                            'main_table.product_id',
                            ['eq' => $productId]
                        );
                        $charge = $initialFee = 0;
                        foreach ($plansCollection as $planCollection) {
                            $charge = $planCollection->getSubscriptionCharge();
                            $initialFee =$planCollection->getInitialFee();
                        }
                        $totalShipping = $charge + $initialFee;
                    
                        $temp += $totalShipping;
                    }
                }
               
                if ($temp) {
                    $dataSet[] = $temp;
                    $dataLabel[] = date('m/d/Y', strtotime($monthValue."/".$dayValue."/".$startYear));
                }
            }
        }
        return ['data_set' => $dataSet, 'data_label' => $dataLabel, 'label'=>$label];
    }

    /**
     * Get month days
     *
     * @param string $month
     * @param string $year
     * @return int
     */
    public function getMonthDays($month, $year)
    {
        $days = 28;
        if ((0 == $year % 4) && (0 != $year % 100) || (0 == $year % 400)) {
            $days = 29;
        }
        $monthsWithThirty = [4,6,9,11];
        $monthsWithThirtyOne = [1,3,5,7,8,10,12];
        if (in_array($month, $monthsWithThirty)) {
            $days = 30;
        } elseif (in_array($month, $monthsWithThirtyOne)) {
            $days = 31;
        }
        return $days;
    }

    /**
     * Prepare data set
     *
     * @param array $data
     * @param string $label
     * @return array
     */
    public function prepareDataSet($data, $label)
    {
        if ($label == 'Churn Rate') {
            $dataArray[] = [
                "label"=> $label,
                "borderColor"=> '#eb5202',
                "backgroundColor" => [
                    '#eb52021a',
                    ],
                "borderWidth" => 1,
                "borderRadius" =>  20,
                "data" => $data
            ];
            return $dataArray;
        } else {
            $dataArray[] = [
                "label"=> $label,
                "borderColor"=> '#1979C3',
                "backgroundColor" => [
                    '#1d8ee638',
                    ],
                "borderWidth" => 1,
                "borderRadius" =>  20,
                "data" => $data
            ];
            return $dataArray;
        }
    }

    /**
     * Get data for subscription type
     *
     * @return array
     */
    public function getDataForActiveSubscription()
    {
        $countActive = [];
        $label = [];
        $allPlanList = $this->term->create()->getCollection();
        foreach ($allPlanList as $plan) {
            $planId = $plan->getEntityId();
            $subscriptionColl = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'main_table.status',
                ['neq' => 0]
            )->addFieldToFilter(
                'main_table.plan_id',
                ['eq' => $planId]
            );
            $label[] = $plan->getTitle();
            $countActive[] = $subscriptionColl->count();
                
        }
        return[$this->prepareData($countActive),$this->arrayToJson($label)];
    }

    /**
     * Prepare Data
     *
     * @param array $data
     * @return array
     */
    public function prepareData($data)
    {
        $dataArray[] = [
            'label' => __('Active Subscription'),
            'borderColor' => "#FAF9F6",
            'backgroundColor' => ['rgb(255, 99, 132)',
                                'rgb(54, 162, 235)',
                                'rgb(255, 205, 86)',
                                'green'],
            'borderWidth' => 1,
            'borderRadius' => 20,
            'data' => $data
        ];
        return $dataArray;
    }

    /**
     * Get churn rate
     *
     * @return array
     */
    public function getDataForChurnRate()
    {
        $dataSet = [];
        $dataLabel = [];
        $label = __('Churn Rate');
        list($dataSet, $dataLabel, $totalChurnRate) = $this->getMonthlyChurnRateData();
        return [$this->prepareDataSet($dataSet, $label),
        $this->arrayToJson($dataLabel), $totalChurnRate];
    }

    /**
     * GetMonthlyChurnRateData function is used to get churn rate according to months
     *
     * @return array
     */
    protected function getMonthlyChurnRateData()
    {
        $dataSet = [];
        $dataLabel = [];
           
        list($from, $to) = $this->getDateData();
        if ($to) {
            $toDate = date_create($to);
            $to = date_format($toDate, 'm/d/Y 23:59:59');
        }
        if (!$to) {
            $to = date('m/d/Y 23:59:59');
        }
        if ($from) {
            $fromDate = date_create($from);
            $from = date_format($fromDate, 'm/d/Y H:i:s');
        }
        if (!$from) {
            $from = date('m/d/Y 23:59:59');
        }
        
        if ($from == "" && $to == "") {
            $to = date('12/31/Y 23:59:59');
            $from = date('01/01/Y 23:59:59');
        }
            
        $fromYear = $from ? date('Y', strtotime($from)) : date('Y');
        $fromDay = $from ? (int)date('d', strtotime($from)) : 1;
        $fromMonth = $from ? (int)date('m', strtotime($from)) : 1;
        $currYear = $to ? date('Y', strtotime($to)) : date('Y');
        $currMonth = $to ? (int)date('m', strtotime($to)) : date('m');
        $currDay = $to ? (int)date('d', strtotime($to)) : date('d');
        for ($startYear = $fromYear; $startYear <= $currYear; ++$startYear) {
            $months = 12;
            if ($startYear == $currYear) {
                $months = $currMonth;
            }
            
            $monthStart = ($startYear == $fromYear && $from) ? $fromMonth : 1;
            $monthlyArrData = $this->getMonthlyArrData(
                $monthStart,
                $months,
                $fromYear,
                $fromMonth,
                $from,
                $fromDay,
                $startYear,
                $currDay,
                $currMonth,
                $currYear
            );
            $dataSet = $monthlyArrData['data_set'];
            $dataLabel = $monthlyArrData['data_label'];
            $totalChurnRate = $monthlyArrData['totalChurnRate'];
        }
    
        return [$dataSet, $dataLabel, $totalChurnRate];
    }

    /**
     * GetMonthlyArrData to calculate data_set and data_label.
     *
     * @param int $monthStart
     * @param int $months
     * @param int $fromYear
     * @param int $fromMonth
     * @param int $from
     * @param int $fromDay
     * @param int $startYear
     * @param int $currDay
     * @param int $currMonth
     * @param int $currYear
     * @return array
     */
    public function getMonthlyArrData(
        $monthStart,
        $months,
        $fromYear,
        $fromMonth,
        $from,
        $fromDay,
        $startYear,
        $currDay,
        $currMonth,
        $currYear
    ) {
        $dataSet = [];
        $dataLabel = [];
        $monthsArray = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct',
        11 => 'Nov', 12 => 'Dec'];
        $totalChurnRate = 0;
        for ($monthValue = $monthStart; $monthValue <= $months; ++$monthValue) {
            $days = $this->getMonthDays($monthValue, $startYear);
            $dayStart = ($startYear == $fromYear && ($fromMonth == $monthValue) && $from) ? $fromDay : '1';
            $dayEnd = ($startYear == $currYear && ($currMonth == $monthValue) && $from) ? $currDay : $days;
            $date1 = $startYear.'-'.$monthValue.'-'.$dayStart.' 00:00:00';
            $date2 = $startYear.'-'.$monthValue.'-'.$dayEnd.' 23:59:59';
            $churnRate = 0;
            $cancelledSubscription = $this->subscriptionFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'main_table.status',
                                ['eq' => 0]
                            )->addFieldToFilter(
                                'main_table.created_at',
                                ['datetime' => true, 'from' => $date1, 'to' => $date2]
                            );
            $totalSubsOnTimePeriod = $this->subscriptionFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'main_table.created_at',
                                        ['datetime' => true, 'from' => $date1, 'to' => $date2]
                                    );
                                    
            if (($totalSubsOnTimePeriod->count()) > 0) {
                $churnRate += ($cancelledSubscription->count()/$totalSubsOnTimePeriod->count())*100;
            }
            $totalChurnRate += $churnRate;
            if ($churnRate) {
                $dataLabel[] = $monthsArray[$monthValue];
                $dataSet[] = $churnRate;
            }
        }
    
        return ['data_set' => $dataSet, 'data_label' => $dataLabel, 'totalChurnRate' => $totalChurnRate];
    }

    /**
     * GetPeriodValues function is return the list of filter periods
     *
     * @return array
     */
    public function getPeriodValues()
    {
        return [
                [
                    'value' => self::WEEKLY,
                    'label' => __('Weekly')
                ],
                [
                    'value' => 'monthly',
                    'label' => __('Monthly')
                ],
                [
                    'value' => self::YEARLY,
                    'label' => __('Yearly')
                ]
            ];
    }

    /**
     * GetRevenuePeriodValues function is return the list of filter periods
     *
     * @return array
     */
    public function getRevenuePeriodValues()
    {
        return [
                [
                    'value' => self::REVENUE,
                    'label' => __('Subscription Revenue')
                ],
                [
                    'value' => self::ACTIVE_SUBSCRIPTION,
                    'label' => __('Active Subscription')
                ],
                [
                    'value' => self::NEW_SUBSCRIPTION,
                    'label' => __('New Subscription')
                ],
                [
                    'value' => self::UNSUBSCRIBED,
                    'label' => __('UnSubscribed')
                ]
            ];
    }

    /**
     * Get average subscription function
     *
     * @param string $from
     * @param string $to
     * @return int
     */
    public function getAvgSubscription($from, $to)
    {
        $revenue = 0;
        $total = 0;
        $i = $j = 0;
        $count = 0;
        $activeSubs = $this->getActiveSubscription($from, $to);
        $activeSubs->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $from, 'to' => $to]
        );
        foreach ($activeSubs as $subs) {
            $orderId = $subs->getOrderId();
            $order = $this->orderModel->load($orderId);
            $revenue += $order->getGrandTotal();
            $i++;
        }
        
        $mappingColl = $this->mappingFactory->create()
            ->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $from, 'to' => $to]
            );
            
        foreach ($mappingColl as $coll) {
            $orderId = $coll->getOrderId();
            $order = $this->orderModel->load($orderId);
            $total += $order->getGrandTotal();
            $j++;
        }
        $totalCount = $i+$j;
        if ($totalCount > 0) {
            $count = round((($revenue + $total)/$totalCount), 2);
        }
        return $count;
    }

    /**
     * Get FilterData function
     *
     * @return array
     */
    public function getFilterData()
    {
        $limit = $this->getRequest()->getParam('period');
        $avgSubs = [];
        $totalRevenue = [];
        $activeSubs = [];
        $newSubs = [];
        switch ($limit) {
            case self::WEEKLY:
                list($avgSubs, $totalRevenue, $activeSubs, $newSubs) = $this->getWeeklyData($limit);
                break;
            case self::YEARLY:
                list($avgSubs, $totalRevenue, $activeSubs, $newSubs) = $this->getYearlyData($limit);
                break;
            default:
                list($avgSubs, $totalRevenue, $activeSubs, $newSubs) = $this->getSubsMonthlyData($limit);
                break;
        }
        
        return [$avgSubs, $totalRevenue, $activeSubs, $newSubs];
    }

   /**
    * Get dates
    *
    * @return array
    */
    public function getDatesData()
    {
        $params = $this->getRequest()->getParams();
        $from = $params['from'] ?? '';
        $to = $params['to'] ?? '';
        if ($to) {
            $toDate = date_create($to);
            $to = $toDate ? date_format($toDate, 'Y-m-d H:i:s') : null;
        }
        if (!$to) {
            $to = date('Y-m-d H:i:s');
        }
        if ($from) {
            $fromDate = date_create($from);
            $from = $fromDate ? date_format($fromDate, 'Y-m-d H:i:s') : null;
        }
        
        return [$from, $to];
    }

    /**
     * Get monthly data function
     *
     * @param string $period
     * @return array
     */
    public function getSubsMonthlyData($period)
    {
        list($from, $to) = $this->getDatesData();
        if (!$from) {
            $from = date("Y-m-01 H:i:s");
        }
        list($revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs) = $this->getReturnData($period, $from, $to);
        return [$revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs];
    }

    /**
     * Get filtered subscription data function
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getSubscriptionData($from, $to)
    {
        $totalRevenue = $this->getTotalRevenue($from, $to);
        $activeSubs = $this->getActiveSubscription($from, $to);
        $avgSubs = $this->getAvgSubscription($from, $to);
        $newSubs = $this->getNewSubscription($from, $to);
        return [$totalRevenue, $activeSubs->count(), $avgSubs, $newSubs];
    }

    /**
     * Get return data function
     *
     * @param string $period
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getReturnData($period, $from, $to)
    {
        list($fromDate, $toDate) = $this->getPerviousDates($period, $from, $to);
        list($totalRevenue, $activeSubs, $avgSubs, $newSubs) = $this->getSubscriptionData($from, $to);
        list($revenuePercent,
        $activeSubsPercent,
        $avgSubsPercent,
        $newSubsPercent) = $this->getPercentData($totalRevenue, $activeSubs, $avgSubs, $newSubs, $fromDate, $toDate);
        $revenue = ['totalRevenue' => $totalRevenue, 'revenuePercent' => $revenuePercent];
        $activeSubscription = ['activeSubs' => $activeSubs, 'activeSubsPercent' => $activeSubsPercent];
        $totalAvgSubs = ['avgSubs'=> $avgSubs, 'avgSubsPercent' => $avgSubsPercent];
        $totalNewSubs = ['newSubs' => $newSubs, 'newSubsPercent' => $newSubsPercent];
        return [$revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs];
    }
    /**
     * Get subscription percent function
     *
     * @param int $totalRevenue
     * @param int $activeSubs
     * @param int $avgSubs
     * @param int $newSubs
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getPercentData($totalRevenue, $activeSubs, $avgSubs, $newSubs, $fromDate, $toDate)
    {
        $revenuePercent = $this->getRevenuePercent($totalRevenue, $fromDate, $toDate);
        $activeSubsPercent = $this->getActiveSubsPercent($activeSubs, $fromDate, $toDate);
        $avgSubsPercent = $this->getAvgSubscriptionPercent($avgSubs, $fromDate, $toDate);
        $newSubsPercent = $this->getNewSubscriptionPercent($newSubs, $fromDate, $toDate);
        return[$revenuePercent, $activeSubsPercent, $avgSubsPercent, $newSubsPercent];
    }

    /**
     * Get monthly revenue percent function
     *
     * @param int $totalRevenue
     * @param string $fromDate
     * @param string $toDate
     * @return float
     */
    public function getRevenuePercent($totalRevenue, $fromDate, $toDate)
    {
        $percent = 0;
        $previousMonthRevenue = $this->getTotalRevenue($fromDate, $toDate);
        if ($totalRevenue > 0) {
            $percent = round(((($totalRevenue - $previousMonthRevenue)/$totalRevenue) * 100), 2);
        }
        return $percent;
    }

    /**
     * Get monthly active subs percent function
     *
     * @param int $activeSubs
     * @param string $fromDate
     * @param string $toDate
     * @return float
     */
    public function getActiveSubsPercent($activeSubs, $fromDate, $toDate)
    {
        $percent = 0;
        $previousMonthSubs = $this->getActiveSubscription($fromDate, $toDate);
        if ($activeSubs > 0) {
            $percent = round(((($activeSubs - $previousMonthSubs->count())/$activeSubs) * 100), 2);
        }
        return $percent;
    }

    /**
     * Get monthly avg subs percent function
     *
     * @param int $avgSubs
     * @param string $fromDate
     * @param string $toDate
     * @return float
     */
    public function getAvgSubscriptionPercent($avgSubs, $fromDate, $toDate)
    {
        $percent = 0;
        $previousMonthAvgSubs = $this->getAvgSubscription($fromDate, $toDate);
        if ($avgSubs > 0) {
            $percent = round(((($avgSubs - $previousMonthAvgSubs)/$avgSubs) * 100), 2);
        }
        return $percent;
    }

    /**
     * Get monthly new subs percent function
     *
     * @param int $newSubs
     * @param string $fromDate
     * @param string $toDate
     * @return float
     */
    public function getNewSubscriptionPercent($newSubs, $fromDate, $toDate)
    {
        $percent = 0;
        $previousMonthNewSubs = $this->getNewSubscription($fromDate, $toDate);
        if ($newSubs > 0) {
            $percent = round(((($newSubs - $previousMonthNewSubs)/$newSubs) * 100), 2);
        }
        return $percent;
    }

    /**
     * Get weekly subscription data function
     *
     * @param string $period
     * @return array
     */
    public function getWeeklyData($period)
    {
        list($from, $to) = $this->getDatesData();
        if (!$from) {
            $from = date('Y-m-d H:i:s', strtotime('last Sunday'));
        }
        list($revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs) = $this->getReturnData($period, $from, $to);
        return [$revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs];
    }

    /**
     * Get yearly data function
     *
     * @param string $period
     * @return array
     */
    public function getYearlyData($period)
    {
        list($from, $to) = $this->getDatesData();
        if (!$from) {
            $from = date('Y-m-d H:i:s', strtotime(date('Y-01-01')));
        }
        list($revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs) = $this->getReturnData($period, $from, $to);
        return [$revenue, $activeSubscription, $totalAvgSubs, $totalNewSubs];
    }
}
