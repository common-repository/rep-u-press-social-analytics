<?php
/**
 * 
 * Dashboard module, displays all statistics
 * 
 */
class Smaasb_Dashboard extends Smaasb_Module_Admin implements Smaasb_Module_Interface
{
	/**
	 * Called on module initialization
	 */
	public function init()
	{		

		// Register page in Wordpress
		$this->register_page(array(
			'parent' => 'social-media-analytics-share-buttons',
			'title' => 'Social Analytics',
			'menu' => 'Social Analytics',
			'capability' => 'publish_posts'
		));

		// Add column with total shares statistics for Posts/Pages management
		add_filter('manage_posts_columns', array($this, 'registerColumn'));
		add_action('manage_posts_custom_column', array($this, 'showColumn'));
		add_filter('manage_pages_columns', array($this, 'registerColumn'));
		add_action('manage_pages_custom_column', array($this, 'showColumn'));

		// Set witdh of this column to 10% 
		add_action('admin_head', function() {
			echo '<style>.column-smaasb_shares {width: 10%;}</style>';
		});					

		$url = $this->options['url'];

		// Add required styles on these pages
		add_action('admin_enqueue_scripts', function($hook) use ($url)
		{
			if ($hook == 'edit.php')
			{
				// Load font with icons (font-awesome)
				wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');

				// Load Dashboard CSS styles
				wp_enqueue_style('social-analytics', $url. '/css/'. 'social-analytics.css');
			}
		});		
	}

