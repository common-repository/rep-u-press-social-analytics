<?php
/**
 * Class for DB queries for social statistics table
 */	
class Smaasb_Social_Post
{
	/**
 	 * Table's name in DB
 	 */	
	private $table_name;

	/**
	 * Set table's name
	 */	
	public function __construct()
	{
		global $wpdb;
		$this->table_name = $wpdb->prefix . "posts_social"; 
	}
	
	/**
	 * Get statistics data for specific post
	 */
	public function find($post_id)
	{
		global $wpdb;

		// Ensure post_id is correctly set
		$post_id = (int) $post_id;
		if ($post_id == 0)
		{
			return false;
		}

		// In demo mode return fake data
		if (smaasb_is_demo())
		{
			return Smaasb_Social_Post_Demo::find($post_id);
		}

		// Get data row from DB
		$data = $wpdb->get_row('SELECT * FROM '.$this->table_name.' WHERE post_id = '.$post_id);
	
		// Return false if there is no data for selected post
		if ($data === null)
		{
			return false;
		}

		// Sum data for each social media service
		$data->total = $this->calculateTotal($data);

		return $data;
	}

	/**
	 * Insert new row to DB
	 */
	public function insert($post_id, $data)
	{
		global $wpdb;

		$data['post_id'] = (int) $post_id;
		$wpdb->insert($this->table_name, $data);
	}

	/**
	 * Update existing DB row
	 */
	public function update($post_id, $data)
	{
		global $wpdb;

		$post_id = (int) $post_id;
		$wpdb->update($this->table_name, $data, array('post_id' => $post_id));
	}

	/**
	 * Get total shares count for specific post_id with cache
	 * Cache TTL is dynamic (5-10 minutes) to avoid refreshing cache for many posts at the same time
	 */
	public function total($post_id)
	{
		$post_id = (int) $post_id;
		$cache_key = 'smaasb_social_post'.$post_id;
		$cache_group = 'smaasb_total';
		
		$total = wp_cache_get($cache_key, $cache_group);
		if ($total === false)
		{
			$data = $this->find($post_id);
			$total = ($data === false) ? 0 : $data->total;
			wp_cache_set($cache_key, $total, $cache_group, rand(5,10) * 60);
		} 
		return $total;
	}

	/**
	 * Get DB rows for posts published in specific date range
	 */
	public function get_data(DateTime $start, DateTime $end, $limit = 50)
	{
		global $wpdb;

		// In demo mode return fake data
		if (smaasb_is_demo())
		{
			return Smaasb_Social_Post_Demo::get_data(rand(4,10));
		}

		// Get posts count data from Wordpress DB
		$query = $wpdb->prepare(
			'SELECT ID as id, post_title as title, s.*, (facebook+twitter+pinterest+googleplus+linkedin) as total FROM '.$wpdb->posts.' p
			LEFT JOIN '.$this->table_name.' s ON p.ID = s.post_id
			WHERE post_type="post" AND post_status="publish"
			AND DATE(post_date)>="%s"
			AND DATE(post_date)<="%s"
			ORDER BY total DESC, post_date DESC
			LIMIT '.$limit,
			$start->format('Y-m-d'),
			$end->format('Y-m-d')
		);

		$posts = $wpdb->get_results($query);

		// Add additional data for each row
		foreach ($posts as $post)
		{
			// Add post's permalink
			$post->url = get_permalink($post->id);

			// Add post's thumbnail
			$post->thumb = get_the_post_thumbnail($post->id, 'thumbnail');

			// Ensure total count is integer (not null)
			$post->total = (int) $post->total;
		}

		return $posts;
	}


	/**
	 * Sum total shares count
	 */
	private function calculateTotal($data)
	{
		if (!$data)
		{
			return 0;
		}
		
		// Sum each social media service
		return $data->facebook + $data->twitter + $data->linkedin + $data->pinterest + $data->googleplus;
	}
}