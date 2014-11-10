<?php
/**
 * Plugin Name: Issuu Embed: The Official Plugin
 * Plugin URI: http://issuu.com
 * Description: This plugin simplifies the embedding of Issuu publications in blog posts, allowing you to copy/paste any publication URL from your browser into your blog post.
 * Version: 2.0
 * Author: Issuu
 * Author URI: http://issuu.com
 * License: GPL2
 */

function add_issuu_oembed_provider(){
  wp_oembed_add_provider('#https?://(www\.)?issuu\.com/.+/docs/.+#i', 'http://issuu.com/oembed_wp', true);
}

add_action('init', 'add_issuu_oembed_provider');