<?php
 /**
 * Get social shares statistic from Twitter
 */
class Smaasb_Twitter implements Smaasb_Service_Interface
{
	public function count($url)
	{
		$url = urlencode($url);
		$response = wp_remote_get("http://urls.api.twitter.com/1/urls/count.json?url=".$url);
  		
  		if (is_wp_error($response))
  		{
  			return 0;
  		}
		
		$data = json_decode($response['body'], true);
		
		if (!isset($data['count']))
		{
			return 0;
		}

		return intval($data['count']);
	}
}