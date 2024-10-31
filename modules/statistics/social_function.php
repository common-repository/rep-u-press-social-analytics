<?php
/**
 * Function for usage in theme
 */	
function social_shares($post_id = false)
{
	global $post;
	
	// If post_id is not specified, take it from the global $post
    if ($post_id === false)
	{
		$post_id = get_the_ID();
	}

	// Additional check that we have correct post_id
	$post_id = (int) $post_id;
	if ($post_id == 0)
	{
		return false;
	}		

	// Get social shares statistics from model
	$social_post = new Smaasb_Social_Post();
	$data = $social_post->find($post_id);

	// Return data as an object
	return new Smaasb_Social_Share_Public($data);
}


/**
 * Simple class which is being returned for theme authors
 */	
class Smaasb_Social_Share_Public
{
	/**
	 * Statistics data for specified post
	 */		
	private $data;

	/**
	 * Assign statistics data
	 */	
	public function __construct($data)
    {
        $this->data = $data;
    }

	/**
	 * Magic function to return item from statistics data
	 */	
    public function __get($name)
    {    	
   		return (is_object($this->data) && property_exists($this->data, $name)) ? $this->data->$name : 0;
    }

	/**
	 * Return total shares when casted to string
	 */	
	public function __toString()
    {
        return (string) $this->total;
    }
}