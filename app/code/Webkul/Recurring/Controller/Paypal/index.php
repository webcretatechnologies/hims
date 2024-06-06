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
namespace Webkul\Recurring\Controller\Paypal;

/**
 * Webkul Recurring Landing page Index Controller.
 */
class Index extends PaypalAbstract
{
    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $isSandBox = $this->helper->getConfig(parent::SANDBOX);
        $clientId  = $this->encryptor->decrypt($this->helper->getConfig(parent::CLIENT_ID));
        $secretKey = $this->encryptor->decrypt($this->helper->getConfig(parent::SECRET_KEY));
        
        $order = $this->checkoutSession->getLastRealOrder();
        //get paypal access key
        $responseData = $this->getAccessToken($isSandBox, $clientId, $secretKey);
        if (!array_key_exists("access_token", $responseData)) {
            $resultRedirect = $this->orderCancel($order);
            return $resultRedirect;
        }
        $this->helper->setData('token', $responseData['access_token']);
        $this->helper->setData('expires_in', $responseData['expires_in']);
        $this->helper->setData('nonce', $responseData['nonce']);
        $accessToken = $responseData['access_token'];

        //create paypal product
        $responseData = $this->createProduct($accessToken, $order, $isSandBox);
        if (!array_key_exists("id", $responseData)) {
            $resultRedirect = $this->orderCancel($order);
            return $resultRedirect;
        }
        $productId = $responseData['id'];

        //create paypal plan
        $responseData = $this->createPlan($accessToken, $productId, $order, $isSandBox);
        if (!array_key_exists("id", $responseData)) {
            $resultRedirect = $this->orderCancel($order);
            return $resultRedirect;
        }
        $paypalPlanId = $responseData['id'];

        //create paypal subscription
        $responseData = $this->createSubscription($paypalPlanId, $accessToken, $order, $isSandBox);
        if (!array_key_exists("status", $responseData)) {
            $resultRedirect = $this->orderCancel($order);
            return $resultRedirect;
        }

        if ($responseData['status'] == "APPROVAL_PENDING") {
            $redirectUrl = $responseData['links']['0']['href'];
        }
        
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirect->create(
            $this->resultRedirect::TYPE_REDIRECT
        );
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

    /**
     * Cancel order
     *
     * @param string $order
     * @return string
     */
    public function orderCancel($order)
    {
        $order->cancel();
        $order->save();
        $this->messageManager->addErrorMessage(__("Something went wrong with the payment."));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirect->create(
            $this->resultRedirect::TYPE_REDIRECT
        );
        $resultRedirect->setUrl(
            $this->urlBuilder->getUrl("checkout/onepage/failure")
        );
        return $resultRedirect;
    }

