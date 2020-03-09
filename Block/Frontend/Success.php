<?php
namespace RatingApp\Rate\Block\Frontend;

class Success extends \Magento\Checkout\Block\Onepage\Success
{

    public function getOrder()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        error_log(print_r($request->getPostValue(), true));
        return $request->getPostValue();
    }

    private function _getOrderData($order)
    {

        $orderItems = $order->getAllItems();

        // Determining Customer Logged-in or is Guest

        // Creating Payload
        return $order_data = array(
            //  "orderData" => $orderObject->getData(),
            "billingData" => $orderObject->getBillingAddress()->getData(),
            "shippingData" => $orderObject->getShippingAddress()->getData(),
            // "itemsData" => $itemsData,
            "customer_id" => $customerId,
        );
        error_log(print_r($data, true));
    }

    public function getSomething()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\RatingApp\Rate\Helper\Data');
        $cookieManager = $objectManager->create('\Magento\Framework\Stdlib\CookieManagerInterface');
        $cookieMetadataFactory = $objectManager->create('\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
        $appToken = $helper->ratingApp_token();

        $authorization = "Bearer " . $appToken;

        // $url = 'https://api03.validage.com/API/emails';
        $url = 'https://reviews-ai.ngrok.io/websiteDirectReview';
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getCustomerIsGuest()) {
            $customerId = null;
            $customerEmail = $order->getBillingAddress()->getEmail();
            $customerFirstname = $order->getBillingAddress()->getFirstname();
            $customerLastname = $order->getBillingAddress()->getLastname();
        } else {
            $customerId = $order->getCustomerId();
            $customerEmail = $order->getCustomerEmail();
            $customerFirstname = $order->getCustomerFirstname();
            $customerLastname = $order->getCustomerLastname();
        }

        $arrayOrderData = array(
            "websiteID" => $helper->ratingApp_getJWTData('websiteID'),
            "customer_Id" => $customerId,
            "customer_email" => $customerEmail,
            "customer_firstName" => $customerFirstname,
            "customer_lastName" => $customerLastname,
            "orderID" => $order->getId(),
            "orderNumber" => $order->getIncrementId(),
            "customer_nickName" => $customerFirstname . ' ' . $customerLastname[0] . '.',
        );

        $jsonOrderData = json_encode($arrayOrderData);

        return $arrayOrderData;
        
        $metadata = $cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(30);
        $cookieManager->setPublicCookie(
            'ratig_app',
            $jsonOrderData,
            $metadata
        );

        //error_log(print_r($arrayOrderData, true));

        // $data = json_encode(_getOrderData($this->_checkoutSession->getLastRealOrder()), true);
        //    $data=http_build_query($this->_email_data);

        $cLength = mb_strlen($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:", 'Content-Type:application/json', "Authorization:{$authorization}", "Content-Length:{$cLength}"));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);

        // Check HTTP status code
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: # OK
                    break;
                default:
                    error_log('Unexpected HTTP code: ' . $http_code);
            }
        }

        curl_close($ch);
        error_log($result);
        error_log($appToken);
        error_log('------------------=====');
        return 'returned something from custom block.';
    }
}
