<?php
/*
Plugin Name: Facebook Status
Plugin URI: http://blog.iantearle.com/
Description: Displays your Facebook Status updates on your web site.
Author: Ian Tearle
Version: 1.1
Author URI: http://www.iantearle.com/
*/

/* 
Stats2Site is a PHP function that parses your Facebook Status RSS feed so it can be displayed on any website. No need to be running Wordpress or Drupal!

Created By: Blake Brannon http://www.blakebrannon.com
Date: 11/18/2007

Usage: 	Just include this file in your page and then call: <?php if(function_exists('put_Stats2Site')){put_Stats2Site();} ?>
		You will need to configure the first section of the file below to add your credentials. To get your Facebook RSS feed URL, see instructions at http://www.blakebrannon.com/2007/11/18/stats2site-add-facebook-status-to-your-website/
		If you are having issues with the function working, check and make sure your FB privacy credentials allow your status feed to be viewed.

*/
if(!class_exists('SimpleXMLObject')){include_once('simplexml.class.php');}

ozone_action('preferences_menu','s2s_config_menu');

function s2s_config_menu()
{
//	if (function_exists("add_submenu_page"))
//		ozone_action("plugins.php", __("Reinvigorate"), __("Reinvigorate"), "manage_options", "config-re_", "re_config");

	?>
	<!-- /*   Stats 2 Site Menu   //===============================*/ -->
	<h3 class="stretchToggle" title="Facebook Status"><a href="#FacebookStatus"><span>Facebook Status</span></a></h3>
    <div class="stretch" id="FacebookStatus">
    <label for="s2s_rss_uri">Facebook Status RSS</label>
    <input type="text" name="s2s_rss_uri" id="s2s_rss_uri" value="<?php echo getOption('s2s_rss_uri'); ?>">
	<?php tooltip('Facebook Status RSS', 'To obtain your status RSS, simply visit http://www.facebook.com/minifeed.php and select Status Feed from the left hand menu.');
	?>
	<label for="s2s_name">Facebook Status Name</label>
    <input type="text" name="s2s_name" id="s2s_name" value="<?php echo getOption('s2s_name'); ?>">
	<?php tooltip('Facebook Status Name', 'Enter the name you would like to prefix your statuses with.');
	?>
	<label for="s2s_numofupdates">Number of Facebook Status Updates</label>
    <input type="text" name="s2s_numofupdates" id="s2s_numofupdates" value="<?php echo getOption('s2s_numofupdates'); ?>">
	<?php tooltip('Number of Facebook Status', 'Enter the number of updates you would like to show.');
	?>
	<label for="s2s_rmnameis">Remove Name</label>
	<input type="hidden" value="false" name="s2s_rmnameis" />
	<input type="checkbox" name="s2s_rmnameis" value="true" <?php echo getOption('s2s_rmnameis') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="s2s_rmnameis" />
	<?php tooltip('Remove Name', 'If checked, this option will remove the "Name" before your updates.');
	?>
	<label for="s2s_showtimestamp">Show Timestamp</label>
	<input type="hidden" value="false" name="s2s_showtimestamp" />
	<input type="checkbox" name="s2s_showtimestamp" value="true" <?php echo getOption('s2s_showtimestamp') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="s2s_showtimestamp" />
	<?php tooltip('Show Timestamp', 'If checked, this option will show the time of the status update.'); 
	?>
	<label for="s2s_showfeedback">Show Feedback</label>
	<input type="hidden" value="false" name="s2s_showfeedback" />
	<input type="checkbox" name="s2s_showfeedback" value="true" <?php echo getOption('s2s_showfeedback') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="s2s_showfeedback" />
	<?php tooltip('Show Feedback', 'If checked, this option will show the feeback link at the end.'); 
	?>
	</div>
	<?php
}



