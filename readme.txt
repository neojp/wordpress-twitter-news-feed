=== Twitter News Feed ===
Contributors: keirwhitaker
Donate link: http://www.keirwhitaker.com/
Tags: twitter, news
Requires at least: 2.8
Tested up to: 2.8.2
Stable tag: trunk

Collects Tweets based on specific Twitter usernames and hashtags and then adds them as posts

== Description ==

The Twitter News Feed plugin has a specific use case, that is to import tweets from defined Twitter users containing specific hashtags into Wordpress.

**Why Use Twitter News Feed**

Let's say everyone in your company has a Twitter account and you would like their updates to appear on your site but some of them tweet about pizzas and beer as well as some good links and company news. Twitter News Feed allows you to import tweets tagged with certain hashtags from those users. 

For example you might want to import tweets from @johndoe and @janedoe containing the hashtag #news. Twitter News Feed can do this for you. There is no limit to the number of users or hashtags you can add. The main benefit being that you can power your news feed using Twitter as well as allowing the conversation to continue using Wordpress comments.

**Automatically add Tweets as Posts**

Twitter News Feed is designed to add the imported tweets as posts, although you can choose to not add them to the database as posts and just grab them on each page load using a template function. In the admin panel (available from the Settings menu) you are able to specify hashtags, usernames as well as define a category to add your imported tweets to as well as a default user.

When adding Tweets as posts a number of custom fields are added that you are then able to use in your templates. These are:

* status => The full tweet returned from the RSS feed
* status_href => A href to the tweet
* status_id => The status id of the tweet
* twitter_username => The username of the tweets author (NB: keirwhitaker NOT @keirwhitaker)
* twitter_username_link => A href link to the tweet authors homepage (NB: includes the @)

**Use the Jabber field to Map Users to Tweets**

Twitter News Feed goes one step further and will attribute posts to the correct author. It does this by looking in the "jabber" field in the user profile for a Twitter username. Let's say we enter "johndoe" in the jabber field any tweets from @johndoe will be attributed to him. If no match is found the post will be added to the default user defined in the Twitter News Feed admin panel.

**Features**

* Allows multiple Twitter users to add news to a Wordpress blog based on defined hashtags
* Automatically adds Tweets to Wordpress to a defined category
* Maps Twitter usernames to the "jabber" field in the user profile, if not found Tweets are attributed to a "default" user e.g. Twitter News Bot
* Uses Wordpress scheduled events to look for new tweets for every hour
* Provides a template function to grab updates but not store them as posts 

**Support**

If you have any problems with of feedback on Twitter News Feed please email keir[at]keirwhitaker.com

You can also follow me on Twitter - http://twitter.com/keirwhitaker

== Installation ==

1. Upload `twitter-news-feed` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Edit the Twitter News Feed settings (menu item in Settings)

== Requirements ==

1. Twitter News Feed 1.0 requires PHP5
1. Simplepie - http://simplepie.org/ (This is included in the plugin)

== Changelog ==

= 1.1 =
* Fixed an issue with the registration hooks not firing due to incorrectly named actions

= 1.0 =
* Initial release of Twitter News Feed plugin

== Usage ==

There are two ways to display Tweets on your blog.

Firstly, if you choose not to store your Tweets as post you can use the following template tag "tnf_get_news_as_array()" in your templates

`<?php $tweets = tnf_get_news_as_array(); ?>`
`<ul>`
`<?php foreach($tweets as $tweet): ?>`
`<li><?php echo $tweet["description_filtered"]." - ".date("l jS \of F Y - H:i:s"); ?></li>`
`<?php endforeach; ?>`
`</ul>`

The available array elements are as follows:

* user_id => (string e.g 1 - needs to be cast to int if required)
* date => (Y-m-d H:i:s)
* link => (string e.g http://twitter.com/keirwhitaker/status/2784830708)
* id => (string e.g 2784830708 - the actual status id of the tweet)
* description => (string - The unfiltered tweet which will include the #tag(s))
* description_filtered => (string - The filtered tweet)
* twitter_username_link => (String - href of the Twitter username)

The other option is to use Wordpress to pull out posts from the category specified in the Twitter News Feed admin panel. 

See http://codex.wordpress.org/Template_Tags/get_posts for examples.

== Screenshots ==

1. Screenshot of the configuration panel for this plugin.