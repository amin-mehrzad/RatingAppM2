<?php

namespace RatingApp\Rate\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use RatingApp\Rate\Helper\Data;

class RefreshToken extends Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'RatingApp_Rate::system/config/refresh_token.phtml';
    protected $helper;


    /**
     * @param  Context     $context
     * @param  array       $data
     */
    public function __construct(
        Data $helper,
        Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getRefreshToken()
    {
        $refreshtoken= $this->helper->ratingApp_refreshToken();
        return $refreshtoken;
    }
}