function put_Stats2Site() {
	if(getOption('s2s_rss_uri') == ''){
		$uri = 'http://www.facebook.com/feeds/status.php?id=900005295&viewer=900005295&key=a3a410bf20&format=rss20';
	}else{
		$uri = getOption('s2s_rss_uri');
	}

	// Please edit the variables below. All 8 variables should be specified.
	$url = $uri; // Your Facebook RSS Feed (http://www.facebook.com/feeds/status.php?id=900005295&viewer=900005295&key=a3a410bf20&format=rss20)
	$fbname = getOption('s2s_name'); 		// Facebook name (that matches the above feed).
	$rmnameis = getOption('s2s_rmnameis');		// Boolean that dictates if you would like to remove the "Name is" before your updates (true or false).
	$prefix = '<p>'; 		// HTML Tags to go before your Facebook status. ex. <p>, <ul><li>,...
	$suffix = '</em>'; 		// HTML Tags to go after your Facebook status. ex. </p>, </li></ul>,...
	$numofupdates = getOption('s2s_numofupdates');		// Number of updates you want to display, (1, 2, 3, 4,...).
	$showtimestamp = getOption('s2s_showtimestamp'); 	// Show the time of the status update (true or false).
	$showfeedback = getOption('s2s_showfeedback');	// Show the feeback link at the end (true or false).
	
	// ********************************************************************************************************************************
	// Starting The Code
	
	// Variable needed for the algorithm.
	$firstpass = true;	// Identifies the first loop.
	// Fix numofupdates because it is indexed from zero
	$numofupdates--;
	
	
	// Setup curl
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, 'CULROPT_HEADER', 0);
	
	// Spoof Firefox
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1.1) Gecko/20061223 Firefox/2.0.0.1");
	
	// Begin output buffering  
	ob_start();
	curl_exec ($ch);
	curl_close ($ch);
	
	// Save buffer to string
	$xmlstr = ob_get_contents();
	ob_end_clean();
	
	// Convert string to xml object
	$xml = new SimpleXMLElement($xmlstr);
	
	
	// Create Facebook prefix
	$fbprefix = "<p>". $fbname . "is ";
	
	// Start the loop
	for ($count = 0; $count <= $numofupdates; $count++) { 

	
		// If you want to remove the "Name is"
		if ($rmnameis == 'true') {
			// Remove prefix from facebook status message
			$message = htmlspecialchars(str_replace($fbprefix,null,$xml->channel->item[$count]->title));
		
		}else{
			$message = htmlspecialchars($xml->channel->item[$count]->title);
		}
		//mixed search, mixed replace, mixed subject [, int &count]
		$message = str_replace("Ian","",str_replace('http://', 'http&#58;//',$xml->channel->item[$count]->title));
		
		// If you want to place the timestamp on the update.
		if ($showtimestamp == 'true' && $message != '') {
			
			$timestamp = strtotime($xml->channel->item[$count]->pubDate); // Get the timestamp for the post update.
			
			// Calculate how long it's been since the status was updated (relative).
			$currenttime = time();
			$delta = $currenttime - $timestamp;
			
			// Display how long it's been since the last update.
			$timestampdisplay = "</p><em>Updated ";
			
			// Show days if it's been more than a day.
			if(floor($delta / 84600) > 0) {
				$timestampdisplay .= floor($delta / 84600);
				if(floor($delta / 84600) == 1) { $timestampdisplay .= ' day, '; } else { $timestampdisplay .= ' days, '; }
				$delta -= 84600 * floor($delta / 84600);
			}
			
			// Show hours if it's been more than an hour.
			if(floor($delta / 3600) > 0) {				
				$timestampdisplay .= floor($delta / 3600);
				if(floor($delta / 3600) == 1) { $timestampdisplay .= ' hour, '; } else { $timestampdisplay .= ' hours, '; }
				$delta -= 3600 * floor($delta / 3600);
			}
			
			// Show minutes if it's been more than a minute.
			if(floor($delta / 60) > 0) {	
				$timestampdisplay .= floor($delta / 60);
				if(floor($delta / 60) == 1) { $timestampdisplay .= ' minute ago'; } else { $timestampdisplay .= ' minutes ago'; }
				$delta -= 60 * floor($delta / 60);				
				}else{			
				$timestampdisplay .= $delta;
				if($delta == 1) { $timestampdisplay .= ' second ago'; } else { $timestampdisplay .= ' seconds ago'; }		
				}
				
			
			$message .= $timestampdisplay;
		
		}// End of Timestamp
	
		$echo = "";
		// If no updates are available.
		if ($message == '' && $firstpass) {
			$message = $fbname . ' has no recent status updates.';
			$echo.= $prefix . $message . $suffix;
			break; 
		}
	
		// Set the value of $firstpass to know that at least one update has been posted
		$firstpass = false;
		
		// Capitalize first letter
		$message = ucfirst($message);
		
		// Echo out the status update.
		$echo.= $prefix . $message . $suffix;
		
	}// end of Loop
	// Add feedback link.
	if ($showfeedback == 'true'){
		$echo.= '<p>Media by <a href="http://www.iantearle.com/">Ian Tearle</a></p>';
	}else{
		$echo.= '<!-- Media by <a href="http://www.iantearle.com/">Ian Tearle</a> -->';
		
	}
	return $echo;
}
$putStats2Site = put_Stats2Site();
$putStats2Site = preg_replace('/:/', '&#58;', $putStats2Site);
if(function_exists('add_variable')){
	add_variable('status:'.$putStats2Site, 'header');
}
?>