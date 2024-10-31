<?php
 /**
 * Get social shares statistic from Google Plus
 */
class Smaasb_Googleplus implements Smaasb_Service_Interface
{
	public function count($url)
	{
		$url = urlencode($url);
		$response = wp_remote_get("https://apis.google.com/u/0/_/+1/fastbutton?url=".$url);
  		
  		if (is_wp_error($response))
  		{
  			return 0;
  		}

		preg_match_all('/window\.__SSR\s\=\s\{c:\s(\d+?)\./', $response['body'], $match, PREG_SET_ORDER);
    	return (1 === sizeof($match) && 2 === sizeof($match[0])) ? intval($match[0][1]) : 0;	
	}
}