	/**
	 * Called when module page is being accessed
	 */
	public function load()
	{

		// Determine if CSV export operation should be executed
		if (isset($_GET['export']) && $_GET['export'] == 1)
		{
			$this->exportCSV();
		}

		// Load font with icons (font-awesome)
		$this->enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');

		// Load Dashboard CSS styles
		$this->enqueue_style('social-analytics', 'social-analytics.css');

		// Display Full dashboard
		if (!isset($_GET['post_id']))
		{
			// Load library for date range picker
			$this->enqueue_script('jqueryui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js');
			$this->enqueue_style('jqueryui', 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');			
			$this->enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js');

			// Load Dashboard CSS and JS script
			$this->enqueue_style('smaasb_social_analytics', 'jquery.comiseo.daterangepicker.css');
			$this->enqueue_script('smaasb_social_analytics', 'jquery.comiseo.daterangepicker.min.js', array('jquery'));
		}
		
	}

	/**
	 * Called when module page is being rendered
	 */	
	public function render()
	{
		// Set date range from user's selection
		if (isset($_GET['start']) && isset($_GET['end']))
		{
			$start = new DateTime($_GET['start']);
			$end = new DateTime($_GET['end']);
		}
		else 
		{
			// Default date range - last 7 days
			$start = new DateTime();
			$end = new DateTime();
			$start->modify('-6 days');
		}

		// Get data for specified date range
		$social_post = new Smaasb_Social_Post();
		$data = $social_post->get_data($start, $end);

		// Be sure that at least 5 latest posts in this period were updated
		// This is useful for 1st run or for older posts, not accessed by anyone where plugin was enabled
		$fixed = $this->fixMissingData($data, 5);

		// If data has been changed because of the method above - re-run SELECT query
		if ($fixed === true)
		{
			$data = $social_post->get_data($start, $end);
		}

		// Prepare data which will be passed to the view
		$view_data = array(
			'title' => $this->options['title'],
			'view' => $this->options['module'].'/'.$this->options['module'].'.view.php',
			'start' => $start,
			'end' => $end,
			'data' => $data
		);

		extract($view_data);
		include($this->options['path'] . 'views/layout.php');
	}

	/**
	 * Register "Shares" column for Pages/Posts management
	 */	
	public function registerColumn($columns)
	{		
		add_thickbox();
		$columns['smaasb_shares'] = 'Shares';
		return $columns;
	}

	/*
	 * Display column's content for each row
	 * For posts with >0 shares prepare popup window with detailed statistics
	 */
	public function showColumn($column_name)
	{
		global $post;
		if ($column_name == 'smaasb_shares')
		{
			// Get required data for the post
			$post_id = get_the_ID();			
			$post_title = get_the_title();
			$social_post = new Smaasb_Social_Post();
			$post = $social_post->find($post_id);

			// Display shares count and popup window code
			if (!$post)
			{
				echo 0;
			}
			else {
				echo ($post->total > 0) ? '<a href="#TB_inline?width=600&height=230&inlineId=smaasb_'.$post_id.'" class="thickbox" title="Social Analytics">'.$post->total.'</a>' : '0';
				include($this->options['path'] . 'views/dashboard/popup.view.php');
			}
		}
	}	

	/*
	 * Export posts social shares to CSV format
	 */
	public function exportCSV()
	{
		// Start and end date must be set
		if (isset($_GET['start']) && isset($_GET['end']))
		{
			$start = new DateTime($_GET['start']);
			$end = new DateTime($_GET['end']);

			// Get data for specified date range
			$social_post = new Smaasb_Social_Post();
			$data = $social_post->get_data($start, $end);

			// Filter results for CSV file
			$data = $this->filterCSV($data);

			// Output data in CSV format
			$this->outputCSV($data, 'social_shares_'.$start->format('M_d').'-'.$end->format('M_d'));
		}
		else // User manually removed start and/or end date from the request's URL
		{
			// Raise 404 error
			status_header(404);
			nocache_headers();
			include(get_404_template());
			exit;
		}
	}

	/*
	 * Prepare database results for CSV export:
	 * - Select columns which should be included
	 * - Set specific columns order
	 * - Adds column labels
	 * - Ignore posts without any data 
	 * - Format date value for CSV 
	 */
	private function filterCSV($data)
	{
		// Ensure that we have some data
		if (!is_array($data) || empty($data))
		{
			return false;
		}

		// Determine which columns do we want in CSV and in which order
		$columns = array('title','url','total','facebook','twitter','pinterest','googleplus','linkedin','updated');

		$rows = array();

		// Add 1st row with columns label
		$rows[] = $columns;

		foreach ($data as $post)
		{
			$row = array();

			// Ignore posts without any data
			if (empty($post->updated))
			{
				continue;
			}

			// Include only selected columns and in correct order
			foreach ($columns as $col)
			{
				// Format date for CSV
				if ($col == 'updated')
				{
					$post->$col = date("Y-m-d H:i", $post->$col);
				}
				$row[] = $post->$col;
			}
			$rows[] = $row;
		}

		return $rows;
	}

	/*
	 * Format array as CSV and send it to the browser as $filename.csv
	 */
	private function outputCSV($data, $filename)
	{
		// Set HTTP headers for CSV file
 		header("Content-type: text/csv");
    	header("Content-Disposition: attachment; filename={$filename}.csv");
    	header("Pragma: no-cache");
    	header("Expires: 0");

    	// Write CSV to the output
        $handler = fopen("php://output", 'w');
        foreach($data as $row)
        {
            fputcsv($handler, $row);
        }
        fclose($handler);

        // Do not execute any other Wordpress code - file is already sent
        exit;
    }


	/*
	 * Ensure that at least $max posts from resultset were checked for social shares statistics
	 * Max parameter is to avoid checking too many posts in one request
	 */
    private function fixMissingData($data, $max)
    {

		// Ensure that we have some data
		if (!is_array($data) || empty($data))
		{
			return false;
		}

		// Prepare module responsible for checking stats
		$statistics = new Smaasb_Statistics();

		// How many posts were updated
		$updated = 0;
    	foreach ($data as $row)
    	{
    		// Post has no data at all about social shares
    		if (empty($row->updated))
    		{
    			// Update social shares statistics
    			$statistics->updateSocialCounter($row->id, $row->url, 0);

    			// Do not check more posts than specified in $max parameter
    			++$updated;
    			if ($updated == $max)
    			{    				
    				return true;
    			}
    		}
    	}
    	
    	return ($updated == 0) ? false : true;
    }
	
}