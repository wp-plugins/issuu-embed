<?php
/**
 * Plugin Name: issuu Embed: The Official Plugin (Deprecated)
 * Plugin URI: http://issuu.com
 * Description: This plugin is now deprecated as WordPress 4.0 and up has this functionality is built-in. It simplifies the embedding of issuu publications in blog posts, allowing you to copy/paste any publication URL from your browser into your blog post.
 * Version: 2.0
 * Author: issuu
 * Author URI: http://issuu.com
 * License: GPL2
 */

function add_issuu_oembed_provider(){
  wp_oembed_add_provider('#https?://(www\.)?issuu\.com/.+/docs/.+#i', 'http://issuu.com/oembed_wp', true);
}

add_action('init', 'add_issuu_oembed_provider');