    /**
     * Create paypal subscription
     *
     * @param string $paypalPlanId
     * @param string $accessToken
     * @param \Magento\Checkout\Model\Session $order
     * @param bool $isSandBox
     * @return array
     */
    public function createSubscription($paypalPlanId, $accessToken, $order, $isSandBox)
    {
        $quote              = $this->quoteRepository->get($order->getQuoteId());
        $cartData           = $quote->getAllVisibleItems();
        list(
            $description, $startDate, $endDate, $subscriptionsAmt, $initialFee, $planId, $descriptionPlanInfo
        ) = $this->getQuoteData($quote);
        $currentDate = strtotime($this->date->date());
        $currentDate = date("Y-m-d", $currentDate);
        $tmStamp            = strtotime($startDate);
        $startTime = date("Y-m-d", $tmStamp)."T".date("H:i:s", $tmStamp)."Z";
        $shippingAmt        = number_format((float)$order->getShippingAmount(), 2, ".", "");
        $endPointUrl = parent::URL;
        $endPointUrl .= (($isSandBox) ? "sandbox." : "");
        $endPointUrl .=  parent::URL_COMPLETE ."billing/subscriptions";
        $address = $order->getBillingAddress()->getData();
        $customerFullName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        $cancelUrl = $this->urlBuilder->getUrl(parent::CANCEL_URL).'?orderId='.$order->getIncrementId();
        $returnUrl = $this->urlBuilder->getUrl(parent::RETURN_URL).'?orderId='.$order->getIncrementId();

        $postData = [
            "plan_id" => $paypalPlanId,
            "shipping_amount" => [
                    "currency_code" => $order->getOrderCurrencyCode(),
                    "value" => $shippingAmt
                ],
            "subscriber" => [
                        "name" => [
                            "given_name" => $order->getCustomerFirstname(),
                            "surname" => $order->getCustomerLastname()
                        ],
                        "email_address" => $order->getCustomerEmail(),
                        "shipping_address" => [
                            "name" => [
                                "full_name" => $customerFullName
                            ],
                            "address" => [
                                    "address_line_1" => $address['street'],
                                    "admin_area_2" => $address['city'],
                                    "admin_area_1" => $address['region'],
                                    "postal_code" => $address['postcode'],
                                    "country_code" => $address['country_id']
                                ]
                        ]
                    ],
            "application_context" => [
                                    "locale" => "en-US",
                                    "shipping_preference" => "SET_PROVIDED_ADDRESS",
                                    "user_action" => "SUBSCRIBE_NOW",
                                    "payment_method" => [
                                        "payer_selected" => "PAYPAL",
                                        "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
                                    ],
                                    "return_url" => $returnUrl,
                                    "cancel_url" => $cancelUrl
                                    ]

        ];
        if ($currentDate != date("Y-m-d", $tmStamp)) {
            $postData['start_time'] = $startTime;
        }
        $postData = $this->helper->jsonEncode($postData);
        $headerData = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Authorization" => "Bearer ". $accessToken
        ];
        $this->curl->setHeaders($headerData);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        $responseData = $this->helper->jsonDecode($response);
        return $responseData;
    }

    /**
     * Create paypal Plan
     *
     * @param string $accessToken
     * @param string $productId
     * @param \Magento\Checkout\Model\Session $order
     * @param bool $isSandBox
     * @return string
     */
    public function createPlan($accessToken, $productId, $order, $isSandBox)
    {
        $quote              = $this->quoteRepository->get($order->getQuoteId());
        $cartData           = $quote->getAllVisibleItems();
        $descriptionPlanInfo = $period = '';
        $startDate          = date("Y-m-d H:i:s");
        $subscriptionsAmt   = 0.0;
        $planId             = $duration = 0;
        list(
            $description, $startDate, $endDate, $subscriptionsAmt, $initialFee, $planId, $descriptionPlanInfo
        ) = $this->getQuoteData($quote);
        if ($planId) {
            $result         = $this->getFrequency($planId);
            if ($result['interval_count'] != 0) {
                $duration = $result['interval_count'];
                $period = strtoupper($result['interval']);
            }
        }
        if ($period != 'DAY') {
            $endDate = false;
        }
        $endPointUrl = parent::URL;
        $endPointUrl .= (($isSandBox) ? "sandbox." : "");
        $endPointUrl .=  parent::URL_COMPLETE ."billing/plans";
        $currencyCode = $order->getOrderCurrencyCode();

        $postData = [
            "name" => $period,
            "product_id" => $productId,
            "status" => "ACTIVE",
            "billing_cycles" => [
                    [
                        "frequency" => [
                        "interval_unit" => $period,
                        "interval_count" => $duration
                        ],
                        "tenure_type" => "REGULAR",
                        "sequence" => 1,
                        "pricing_scheme" => [
                            "fixed_price" => [
                                "value" => $subscriptionsAmt,
                                "currency_code" => $currencyCode
                            ]
                        ]
                    ]
                ],
            "payment_preferences" => [
                                        "auto_bill_outstanding" => true,
                                        "setup_fee" => [
                                        "value" => $descriptionPlanInfo['Initial Fee'],
                                        "currency_code" => $currencyCode
                                        ],
                                        "setup_fee_failure_action" => "CONTINUE",
                                        "payment_failure_threshold" => 3
                                    ],
            "taxes" => [
                            "percentage" => "10",
                            "inclusive" => true
                        ]

        ];
        if ($endDate == false) {
            $postData["billing_cycles"][0]["total_cycles"] = 0;
        } else {
            $dayLen = 60*60*24;
            $days = ((strtotime($endDate)-strtotime($startDate))/$dayLen) + 1;
            $postData["billing_cycles"][0]["total_cycles"] = $days;
        }
        $postData = $this->helper->jsonEncode($postData);
        $headerData = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Authorization" => "Bearer ". $accessToken
        ];
        $this->curl->setHeaders($headerData);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        $responseData = $this->helper->jsonDecode($response);

        return $responseData;
    }

     /**
      * Get access token
      *
      * @param string $isSandBox
      * @param string $clientId
      * @param string $secretKey
      * @return string
      */
    public function getAccessToken($isSandBox, $clientId, $secretKey)
    {
        $endPointUrl = self::URL;
        $endPointUrl .= (($isSandBox) ? "sandbox." : "");
        $endPointUrl .=  self::URL_COMPLETE ."oauth2/token";
        $postData = [
            "grant_type"     => "client_credentials"
        ];
        $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $this->curl->setCredentials($clientId, $secretKey);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        $responseData = $this->helper->jsonDecode($response);
        return $responseData;
    }

    /**
     * Create paypal product
     *
     * @param string $accessToken
     * @param \Magento\Checkout\Model\Session $order
     * @param string $isSandBox
     * @return string
     */
    public function createProduct($accessToken, $order, $isSandBox)
    {
        $endPointUrl = parent::URL;
        $endPointUrl .= (($isSandBox) ? "sandbox." : "");
        $endPointUrl .=  parent::URL_COMPLETE ."catalogs/products";
        $postData = [
            "name" => "order-".$order->getIncrementId(),
            "type" => "SERVICE"
        ];
        $postData = $this->helper->jsonEncode($postData);
        $headerData = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Authorization" => "Bearer ". $accessToken
        ];
        $this->curl->setHeaders($headerData);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        $responseData = $this->helper->jsonDecode($response);
        return $responseData;
    }

    /**
     * This function is used to get the quote data
     *
     * @param \Magento\Quote\Model\QuoteRepository $quote
     * @return array
     */
    private function getQuoteData($quote)
    {
        $cartData           = $quote->getAllVisibleItems();
        $descriptionPlanInfo = $description =   '';
        $itemNameArray = [];
        $startDate          = date("Y-m-d H:i:s");
        $endDate            = false;
        $subscriptionsAmt   = $initialFee = $baseInitialFee = 0.0;
        $planId             = 0;
        foreach ($cartData as $item) {
            if ($additionalOptionsQuote =   $item->getOptionByCode('custom_additional_options')) {
                $itemNameArray[] = $item->getName();
                $allOptions = $this->jsonHelper->jsonDecode(
                    $additionalOptionsQuote->getValue()
                );
                foreach ($allOptions as $key => $option) {
                    if ($option['label'] == 'Start Date') {
                        $startDate = $option['value'];
                    }
                    if ($option['label'] == 'Subscription Charge') {
                        $subscriptionsAmt = round($item->getPriceInclTax() - $item->getDiscountAmount(), 2);
                    }
                    if ($option['label'] == 'Initial Fee') {
                        $initialFee = ((float)$initialFee) + $option['value'];
                    }
                    if ($option['label'] == 'Base Initial Fee') {
                        $baseInitialFee = ((float)$baseInitialFee) + $option['value'];
                    }
                    if ($option['label'] == 'Plan Id') {
                        $planId = $option['value'];
                    }
                    if ($option['label'] == 'End Date') {
                        $endDate = $option['value'];
                    }
                }
            }
        }
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $initialFee = round($this->storeManager->getStore()->getBaseCurrency()
        ->convert($baseInitialFee, $currentCurrencyCode), 2);
        $description = implode(', ', $itemNameArray);
        $descriptionPlanInfo = [
            'Start Date' => $startDate,
            'Initial Fee' => $initialFee,
            'Subscription Charge' => $subscriptionsAmt];
        return [$description, $startDate, $endDate, $subscriptionsAmt, $initialFee, $planId, $descriptionPlanInfo];
    }

    /**
     * This function return the duration of the plan
     *
     * @param integer $planId
     * @return integer
     */
    private function getFrequency($planId)
    {
        $typeId         = $this->plans->load($planId)->getType();
        $terms  = $this->term->create()->load($typeId);
        $result = ['interval' => $terms->getDurationType(), 'interval_count' => $terms->getDuration()];
        return $result;
    }
}
