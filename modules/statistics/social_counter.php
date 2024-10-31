<?php
require_once 'services/interface.php';

 /**
 * 
 * Class responsible for getting statistics data for specific URL
 * 
 */
class Smaasb_Social_Counter 
{
	
	/**
	 * List of social media
	 */	
	private $services;

	/**
	 * Define and load all social media services
	 */	
	public function __construct()
	{
		$this->services = array('facebook', 'twitter', 'pinterest', 'linkedin', 'googleplus');
		$this->loadServices();
	}	


	/**
	 * Get social shares statistics for specific URL for each social media service
	 */	
	public function count($url)
	{
		$data = array();
		$data['updated'] = time(); // Add information about update time
		foreach ($this->services as $service)
		{
			$class = $this->guessServiceClass($service);
			$social = new $class;
			$data[$service] = $social->count($url);
		}
		return $data;
	}

	/**
	 * Load file with class for services list
	 */	
	private function loadServices()
	{
		foreach ($this->services as $service)
		{
			require_once 'services/'.$service.'.php';
		}
	}

	/**
	 * Generate Class name for specific service
	 */	
	private function guessServiceClass($service)
	{
		return 'Smaasb_'.ucfirst($service);
	}
	
}