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
namespace Webkul\Recurring\Helper;

use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Webkul Recurring Helper Paypal
 */
class Paypal extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const  MOD_ENABLE       = "recurring/general_settings/enable";
    public const  SANDBOX          = "payment/recurringpaypal/sandbox";
    public const  USERNAME         = "payment/recurringpaypal/api_username";
    public const  PASSWORD         = "payment/recurringpaypal/api_password";
    public const  CLIENT_ID        = "payment/recurringpaypal/client_id";
    public const  SECRET_KEY       = "payment/recurringpaypal/secret_key";
    public const  SIGNATURE        = "payment/recurringpaypal/api_signature";
    public const  URL              = "https://api-m.";
    public const  URL_COMPLETE     = "paypal.com/v1/";
    public const  PAYPAL_STATUS    = 'ManageRecurringPaymentsProfileStatus';
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Laminas\Uri\Uri
     */
    protected $uri;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Laminas\Uri\Uri $uri
     * @param  \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Laminas\Uri\Uri $uri,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        WriterInterface $configWriter
    ) {
        $this->curl                     = $curl;
        $this->uri                      = $uri;
        $this->configWriter             = $configWriter;
        $this->json                     = $json;
        $this->encryptor                = $encryptor;
        parent::__construct($context);
    }

    /**
     * This function returns the recurring cancel url
     *
     * @param integer $isSandBox
     * @return string
     */
    protected function getRecurringCancelUrl($isSandBox)
    {
        return self::URL.(($isSandBox) ? "sandbox." : "").self::URL_COMPLETE;
    }

    /**
     * This function will return the every configuration field value.
     *
     * @param string $field
     * @return string
     */
    public function getConfig($field)
    {
        return  $this->scopeConfig->getValue(
            $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * This function is used to return the paypal credentials
     *
     * @return array
     */
    private function getCredentials()
    {
        $isSandBox          = $this->getConfig(self::SANDBOX);
        $userName           = $this->getConfig(self::USERNAME);
        $password           = $this->getConfig(self::PASSWORD);
        $signature          = $this->getConfig(self::SIGNATURE);
        return [
            $isSandBox, $userName, $password, $signature
        ];
    }

    /**
     * This model is used to cancel the paypal recurring payment
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @return bool
     */
    public function cancelSubscriptions($model)
    {
        list($isSandBox, $userName, $password, $signature) = $this->getCredentials();
        $endPointUrl = $this->getRecurringCancelUrl($isSandBox)."billing/subscriptions/".
        $model->getRefProfileId()."/cancel";
        $postData = [
            "id"         => $model->getRefProfileId(),
            "reason"     => "customer cancels the subscription"
        ];
        $postData = $this->jsonEncode($postData);
        $accessToken = $this->getAccessToken();
        $headerData = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Authorization" => "Bearer ". $accessToken
        ];
        $this->curl->setHeaders($headerData);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        if ($response == "") {
            return true;
        }
        return false;
    }
    
    /**
     * This model is used to hold the paypal recurring payment
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @return bool
     */
    public function holdSubscriptions($model)
    {
        list($isSandBox, $userName, $password, $signature) = $this->getCredentials();
        $endPointUrl = $this->getRecurringCancelUrl($isSandBox)."billing/subscriptions/".
        $model->getRefProfileId()."/suspend";
        $postData = [
            "id"         => $model->getRefProfileId(),
            "reason"     => "customer hold the subscription"
        ];
        $postData = $this->jsonEncode($postData);
        $accessToken = $this->getAccessToken();
        $headerData = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Authorization" => "Bearer ". $accessToken
        ];
        $this->curl->setHeaders($headerData);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        if ($response == "") {
            return true;
        }
        return false;
    }

    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        $isSandBox = $this->getConfig(self::SANDBOX);
        $clientId  = $this->encryptor->decrypt($this->getConfig(self::CLIENT_ID));
        $secretKey = $this->encryptor->decrypt($this->getConfig(self::SECRET_KEY));
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
        $responseData = $this->getParsedString($response);
        $responseData = $this->jsonDecode($response);
        return $responseData['access_token'];
    }

    /**
     * This model is used to resume the paypal recurring payment
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @return bool
     */
    public function resumeSubscriptions($model)
    {
        list($isSandBox, $userName, $password, $signature) = $this->getCredentials();
        $endPointUrl = $this->getRecurringCancelUrl($isSandBox)."billing/subscriptions/".
        $model->getRefProfileId()."/activate";
        $postData = [
            "id"         => $model->getRefProfileId(),
            "reason"     => "customer hold the subscription"
        ];
        $postData = $this->jsonEncode($postData);
        $accessToken = $this->getAccessToken();
        $headerData = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Authorization" => "Bearer ". $accessToken
        ];
        $this->curl->setHeaders($headerData);
        $this->curl->post($endPointUrl, $postData);
        $response = $this->curl->getBody();
        if ($response == "") {
            return true;
        }
        return false;
    }

    /**
     * Check paypal details are valid or not
     *
     * @return bool
     */
    public function checkPaypalDetails()
    {
        list($isSandBox, $userName, $password, $signature) = $this->getCredentials();
        
        if (!$this->getConfig(self::MOD_ENABLE)) {
            return false;
        }
        if ($userName != "" && $password != "" && $signature != "") {
            return true;
        }
        return false;
    }

    /**
     * This function parses a query string into variables
     *
     * @param string $response
     * @return array
     */
    public function getParsedString($response)
    {
        $parsedResponse = $this->uri->setQuery($response);
        $responseData = $parsedResponse->getQueryAsArray();
        return $responseData;
    }

    /**
     * Set config data
     *
     * @param String $path
     * @param String $value
     */
    public function setData($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = 'default', $scopeId = 0);
    }

    /**
     * This function will return json encoded data
     *
     * @param  array $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->json->serialize($data);
    }

    /**
     * This function will return json decode data
     *
     * @param  string $data
     * @return array
     */
    public function jsonDecode($data)
    {
        return $this->json->unserialize($data);
    }
}
