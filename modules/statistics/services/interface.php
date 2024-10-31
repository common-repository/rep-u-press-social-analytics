<?php
/**
 * 
 * Interface for each social service within the plugin
 * 
 */
interface Smaasb_Service_Interface
{

	/**
	 * Each service should return count for specific URL
	 */
	public function count($url);
}