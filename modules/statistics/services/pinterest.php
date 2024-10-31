<?php
 /**
 * Get social shares statistic from Pinterest
 */
class Smaasb_Pinterest implements Smaasb_Service_Interface
{
	public function count($url)
	{
		$url = urlencode($url);
		$response = wp_remote_get('http://api.pinterest.com/v1/urls/count.json?callback%20&url=' . $url);
  		
  		if (is_wp_error($response))
  		{
  			return 0;
  		}

  		$data = preg_replace('/^receiveCount\((.*)\)$/', '\\1', $response['body']);		
		$data = json_decode($data, true);

		if (!isset($data['count']))
		{
			return 0;
		}

		return intval($data['count']);
	}
}