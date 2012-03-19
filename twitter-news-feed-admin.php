<?php 

	/**
	 * Create Clean String
	 *
	 * Returns a clean comma separated array with no
	 * trailing comma and all white space removed
	 *
	 * @return string
	 **/
	function create_clean_string($str) {
	    $myarr = explode(",", $str);
	    foreach ($myarr as $key => $value) {
            $value = trim($value);
            $value = str_replace(" ", "", $value);
            if (is_null($value) || $value=="") {
                unset($myarr[$key]);
            } else {
                $myarr[$key] = $value;
            };
        };
        $myarr = ( implode(",", array_values($myarr))  );    
        return $myarr;
	}
	
	// Process $_POST for form id="tnf_options_form"
	if($_POST["tnf_hidden"] == "Y") {
		
	    $hashtags   = create_clean_string($_POST["tnf_hashtags"]);
		$usernames  = create_clean_string($_POST["tnf_usernames"]);
		$category   = $_POST["tnf_category"];
		$user       = $_POST["tnf_user"];
    	
    	if($_POST["tnf_add_news_as_post"] == "Y") {
    	    $add_news_to_db = "checked";
            $add_news_to_db_boolean = TRUE;
    	} else {
    	    $add_news_to_db = "";
            $add_news_to_db_boolean = FALSE;
    	};

    	if($hashtags !="" && $usernames !="") {
    	
        	update_option("tnf_hashtags", $hashtags);
        	update_option("tnf_usernames", $usernames);
        	update_option("tnf_category", $category);
        	update_option("tnf_user", $user);
        	update_option("tnf_add_news_to_db", $add_news_to_db_boolean);
        	
            $flash_success = "Twitter News Feed Options Updated";
        } else {
            $flash_error = "Please enter at least one Hashtag and one Twitter username";
        };
        
	} else {
		
		// Get the options from the wp-options table using get_option($key);
		$hashtags = get_option("tnf_hashtags");
		$usernames = get_option("tnf_usernames");
		$exceptions = get_option("tnf_exceptions");
		$category = get_option("tnf_category");
		$user = get_option("tnf_user");
	    
	    // Add news as post?
	    if(get_option("tnf_add_news_to_db")) {
	        $add_news_to_db = "checked";
	    };
	};
	
	// Process $_POST for form id="tnf_get_news_form"
	if($_POST["tnf_get_news_hidden"] == "Y" && get_option("tnf_add_news_to_db")) {
	    $retval = tnf_get_news(TRUE);
	    $flash_success = $retval." Tweet(s) added as Posts";
	};    
	
	// If tnf_add_news_to_db == TRUE get the last 10 log entries
	if(get_option("tnf_add_news_to_db")) {
        global $wpdb;
        $sql = "SELECT id, time, comment FROM ".$wpdb->prefix."tnf_log"." ORDER BY time DESC LIMIT 5";
        $data = $wpdb->get_results($sql);        
	};
?>

<?php if(!$flash_success==""): ?>
<div class="updated">
    <p><?php _e($flash_success); ?></p>
</div>
<?php endif ?>

<?php if(!$flash_error==""): ?>
<div class="error">
    <p><?php _e($flash_error); ?></p>
</div>
<?php endif ?>

<div class="wrap">

<div id="icon-options-general" class="icon32"><br /></div>
<h2>Twitter News Feed Admin</h2>

<form name="tnf_options_form" method="post" action="<?php echo str_replace( "%7E", "~", $_SERVER["REQUEST_URI"]); ?>">
	<input type="hidden" name="tnf_hidden" value="Y">
	<h3>Settings</h3>
	<table class="form-table">
	    <tr>
	        <th><label for="tnf_hashtags">Hashtag(s):</label></th>
	        <td><input type="text" name="tnf_hashtags" id="tnf_hashtags" value="<?php echo $hashtags; ?>" size="40"></input>&nbsp;<em>ex: news, events</em></input>
	    </tr>
	    <tr>
	        <th><label for="tnf_usernames">Twitter Username(s):</label></th>
	        <td><input type="text" name="tnf_usernames" id="tnf_usernames" value="<?php echo $usernames; ?>" size="40">&nbsp;<em>ex: keirwhitaker, mikekus, gelimehouse</em></td>
	    </tr>
	    <tr>
	        <th><label for="name="tnf_add_news_as_post">Add Tweets as Posts:</label></th>
	        <td><input type="checkbox" name="tnf_add_news_as_post" id="tnf_add_news_as_post" value="Y" id="tnf_add_news_as_post" <?php echo $add_news_to_db ?>></input>
	    </tr>
	    <tr>
	        <th><label for="name="tnf_category">Post Category:</label></td>
	        <td><?php
                    $dropdown_options = array("show_option_all" => __("View all categories"), "hide_empty" => 0, "hierarchical" => 1, "show_count" => 1, "orderby" => "name", "name" => "tnf_category", "selected" => $category);
                    wp_dropdown_categories($dropdown_options);
                ?>
            </td>
            <tr>
                <th><label for="name="tnf_user">Default User:</label></td>
                <td><?php 
                        $dropdown_options = array("name" => "tnf_user", "selected" => $user);
                        wp_dropdown_users($dropdown_options); 
                    ?>
                </td>
            </tr>
	    </tr>
	</table>
	<p class="submit"><input type="submit" name="Submit" value="Update Settings" /></p>
</form>	

<?php if(get_option("tnf_add_news_to_db")): ?>		    
<form name="tnf_get_news_form" method="post" action="<?php echo str_replace( "%7E", "~", $_SERVER["REQUEST_URI"]); ?>">
    <input type="hidden" name="tnf_get_news_hidden" value="Y">
    <h3>Update History</h3>
    <ul>
    <?php foreach ($data as $item) : ?>
        <li><? echo $item->comment; ?></li>
    <?php endforeach; ?>
    </ul>
    <p><em>...The next update is scheduled for <?php echo date("l jS \of F Y - H:i:s", wp_next_scheduled("tnf_hourly_update")); ?></em></p>
	<p class="submit"><input type="submit" name="tnf_get_news" value="Run Twitter News Feed Update" /></p>
<?php endif ?>	
</form>

</div>