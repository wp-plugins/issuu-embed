<?php

	/*

		Plugin Name: Issuu Embed
		Plugin URI: http://wordpress.org/extend/plugins/issuu-embed
		Version: 1.0
		
		Author: Tom Lynch
		Author URI: http://tomlynch.co.uk
		
		Description: Issuu Embed allows you to copy and paste the URL of a Issuu document into your blog post and have it automatically embedded just as you would expect with other oEmbedable sites like Vimeo, YouTube, Flickr, and others.
		
		License: GPLv3
		
		Copyright (C) 2011 Tom Lynch

	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
		
	*/

	class IssuuEmbed {
		var $admin_panel_hook;
		
		function __construct() {
			wp_embed_register_handler( 'issuu-embed', '(http://issuu.com/[a-z0-9._-]{1,30}+/docs/[a-z0-9_.-]{3,50})', array( &$this, 'wp_embed_handler' ) );
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				add_filter( 'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'filter_plugin_action_links' ), 10, 2 );				
				add_action( 'network_admin_menu', array( &$this, 'register_network_admin_menu' ) );
			} else {
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'filter_plugin_action_links' ), 10, 2 );
				add_action( 'admin_menu', array( &$this, 'register_admin_menu' ) );
			}
			add_filter( 'contextual_help', array( &$this, 'register_contextual_help' ), 10, 2 );
		}
		
		function filter_plugin_action_links( $links ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				array_unshift( $links, '<a href="' . network_admin_url( 'settings.php' ) . '?page=issuu-embed">Settings</a>' );
			} else {
				array_unshift( $links, '<a href="' . admin_url( 'options-general.php' ) . '?page=issuu-embed">Settings</a>' );
			}
			return $links;
		}
		
		function wp_embed_handler( $matches, $embed_dimensions, $url ) {
			$extras = array();
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( get_site_option( 'issuu-embed-layout' ) == 'presentation' ) $extras['viewMode'] = get_site_option( 'issuu-embed-layout' );
				if ( get_site_option( 'issuu-embed-autoflip' ) == '1' ) $extras['autoFlip'] = 'true';
				if ( get_site_option( 'issuu-embed-flipbuttons' ) == '1' ) $extras['showFlipBtn'] = 'true';
				return get_issuu_embed_code( $url, $embed_dimensions['width'], $extras, get_site_option( 'issuu-embed-theme', 'http://skin.issuu.com/v/light/layout.xml' ) );
			} else {
				if ( get_option( 'issuu-embed-layout' ) == 'presentation' ) $extras['viewMode'] = get_option( 'issuu-embed-layout' );
				if ( get_option( 'issuu-embed-autoflip' ) == '1' ) $extras['autoFlip'] = 'true';
				if ( get_option( 'issuu-embed-flipbuttons' ) == '1' ) $extras['showFlipBtn'] = 'true';
				return get_issuu_embed_code( $url, $embed_dimensions['width'], $extras, get_option( 'issuu-embed-theme', 'http://skin.issuu.com/v/light/layout.xml' ) );
			}
		}
		
		function register_network_admin_menu() {
			$this->admin_panel_hook = add_submenu_page( 'settings.php', 'Issuu Embed', 'Issuu Embed', 'manage_network_options', 'issuu-embed', array( &$this, 'register_options_page' ) );
		}
		
		function register_admin_menu() {
			$this->admin_panel_hook = add_options_page('Issuu Embed', 'Issuu Embed', 'manage_options', 'issuu-embed', array( &$this, 'register_options_page' ) );
		}
		
		function register_options_page() {
			
			// Issuu themes
			// $themes[Option Group][Theme Name] = Theme URL
			$themes['Normal']['Light (default)'] = 'http://skin.issuu.com/v/light/layout.xml';
			$themes['Normal']['Dark'] = 'http://skin.issuu.com/v/dark/layout.xml';
			$themes['Icons only']['Light'] = 'http://skin.issuu.com/v/lighticons/layout.xml';
			$themes['Icons only']['Dark'] = 'http://skin.issuu.com/v/darkicons/layout.xml';
			$themes['Soft']['Light'] = 'http://skin.issuu.com/v/softlight/layout.xml';
			$themes['Soft']['Dark'] = 'http://skin.issuu.com/v/softdark/layout.xml';
			$themes['Ugly']['Wood'] = 'http://skin.issuu.com/v/wood2/layout.xml';
			$themes['Ugly']['Aquarium'] = 'http://skin.issuu.com/v/aquarium/layout.xml';
			$themes['Ugly']['Cartoon'] = 'http://skin.issuu.com/v/cartoon/layout.xml';
			$themes['Ugly']['Nightmare'] = 'http://skin.issuu.com/v/nightmare/layout.xml';
			$themes['Obsolete']['Grey'] = 'http://skin.issuu.com/v/grey/layout.xml';
			$themes['Obsolete']['Wood'] = 'http://skin.issuu.com/v/wood/layout.xml';
			$themes['Obsolete']['White'] = 'http://skin.issuu.com/v/white/layout.xml';
			$themes['Obsolete']['Grass'] = 'http://skin.issuu.com/v/grass/layout.xml';

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'issuu-embed' ) && current_user_can( 'manage_network_options' ) ) {
					if ( isset( $_POST['theme'] ) && in_array_recursive( $_POST['theme'], $themes ) ) {
						update_site_option( 'issuu-embed-theme', $_POST['theme'] );
					} else {
						delete_site_option( 'issuu-embed-theme' );
					}
					if ( isset( $_POST['layout'] ) && $_POST['layout'] == 'presentation' ) {
						update_site_option( 'issuu-embed-layout', $_POST['layout'] );
					} else {
						delete_site_option( 'issuu-embed-layout' );
					}
					if ( isset( $_POST['autoflip'] ) && $_POST['autoflip'] == 'true' ) {
						update_site_option( 'issuu-embed-autoflip', true );
					} else {
						delete_site_option( 'issuu-embed-autoflip' );
					}
					if ( isset( $_POST['flip_buttons'] ) && $_POST['flip_buttons'] == 'true' ) {
						update_site_option( 'issuu-embed-flipbuttons', true );
					} else {
						delete_site_option( 'issuu-embed-flipbuttons' );
					}
					$done = true;
				}
				$current_theme = get_site_option( 'issuu-embed-theme', $themes['Normal']['Light (default)'] );
				$current_layout = get_site_option( 'issuu-embed-layout' );
				$current_autoflip = get_site_option( 'issuu-embed-autoflip' );
				$current_flipbuttons = get_site_option( 'issuu-embed-flipbuttons' );
			} else {
				if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'issuu-embed' ) && current_user_can( 'manage_options' ) ) {
					if ( isset($_POST['theme'] ) && in_array_recursive( $_POST['theme'], $themes ) ) {
						update_option( 'issuu-embed-theme', $_POST['theme'] );
					} else {
						delete_option( 'issuu-embed-theme' );
					}
					if ( isset( $_POST['layout'] ) && $_POST['layout'] == 'presentation' ) {
						update_option( 'issuu-embed-layout', $_POST['layout'] );
					} else {
						delete_option( 'issuu-embed-layout' );
					}
					if ( isset( $_POST['autoflip'] ) && $_POST['autoflip'] == 'true' ) {
						update_option( 'issuu-embed-autoflip', true );
					} else {
						delete_option( 'issuu-embed-autoflip' );
					}
					if ( isset( $_POST['flip_buttons'] ) && $_POST['flip_buttons'] == 'true' ) {
						update_option( 'issuu-embed-flipbuttons', true );
					} else {
						delete_option( 'issuu-embed-flipbuttons' );
					}
					$done = true;
				}
				$current_theme = get_option( 'issuu-embed-theme', $themes['Normal']['Light (default)'] );
				$current_layout = get_option( 'issuu-embed-layout' );
				$current_autoflip = get_option( 'issuu-embed-autoflip' );
				$current_flipbuttons = get_option( 'issuu-embed-flipbuttons' );
			}
			?>
				<div class="wrap">
					<div id="icon-options-general" class="icon32"></div>
					<h2>Issuu Embed</h2>
					<?php if ( ( ( function_exists( 'is_multisite' ) && is_multisite() ) && current_user_can( 'manage_network_options' ) ) || current_user_can( 'manage_options' ) ): ?>
						
						<?php if ( isset( $done ) ): ?>
							<div id="message" class="updated"><p>Options saved.</p></div>
						<?php endif ?>
						
						<?php if ( function_exists( 'is_multisite' ) && is_multisite() ): ?>
							<form method="post" action="settings.php?page=issuu-embed">
						<?php else: ?>
							<form method="post" action="options-general.php?page=issuu-embed">
						<?php endif ?>
							<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'issuu-embed' ) ?>">
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">Theme</th>
										<td>
											<select name="theme">
												<?php foreach ( $themes as $name => $theme ): ?>
													<optgroup label="<?php echo $name ?>">
														<?php foreach ( $theme as $name => $value ): ?>
															<option value="<?php echo $value ?>" <?php echo ( isset( $current_theme ) && $value == $current_theme ? 'selected="selected"' : null ) ?>><?php echo $name ?></option>
														<?php endforeach ?>												
													</optgroup>
												<?php endforeach ?>
											</select>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">Layout</th>
										<td>
											<input name="layout" id="layout_twoup" value="" type="radio" <?php echo ( ! isset( $current_layout ) || ( isset( $current_layout ) || $current_layout != 'presentation' ) ? 'checked="checked"' : null ) ?> />
											<label for="layout_twoup">Two-up</label>
											<input name="layout" id="layout_presentation" value="presentation" style="margin-left: 10px;" type="radio" <?php echo ( isset( $current_layout ) && $current_layout == 'presentation' ? 'checked="checked"' : null ) ?> />
											<label for="layout_presentation">Single page</label>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">Auto flip</th>
										<td>
											<input type="checkbox" name="autoflip" id="autoflip" value="true" <?php echo ( isset( $current_autoflip ) && $current_autoflip == '1' ? 'checked="checked"' : null ) ?> />
											<label for="autoflip">Automatically turn the page every six seconds.</label>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">Flip buttons</th>
										<td>
											<input type="checkbox" name="flip_buttons" id="flip_buttons" value="true" <?php echo ( isset( $current_flipbuttons ) && $current_flipbuttons == '1' ? 'checked="checked"' : null ) ?> />
											<label for="flip_buttons">Always show flip buttons.</label>
										</td>
									</tr>
							
								</tbody>
							</table>
							<p class="submit">
								<input type="submit" class="button-primary" value="Save Changes">
							</p>
						</form>
					<?php else: ?>
						<p>You do not have permission to use Issuu Embed.</p>
					<?php endif ?>
				</div>
			<?php
		}
		
		function register_contextual_help( $help, $screen ) {
			$new_help = '<p>This screen lets you modify the way your Issuu documents are embedded, from here you can customise the Theme, Layout, and enable options like Auto Flip.</p>
				<p>The width of the embedded document is controlled by the Maximum embed size setting in <a href="' . admin_url('options-media.php') . '">Media Settings</a>, the maximum height setting is ignored.</p>
				<p>
					<strong>For more information:</strong>
				</p>
				<p>
					<a href="http://wordpress.org/extend/plugins/issuu-embed" target="_blank">Issuu Embed Homepage</a>
				</p>
				<p>
					<a href="http://wordpress.org/tags/issuu-embed" target="_blank">Issuu Embed Forum</a>
				</p>';
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( $screen == $this->admin_panel_hook . '-network' )
					return $new_help;
			} else {
				if ( $screen == $this->admin_panel_hook )
					return $new_help;
			}
			return $help;
		}
		
	}
	
	if ( class_exists( 'IssuuEmbed' ) ) {
		$IssuuEmbed = new IssuuEmbed();
	}
	
	/* Plugin support functions, you can use these in your code if you wish */

	function get_issuu_embed_code( $url, $target_width, $extra_settings = array(), $skin = 'http://skin.issuu.com/v/light/layout.xml' ) {

		// Get page
		$page_data = file_get_contents( $url );
		
		// Document ID
		preg_match( '/<meta property="og:video" content=".+documentId=([0-9a-z-]+)">/i', $page_data, $document_id );
		
		// Title
		preg_match( '/<title>(.+)<\/title>/i', $page_data, $title );
		
		// Calculate width
		$meta = get_meta_tags( $url );
		$ratio = $meta['video_height'] / $meta['video_width'];
		$width = ceil( $target_width );
		$height = ceil( $target_width * $ratio );

		// Create query string
		$q['documentId'] = $document_id[1];
		$q['loadingInfoText'] = $title[1];
		$q['layout'] = $skin;
		
		$q = array_merge( $q, $extra_settings );
	
		$querystring = 'mode=embed';
	
		foreach ( $q as $key => $value ) {
			$querystring .= '&' . $key . '=' . $value;
		}
		
		$querystring = htmlentities( $querystring );
	
		return '<object style="width:' . $width . 'px;height:' . $height . 'px" ><param name="movie" value="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf?' . $querystring . '" /><param name="allowfullscreen" value="true"/><param name="wmode" value="transparent"/><param name="menu" value="false"/><embed src="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf" wmode="transparent" type="application/x-shockwave-flash" allowfullscreen="true" menu="false" style="width:' . $width . 'px;height:' . $height . 'px" flashvars="' . $querystring . '" /></object>';

	}
	
	function issuu_embed_code( $url, $target_width, $extra_settings = array(), $skin = null ) {
		if ( isset( $skin ) )
		{
			echo get_issuu_embed_code( $url, $target_width, $extra_settings, $skin );
		}
		else
		{
			echo get_issuu_embed_code( $url, $target_width, $extra_settings);
		}
	}
	
	function in_array_recursive( $needle, $haystack ) {
	    $it = new RecursiveIteratorIterator( new RecursiveArrayIterator( $haystack ) );
	    foreach ( $it AS $element )
	        if ( $element == $needle )
	            return true;
	    return false;
	}


?>