<?php
namespace RatingApp\Rate\Block\Frontend;

class Success  extends \Magento\Checkout\Block\Onepage\Success
{


    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder()->getId();
    }

    public function getSomething()
    {
        return 'returned something from custom block.';
    }
}