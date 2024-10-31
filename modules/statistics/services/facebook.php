<?php
 /**
 * Get social shares statistic from Facebook
 */
class Smaasb_Facebook implements Smaasb_Service_Interface
{
	public function count($url)
	{
		$url = urlencode($url);
  		$response = wp_remote_get('http://api.facebook.com/restserver.php?method=links.getStats&urls='.$url); 

  		if (is_wp_error($response))
  		{
  			return 0;
  		}
  		
  		$xml = simplexml_load_string($response['body']);
		$data = array('shares' => $xml->link_stat->total_count);
		
		if (!isset($data['shares']))
		{
			return 0;
		}

		return intval($data['shares']);
	}
}