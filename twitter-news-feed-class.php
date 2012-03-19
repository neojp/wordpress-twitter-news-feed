<?php    
    /**
     * Twitter News Feed
     *
     * PHP5 Class to query the Twitter search API for
     * tweets with given hashtags from given Twitter users
     *
     * @author Keir Whitaker
     **/
    class TwitterNewsFeed
    {
        /**
         * Twitter News Feed options
         *
         * @var array
         **/
        var $options;
    
        /**
         * Twitter search query string
         *
         * @var string
         **/
        var $search_query;
    
        /**
         * SimplePie feed
         *
         * @var SimplePie object
         **/
        var $feed;
    
        /**
         * WordPress Database object
         *
         * @var wpdb object
         **/
        var $wpdb;
    
        function __construct($wpdb)
        {
            // Check for the existance of SimplePie
            if (!class_exists("SimplePie")) {
            	require_once("simplepie/simplepie.inc");
            };
     
            // Set up the internal class variables
            $this->wpdb = $wpdb;    
            $this->get_options();
            $this->get_search_query();       
        }
    
        /**
         * Return an array of consumed Tweets from the RSS feed
         *
         * @access public
         * @return array
         **/
        public function get_news_feed($add_news_to_db = TRUE) {
        
            // Use SimplePie to get the RSS feed
            $feed = new SimplePie();
            $feed->set_feed_url(array($this->search_query));
            $feed->set_item_limit(50);
            $feed->handle_content_type();
            $feed->enable_cache(false);
            $feed->init();
        
            // Get the feed and create the SimplePie feed object
            $this->feed = $feed->get_items();
            $post = array();
        
            // Array to hold all the tweet info for returning as an array
            $retval = array();
        
            // Set up two counters (1 for use in the return array and 1 for counting the number of inserted posts if applicable)
            $n = 0;
            $i = 0;
         
            // Array to hold the stored hashtags 
            $hashes = explode(',', $this->options["hashtags"]);
        
            foreach ($feed->get_items() as $item) {
            
                // Get the Twitter status id from the status href
                $twitter_status_id = explode("/", $item->get_id());

                // Check to see if the username is in the user profile meta data
                $post["user_id"] = (int)$this->options["user"];
                $user_id = $this->map_twitter_to_user($twitter_status_id[3]);
                if(!$user_id==NULL) {
                    $post["user_id"] = (int)$user_id;
                };

                // Add individual Tweet data to array
                $post["date"]                   = date("Y-m-d H:i:",strtotime($item->get_date()));
                $post["link"]                   = $item->get_id();
                $post["id"]                     = $twitter_status_id[count($twitter_status_id)-1];
                $post["description"]            = $item->get_description();
                $post["description_filtered"]   = $this->strip_hashes($item->get_description(), $hashes);
                $post["twitter_username"]       = $twitter_status_id[3];
                $post["twitter_username_link"]  = $this->create_twitter_link($twitter_status_id[3]);
            
                // Add the new post to the db?
                if($add_news_to_db) {
                    if($this->add_item_as_post($post)) {
                        $i++;
                    };  
                };
            
                // Add the Tweet to the return array    
                $retval[$n] = $post;
                $n++;
            };
        
            // Return correct values depending on the $add_news_to_db boolean
            if($add_news_to_db) {
                return $i;
            } else {
                return $retval;
            };
        }
    
        /**
         * Strip hash tags (#'s) from the Tweet string
         *
         * @access private
         * @return string
         **/
        private function strip_hashes($tweet, $hashes = array()) {
            if(is_array($hashes)) {
                foreach ($hashes as $hash) {
                    $tweet = str_replace("<a href=\"http://search.twitter.com/search?q=%23".$hash."\"><b>#".$hash."</b></a>", "", $tweet);
                };    
            };
            return $tweet;
        }
    
        /**
         * Create a href to the Twitter users Twitter homepage
         *
         * @access private
         * @return string
         **/     
        private function create_twitter_link($twitter_username) {
            return "<a href=\"http://twitter.com/".$twitter_username."\">@".$twitter_username."</a>";
        }
    
        /**
         * Adds a Tweet to a given WP post category
         * Additionally adds post meta data to the post
         *
         * @access private
         * @return boolean
         **/  
        private function add_item_as_post($item = array()) {
        
            // Check to see if the post already exists
            $sql = "SELECT id FROM ".$this->wpdb->posts." WHERE post_title='".$item['twitter_username']."-".$item['id']."'";
        
            if($this->wpdb->get_var($sql) == NULL) {

                // It's a new entry so add it to the posts table
                $new_post = array();
                $new_post["post_date"]       = $item["date"]; 
                $new_post["post_title"]      = $item["twitter_username"]."-".$item["id"];
                $new_post["post_name"]       = $item["id"];
                $new_post["post_content"]    = $item["description_filtered"];
                $new_post["post_status"]     = "publish";
                $new_post["post_author"]     = (int)($item["user_id"]);
                $new_post["post_category"]   = array($this->options["category"]);

                // Insert the post into the database and store the id
                $post_id = wp_insert_post($new_post);
            
                // Add custom fields to the post
                add_post_meta($post_id, $meta_key = "status_id", $meta_value=$item["id"], $unique=TRUE);
                add_post_meta($post_id, $meta_key = "status", $meta_value=$item["description"], $unique=TRUE);
                add_post_meta($post_id, $meta_key = "status_href", $meta_value=$item["link"], $unique=TRUE);
                add_post_meta($post_id, $meta_key = "twitter_username", $meta_value=$item["twitter_username"], $unique=TRUE); 
                add_post_meta($post_id, $meta_key = "twitter_username_link", $meta_value=$item["twitter_username_link"], $unique=TRUE);  
            
                return TRUE;
            } else {
                return FALSE;
            };  
        }
    
        /**
         * Checks to see if a given Twitter user id exists in
         * the Jabber field of any of the user profiles
         *
         * @access private
         * @return int || null
         **/
        private function map_twitter_to_user($twitter_username) {
            $sql = "SELECT user_id FROM ".$this->wpdb->usermeta." WHERE meta_key='jabber' AND meta_value='".$twitter_username."'";
            $user_id = $this->wpdb->get_var($sql);
            return $user_id;
        }
    
        /**
         * Get the Twitter News Feed (prefix tnf_) options
         * from wp_options table using get_option($key) WP function
         *
         * @access private
         * @return void
         **/
        private function get_options() {
       
            $options = array();
       
            $options["hashtags"]        = get_option("tnf_hashtags");
           	$options["usernames"]       = get_option("tnf_usernames");
           	$options["exceptions"]      = get_option("tnf_exceptions");
           	$options["category"]        = get_option("tnf_category");
           	$options["user"]            = get_option("tnf_user");
           	$options["add_news_to_db"]  = get_option("tnf_add_news_to_db");
		
    		$this->options = $options;
        }
    
        /**
         * Builds a Twitter API search string
         *
         * @access private
         * @return string
         **/     
        public function get_search_query() {
        
            $search_url = "http://search.twitter.com/search.rss?rpp=50&q=";
            $hashtag_q = explode(",", $this->options['hashtags']);
            $n = 0;
        
            foreach($hashtag_q as $hashtag) {
                $retval .= urlencode("#".$hashtag);
                if(!($n == count($hashtag_q)-1)) {
                    $retval .= "+OR+";   
                }; 
                $n ++;
            };

            $usernames_q = explode(",", $this->options["usernames"]);
            $n = 0;
            foreach($usernames_q as $username) {
                if(!($n == 0)) {
                    $retval .= "+OR+from%3A"; 
                } else {
                    $retval .= "+from%3A"; 
                };   
                $n ++; 
                $retval .= urlencode($username);
            };
        
            $this->search_query = $search_url.$retval;
        }       
    }
    // END class
?>