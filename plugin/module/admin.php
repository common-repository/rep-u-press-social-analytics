<?php
/**
 * 
 * Parent class for modules in wp-admin, provides some common functionalities
 * 
 */
abstract class Smaasb_Module_Admin
{
	/**
	 * The method is called when accesing module page (initialize)
	 */
	abstract function load();

	/**
	 * The method is called when accesing module page (render)
	 */	
	abstract function render();
	
	/**
	 * Collection of options of current module
	 */
	protected $options;

	/**
	 * Initialize basic options while constructing class by Module Loader
	 */
	public function __construct(array $options)
	{
		$this->options = $options;
	}

	/**
	 * Reister module page in WP sidebar submenu
	 * For options you can refer to http://codex.wordpress.org/Function_Reference/add_submenu_page
	 */
	protected function register_page(array $options)
	{
		// Merge passed options with the ones from module loader
		$this->options = array_merge($this->options, $options);

		// Use page title as menu item, if menu item not provided
		$this->options['menu'] = $this->options['menu'] ?: $this->options['title'];

		// Generate slug for this view
		$this->options['slug'] = sanitize_title($this->options['menu']);

		// Register page in Wordpress
		add_action("admin_menu", array($this, 'register_page_callback'));
	}

	/**
	 *  Enqueue JS script using WP helpers
	 */
	protected function enqueue_script($handle, $src = null, $deps = null)
	{
		
		// Automatically add local path to /javascript for local .js files
		if (isset($src) && !strstr($src, '//'))
		{
			$src = $this->options['url']. '/javascript/'. $src;
		}

		$page = $this->options['page'];

		add_action('admin_enqueue_scripts', function($hook) use ($handle, $src, $deps, $page) {

			// Enqueue this script only on the module admin view
			if ($hook === $page)
			{
				wp_enqueue_script($handle, $src, $deps);
			}
		});
	}

	/**
	 *  Enqueue CSS stylesheet using WP helpers
	 */
	protected function enqueue_style($handle, $src = null, $deps = null)
	{
		
		// Automatically add local path to /css for local .js files
		if (isset($src) && !strstr($src, '//'))
		{
			$src = $this->options['url']. '/css/'. $src;
		}

		$page = $this->options['page'];

		add_action('admin_enqueue_scripts', function($hook) use ($handle, $src, $deps, $page) {

			// Enqueue this script only on the module admin view
			if ($hook === $page)
			{
				wp_enqueue_style($handle, $src, $deps);
			}
		});
	}

	public function register_page_callback()
	{
		// Add module page to submenu
		$this->options['page'] = add_submenu_page($this->options['parent'], $this->options['title'] , $this->options['menu'], $this->options['capability'], $this->options['slug'], array($this, 'render'));

		// Register loaded function when module page is accessed
		add_action('load-'.$this->options['page'],  array($this, 'load'));		
	}
}