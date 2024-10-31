<?php
/**
 * Class for generating fake data for plugin's demo
 */	
class Smaasb_Social_Post_Demo
{
	
	/**
	 * Get fake statistics data for specific post
	 */
	public static function find($post_id)
	{
		// Prepare fake data
		$data = new StdClass();
		$data->id = $post_id;
		$data->post_id = $post_id;
		$data->facebook = rand(1,50);
		$data->twitter = rand(1,50);
		$data->linkedin = rand(1,20);
		$data->pinterest = rand(1,20);
		$data->googleplus = rand(1,20);
		$data->updated = time();
		$data->total = $data->facebook + $data->twitter + $data->linkedin + $data->pinterest + $data->googleplus;

		return $data;
	}

	/**
	 * Get fake data for specific amount posts (with photos & titles)
	 */
	public static function get_data($count = 10)
	{
		// Generate fake data
		$posts = array();
		for ($i=0; $i<$count;$i++)
		{
			$posts[] = Smaasb_Social_Post_Demo::find($i);
		}

		// Add additional data for each row
		foreach ($posts as $key=>$post)
		{
			// Add post's permalink
			$post->url = 'http://wp-social-analytics.com';

			// Add post's title
			$post->title = Smaasb_Social_Post_Demo::title($key);

			// Add post's thumbnail
			$post->thumb = Smaasb_Social_Post_Demo::photo();
		}

		// Sort posts by total shares
		usort($posts, function($a, $b) {
			if ($a->total == $b->total) return 0;
    		return ($a->total < $b->total) ? 1 : -1;
		});

		// Return data
		return $posts;
	}	

	/**
	 * Get code for random photo from Lorempixel
	 * Use different width/height to avoid the same photos for each post
	 */
	public static function photo()
	{
		// Topics on lorempixel.com
		$topics = array('technics', 'business', 'transport', 'city');

		// Random width, height and topic
		$width = rand(40,80);
		$height = rand(40,80);
		$topic = $topics[rand(0, count($topics)-1)];

		// Return html code for the image
		return '<img src="http://lorempixel.com/'.$width.'/'.$height.'/'.$topic.'">';
	}

	/**
	 * Get fake title
	 */
	public static function title($key = false)
	{
		// Fake titles to choose from
		$titles = array(
				'Top 10 Responsive Wordpress themes',
				'Recommended plugin: WP Social Analytics',
				'How to build custom solution?',
				'Installing Wordpress for beginners',
				'Optimize your blog in 5 simple steps',
				'Plugin of the week: Review',
				'Which plugins do you need in your website?',
				'Beginners guide for plugin development',
				'15 best themes for your blog',
				'Manage your blog like a pro',
				'How to measure the impact of social media?',
				'Let your users share your posts',
				'Use WP Social Analytics and monitor social media',
			);

		// Get random title if $key is not provided or item with this key doesn't exist
		if ($key === false || !isset($titles[$key]))
		{
			$key = rand(0, count($titles)-1);
		}

		// Return title
		return $titles[$key];
	}
}