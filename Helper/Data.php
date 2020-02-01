<?php

namespace RatingApp\Rate\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $_reportCollectionFactory;
    protected $configurable;
    protected $grouped;

    const XML_PATH_RATING_APP_USERNAME = 'ratingapp_tab/ratingapp_setting/ratingapp_username';
    const XML_PATH_RATING_APP_PASSWORD = 'ratingapp_tab/ratingapp_setting/ratingapp_password';
    const XML_PATH_RATING_APP_TOKEN = 'ratingapp_tab/ratingapp_setting/token';
    const XML_PATH_RATING_APP_REFRESH_TOKEN = 'ratingapp_tab/ratingapp_setting/refresh_token';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory $reportCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        Magento\GroupedProduct\Model\Product\Type\Grouped $grouped

    ) {
        $this->_reportCollectionFactory = $reportCollectionFactory;
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
    }
    public function ratingApp_username()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_RATING_APP_USERNAME);
    }

    public function ratingApp_password()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_RATING_APP_PASSWORD);
    }

    public function ratingApp_token()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_RATING_APP_TOKEN);
    }

    public function ratingApp_refreshToken()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_RATING_APP_REFRESH_TOKEN);
    }

    public function getParentId($childId)
    {
        /* for simple product of configurable product */
        $product = $this->configurable->getParentIdsByChild($childId);
        if (isset($product[0])) {
            return $product[0];
        }

        /* for simple product of Group product */
        $parentIds = $this->grouped->getParentIdsByChild($childId);
        /* or for Group/Bundle Product */
        $product->getTypeInstance()->getParentIdsByChild($childId);
    }



    
    // public function ratingApp_refreshToken()
    // {
    //     $username = $this->ratingApp_username();
    //     $password = $this->ratingApp_password();

    //     $url = 'http://amin.ngrok.io/API/users/authenticate';

    //     $ch = curl_init($url);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    //         'name' => 'Amin3',
    //         'email' => 'amin@amin.com',
    //         'password' => '123456')
    //     ));
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     error_log($response);
    //     $result = json_decode($response, true);
    //     $refreshToken = trim(json_encode($result['data']['refreshToken'], true),'"');

    //     return $refreshToken;
    //     // return $response;
    // }
}
