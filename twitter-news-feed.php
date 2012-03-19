<?php
    
    /*
    Plugin Name: Twitter News Feed
    Plugin URI: http://www.keirwhitaker.com/
    Description: Allows users to pull out searches for a Twitter powered news feed
    Version: 1.1.1
    Author: Keir Whitaker
    Author URI: http://www.keirwhitaker.com
    */

    // Twitter News Feed Version
    $tnf_version = "1.1.1";
    
    // Require the PHP5 class
    require_once('twitter-news-feed-class.php');
    
    /**
     * Wrapper function to be called from a WP template
     * Get the news feed as numerically indexed array
     *
     * Returns an numerically indexed array to theme template in the following format
     *
     * [user_id]                => (string e.g 1 - needs to be cast to int if required)
     * [date]                   => (Y-m-d H:i:s)
     * [link]                   => (string e.g http://twitter.com/keirwhitaker/status/2784830708)
     * [id]                     => (string e.g 2784830708 - the actual status id of the tweet)
     * [description]            => (string - The unfiltered tweet which will include the #tag(s))
     * [description_filtered]   => (string - The filtered tweet)
     * [twitter_username_link]  => (String - href of the Twitter username)
     *
     * @return Numerically indexed array containing collected tweets
     **/
    function tnf_get_news_as_array() {
        return tnf_get_news($add_news_to_db = FALSE);
    }
    
    /**
     * Get the news feed
     *
     * Returns an numerically indexed array either to 
     * tnf_get_news_as_array() or an integer value with 
     * number of posts added to the database for use in 
     * the admin screen
     *
     * @return Numerically indexed array containing collected tweets
     **/
    function tnf_get_news($add_news_to_db = TRUE) {
    
        // Globalise the WP database class
        global $wpdb;
        
        // Create new TwitterNewsFeed object
        $tnf = new TwitterNewsFeed($wpdb);
        
        // Return the Tweets to template or admin screen
        $retval = $tnf->get_news_feed($add_news_to_db);
        
        // Update the log
        $comment = "Twitter News Feed ran at: ".date("l jS \of F Y - H:i:s", time());
        $sql = "INSERT INTO ".$wpdb->prefix."tnf_log"." (time, comment) VALUES ('".time()."','".$wpdb->escape($comment)."')";
        $wpdb->query($sql);
        
        return $retval;     
    }

    /**
     * Add the Twitter News Feed admin screen to the WP settings menu
     **/
    add_action('admin_menu', 'tnf_admin_actions');
     
    /**
     * Add the Twitter News Feed link to 
     *
     * Returns an numerically indexed array either to 
     * a theme template or the admin screen
     *
     * @return  Numerically indexed array containing collected tweets
     **/
    function tnf_admin_actions() {
	    add_options_page("Twitter News Feed", "Twitter News Feed", 1, "twitter-news-feed-admin", "tnf_admin");
	};
	
	/**
     * Callback function to include the Twitter News Feed Admin panel
     *
     * @return  None
     **/
	function tnf_admin() {
	    include('twitter-news-feed-admin.php');
	} 
	
	/**
     * On plugin activation add the sudo cron job
     **/
    register_activation_hook(__FILE__, 'tnf_activation');
    add_action('tnf_hourly_update_action', 'tnf_hourly_update');
    
    /**
     * Schedule the hourly update event and install the log table
     *
     * @return  None
     **/
    function tnf_activation() {
    	wp_schedule_event(time(), 'hourly', 'tnf_hourly_update_action');
    	tnf_db_install($tnf_version);
    }

    /**
     * Callback function for scheduled event
     *
     * @return  None
     **/
    function tnf_hourly_update() {
        if(get_option("tnf_add_news_to_db")) {
    	    tnf_get_news(TRUE);
    	};
    }
    
    /**
     * On deactivation of plugin clear the scheduled event
     **/
    register_deactivation_hook(__FILE__, 'tnf_deactivation');

    /**
     * Callback function for plugin deactivation
     *
     * @return  None
     **/
    function tnf_deactivation() {
    	wp_clear_scheduled_hook('tnf_hourly_update_action');
    }

    /**
     * Adds the tnf_log table to the database
     *
     * @return  None
     **/
    function tnf_db_install($tnf_version) {
        global $wpdb;
        global $tnf_db_version;
        
        $table_name = $wpdb->prefix."tnf_log";
        if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

            $sql =  "CREATE TABLE " . $table_name . " (
    	            id mediumint(9) NOT NULL AUTO_INCREMENT,
    	            time bigint(11) DEFAULT '0' NOT NULL,
                	comment text NOT NULL,
                	UNIQUE KEY id (id)
    	            );";

          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          dbDelta($sql);
          add_option("tnf_version", $tnf_version);
       };
    }       
?>