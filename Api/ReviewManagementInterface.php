<?php 
namespace RatingApp\Rate\Api;
 
 
interface ReviewManagementInterface {


	/**
	 * GET for Review api
	 * @param string $param
	 * @return string
	 */
	
	public function getReview($param);
}