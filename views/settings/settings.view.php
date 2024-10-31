<h2><?php echo $title ?></h2>
<form action='options.php' method='post'>
<?php
	settings_fields('smaasb_settings');
	do_settings_sections('smaasb_settings');
	submit_button();
?>
</form>


