<?php
 /**
 * Get social shares statistic from LinkedIn
 */
class Smaasb_Linkedin implements Smaasb_Service_Interface
{
	public function count($url)
	{
		$url = urlencode($url);
		$response = wp_remote_get("https://www.linkedin.com/countserv/count/share?format=json&url=".$url);
  		
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