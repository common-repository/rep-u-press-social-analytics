<?php
/**
 * 
 * Class responsible for loading plugin modules
 * 
 */
class Smaasb_Module_Loader 
{
	/**
	 *  Collection of modules to load
	 */
	private $modules;

	/**
	 *  Register new module to load
	 */
	public function register_module($path, $url, $name)
	{		
		// Determine path to module
		$module_file = $path . 'modules/' . $name .'/'. $name .'.php';

		// Check if module exists
		if (!file_exists($module_file))
		{
			return false;
		}
		
		// Include module
		include($module_file);

		// Add to the collection
		$this->modules[] = array('module'=>$name, 'path'=>$path, 'url'=>$url);
		
	}	

	/**
	 * Initialize registered modules
	 */
	public function init_modules()
	{
		// Iterate through registered modules
		foreach ($this->modules as $module)
		{
			// Determine class name of the module
			$class_name = 'Smaasb_'.ucwords($module['module']);

			// Initialize module
			$obj = new $class_name($module);
			$obj->init();
		}
	}

}