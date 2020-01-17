<?php

namespace RatingApp\Rate\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $_reportCollectionFactory;

    const XML_PATH_AGE_VERIFICATION_PUBLIC_KEY = 'ratingapp_tab/ratingapp_setting/ratingapp_publicKey';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory $reportCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig

    ) {
        $this->_reportCollectionFactory = $reportCollectionFactory;
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }
    public function rating_publicKey()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_AGE_VERIFICATION_PUBLIC_KEY);
    }
}
