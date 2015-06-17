<?php

class ChargeBee_Request
{
	
	const GET = "get";
	
	const POST = "post";
	
	public static function send($method, $url, $params = array(), $env = null)
	{
		if(is_null($env))
		{
			$env = ChargeBee_Environment::defaultEnv();
		}
		if(is_null($env))
		{
			throw new Exception("ChargeBee api environment is not set. Set your site & api key in ChargeBee_Environment::configure('your_site', 'your_api_key')");
		}
		$ser_params = ChargeBee_Util::serialize($params);
		$response = ChargeBee_Curl::doRequest($method, $url, $env, $ser_params);
		if(is_array($response) && array_key_exists("list", $response))
		{
			return new ChargeBee_ListResult($response['list']);
		} else {
			return new ChargeBee_Result($response);
		}
	}
	
}

?>