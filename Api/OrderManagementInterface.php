<?php 
namespace RatingApp\Rate\Api;
 
 
interface OrderManagementInterface {


	/**
	 * GET for Order api
	 * @param string $param
	 * @return string
	 */
	
	public function getOrder($param);
}