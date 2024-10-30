<?php
/*
Plugin Name: BuddyPress Tweet Urls
Description: Add tweet buttons to your activity stream posts to share links on twitter using YOURLS custom URL shortener 
Version: 0.1
Author: TomHarrigan
Author URI: http://twitter.com/tomharrigan
License:GPL2
*/

/* Only load the component if BuddyPress is loaded and initialized. */
function bp_example_init() {
	require( dirname( __FILE__ ) . '/bp-tweet-urls.php' );
}

define ( 'BP_TWEET_BUTTON_VERSION', '0.1' );

//Admin Menu
add_action('admin_menu', 'tweet_urls_menu');

function tweet_urls_menu() {
	add_options_page('Tweet Urls Menu Options', 'Tweet Urls', 'manage_options', 'my-unique-identifier', 'tweet_urls_menu_options');
}

function tweet_urls_menu_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	  // variables for the field and option names 
    $opt_name = 'username';
    $opt_pass = 'password';
    $opt_api = 'api_url';
    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name = 'username';
    $data_field_pass = 'password';
    $data_field_api = 'api_url';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    
    $opt_val_pass = get_option( $opt_pass );
    $opt_val_api = get_option( $opt_api );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];
        $opt_val_pass = $_POST[ $data_field_pass ];
        $opt_val_api = $_POST[ $data_field_api ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $opt_pass, $opt_val_pass );
        update_option( $opt_api, $opt_val_api );
        // Put a settings updated message on the screen
?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
<?php

    }
    // Now display the settings editing screen
    echo '<div class="wrap">';
    // header
    echo "<h2>" . __( 'Tweet Urls Settings', 'menu-test' ) . "</h2>";
    
    // settings form
    ?>
<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Username:", 'menu-test' ); ?> 
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">
</p><hr />

<p><?php _e("Password:", 'menu-test' ); ?> 
<input type="text" name="<?php echo $data_field_pass; ?>" value="<?php echo $opt_val_pass; ?>" size="20">
</p><hr />

<p><?php _e("API:", 'menu-test' ); ?> 
<input type="text" name="<?php echo $data_field_api; ?>" value="<?php echo $opt_val_api; ?>" size="20">
</p><hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php

}

/**
 * bp_tweet_button_activity_filter()
 *
 * Adds tweet button to activity stream.
 *
 */
function bp_tweet_urls_activity_filter() {

    //get activity stream post link
$url = bp_get_activity_thread_permalink();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, get_option('api_url'));
curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
curl_setopt($ch, CURLOPT_POSTFIELDS, array(     // Data to POST
    'url'      => $url,
    'keyword'  => $keyword,
    'format'   => $format,
    'action'   => 'shorturl',
    'username' => get_option('username'),
    'password' => get_option('password')
	));
	// Fetch and return content
	$data = curl_exec($ch);
	curl_close($ch);
	$newurl=$data;
	$ttitle = get_the_title();
	if(strlen($ttitle>110)) {
		$ttitle = substr($ttitle, 0,110);
		$ttitle .='…';
	}
	$ttitle .=' ';
	$turl = 'http://twitter.com/home?status='.$ttitle.$newurl;
	echo '<a target="_blank" href="'.$turl.'">Tweet This!</a>';
}
add_action('bp_activity_entry_meta', 'bp_tweet_urls_activity_filter');
/**
 * bp_tweet_button_blog_filter()
 *
 * Adds tweet button to blog posts.
 *
 */
function bp_tweet_urls_blog_filter() {
	
	echo '<span class="twitter-share-blog-button"><a href="http://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></span>';
	
}
add_action('bp_before_blog_single_post', 'bp_tweet_urls_blog_filter');

function bp_tweet_urls_insert_head() {
?>
<style type="text/css">
span.twitter-share-button {
	position: relative;
	top: 6px;
}
.twitter-share-blog-button {
	float: right;
}
</style>
<?php	
}
add_action('wp_head', 'bp_tweet_urls_insert_head');

?>