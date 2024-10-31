<?php
/**
 * Class for email report
 */	
class Smaasb_Social_Email
{
	private $to;
	private $subject;
	private $content;

	/**
	 * Get statistics data for specific post
	 */
	public function content(array $posts, DateTime $start, DateTime $end)
	{
		if (empty($posts))
		{
			return false;
		}

		// Load email template
		$content = file_get_contents(plugin_dir_path( __FILE__ ) . '../../views/weekly_report.html');

		$posts_body = '';	

		// Prepare table with all posts for mail body
		foreach ($posts as $post) {
			$posts_body .= '
				<tr>
					<td style="border-bottom: 1px #ccc solid; padding-top: 5px; padding-bottom: 5px;"><a href="'.$post->url.'" style="text-decoration: none; color: #252525;" target="_blank">'.stripslashes($post->title).'</a></td>
					<td style="border-bottom: 1px #ccc solid; padding-top: 5px; padding-bottom: 5px;" align="center">'.$post->total.'</td>
				</tr>';
		}		

		// Replace template tags with real values
		$variables = array('||DASHBOARD_URL||', '||SETTINGS_URL||', '||DATE_START||', '||DATE_END||', '||POSTS||');
		
		$values = array(
			admin_url('edit.php').'?page=social-analytics',
			admin_url('admin.php').'?page=social-analytics',
			$start->format('jS F Y'),
			$end->format('jS F Y'),
			$posts_body
		);

		$this->content = str_replace($variables, $values, $content);
		return $this->content;
	}

	/**
	 * Set recipient
	 */
	public function to($email)
	{
		$this->to = $email;
	}

	/**
	 * Set subject
	 */
	public function subject($subject)
	{
		$this->subject = $subject;
	}

	/**
	 * Send email
	 */
	public function send()
	{
		 // HTML format
		$headers = array('Content-Type: text/html; charset=UTF-8');

		// Send email
		return wp_mail($this->to, $this->subject, $this->content, $headers);
	}	
}