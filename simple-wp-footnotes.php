<?php 
/*
Plugin Name: Simple WP Footnotes
Plugin URI: http://plugins.findingsimple.com
Description: Adds footnotes to your WP content.
Version: 1.0
Author: Finding Simple (Jason Conroy & Brent Shepherd)
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2008 - 2012  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once dirname( __FILE__ ) . '/simple-wp-footnotes-admin.php';

if ( ! class_exists( 'Simple_WP_Footnotes' ) ) : 

/**
 * So that themes and other plugins can customise the text domain, the Simple_WP_Footnotes
 * should not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package Simple WP Footnotes
 * @since 1.0
 */
function initialize_wp_footnotes(){
	Simple_WP_Footnotes::init();
}
add_action( 'init', 'initialize_wp_footnotes', -1 ); 

/**
 * Plugin Main Class.
 *
 * @package Simple WP Footnotes
 * @since 1.0
 */
class Simple_WP_Footnotes {

	const TINYMCE_PLUGIN_NAME = 'simple_wp_footnotes';

	private static $footnote_fields;
	
	private static $footnotes = array();
	
	private static $placement = 'content';

	/**
	 * Initialize the class
	 *
	 * @since 1.0
	 */
	public static function init() {
	
		self::$footnote_fields = array(
			'footnote_text'  => __( 'Footnote' )
		);
						
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles_and_scripts'), 100 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles_and_scripts' ) );
		
		add_action( 'admin_footer', array( __CLASS__, 'the_jquery_dialog_markup' ) );
		
		add_filter( 'mce_external_plugins', array( __CLASS__, 'register_tinymce_plugin' ) );

		add_filter( 'mce_buttons', array( __CLASS__, 'register_tinymce_button' ) );
		
		self::$placement = ( get_option('simple_wp_footnotes-placement') ) ? get_option('simple_wp_footnotes-placement') : 'content';

        //if placement is after page_links add after page links 
        if ( 'page_links' == self::$placement )
        	add_filter( 'wp_link_pages_args', array( __CLASS__, 'simple_wp_footnotes_link_pages_args' ) );	
        	
        //filter normal content - if placement is set content or it isn't multipage post it will add to the end      
        add_filter( 'the_content', array( __CLASS__, 'simple_wp_footnotes_filter_the_content' ), 12 );	
		
		add_shortcode( 'footnote', array( __CLASS__, 'simple_wp_footnote_shortcode') );

	}

	/**
	 * Add scripts and styles
	 *
	 * @since 1.0
	 */
	public static function enqueue_styles_and_scripts(){
		
		if ( !is_admin() ) {
		
			if ( get_option('simple_wp_footnotes-toggle-js-include') != 1 )
				wp_enqueue_script( 'simple-wp-footnotes', self::get_url( '/js/simple-wp-footnotes.min.js', __FILE__ ) ,'jquery','1.0',true );
			
			if ( get_option('simple_wp_footnotes-toggle-css-include') != 1 )
				wp_enqueue_style( 'simple-wp-footnotes', self::get_url( '/css/simple-wp-footnotes.min.css', __FILE__ ) );
		
		}
		
	}
	
	/**
	 * Add admin scripts and styles
	 *
	 * @since 1.0
	 */
	public static function enqueue_admin_styles_and_scripts() {
		
		global $pagenow;
		
		if ( is_admin() && $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {

			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
	
			wp_register_style( 'simple-wp-footnotes-admin', self::get_url( '/css/simple-wp-footnotes-admin.css', __FILE__ ) , false, '1.0' );
			wp_enqueue_style( 'simple-wp-footnotes-admin' );
	
			wp_enqueue_script( 'simple-wp-footnotes-admin', self::get_url( '/js/simple-wp-footnotes-admin.js', __FILE__ ) );
	
			wp_localize_script( 'simple-wp-footnotes-admin', 'FootnoteFields', self::$footnote_fields );
		
		}
		
	}
	
	/**
	 * Register TinyMCE button.
	 *
	 * @see wp-includes/class-wp-editor.php
	 * @link http://www.tinymce.com/wiki.php/Buttons/controls
	 * @since 1.0
	 *
	 * @param array $buttons Filter supplied array of buttons to modify
	 * @return array The modified array with buttons
	 */
	public static function register_tinymce_button( $buttons ) {

		array_push( $buttons, 'separator', self::TINYMCE_PLUGIN_NAME );

		return $buttons;
	}

	/**
	 * Register TinyMCE plugin.
	 *
	 * Adds the absolute URL for the TinyMCE plugin to the associative array of plugins. Array structure: 'plugin_name' => 'plugin_url'
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @since 1.0
	 *
	 * @param	array $plugins Filter supplied array of plugins to modify
	 * @return	array The modified array with plugins
	 */
	public static function register_tinymce_plugin( $plugins ) {

		$plugins[ self::TINYMCE_PLUGIN_NAME ] = self::get_url( '/tinymce/editor_plugin.js?ver=1.0', __FILE__ );

		return $plugins;
	}

	/**
	 * Build jQuery UI Window.
	 *
	 * Creates the jQuery for Post Editor popup window and the
	 * form fields to enter variables.
	 *
	 * @since 1.0
	 */
	public static function the_jquery_dialog_markup() {

		$screen = get_current_screen();

		if ($screen->base != 'post')
				return;
?>
<div class="hidden">
	<div id="simple-wp-footnotes" title="Insert Footnote">
		<div id="footnote-details" style="margin: 1em;">

		<?php foreach ( self::$footnote_fields as $field_id => $field_label ) : ?>
		<label for="simple-<?php echo $field_id; ?>" style="display: block; width: 90%; margin: 2px; float:none;">
			<?php echo $field_label; ?>
			<textarea id="simple-<?php echo $field_id; ?>" name="simple-<?php echo $field_id; ?>" style="display: block; width: 98%; float:none;" ></textarea>
		</label>		
		<?php endforeach; ?>

		</div><!-- #footnote-details -->
	</div><!-- #simple-wp-footnotes -->
</div><!-- .hidden -->
<?php
	}
	

	/**
	 * Create Shortcode
	 *
	 * @since 1.0
	 */
	public static function simple_wp_footnote_shortcode( $atts, $content = null ) {
	
			$id = get_the_ID();
			
			// if no shortcode content do nothing
			if ( null === $content )
					return;
			
			// prepare array for footnotes if it hasn't been done already		
			if ( ! isset( self::$footnotes[ $id ] ) )
					self::$footnotes[ $id ] = array( 0 => false );
			
			// add footnote to array		
			self::$footnotes[ $id ][] = $content;
			
			// get footnote number/reference (the last item in the array)
			$note = count( self::$footnotes[ $id ] ) - 1;
			
			$output = ' <a class="simple-wp-footnote" title="' . esc_attr( wp_strip_all_tags( $content ) ) . '" id="footnote-' . $id . '-' . $note . '-return" href="#footnote-' . $id . '-' . $note . '" data-id="' . $id . '">';
			$output .= '<sup>' . $note . '</sup>';
			$output .= '</a>';
						
			return $output;
   
	}

	/**
	 * Append footnotes below page links (if a page is split for example)
	 *
	 * @since 1.0
	 */		
	public static function simple_wp_footnotes_link_pages_args( $args ) {
		
			// if wp_link_pages appears both before and after the content, $footnotes[$id] will be empty the first time through
			$args['after'] = self::simple_wp_footnotes_append( $args['after'] );
			
			return $args;
			
	}
	
	/**
	 * Filter the content
	 *
	 * @since 1.0
	 */	
	function simple_wp_footnotes_filter_the_content( $content ) {
			
			//if option is set to 'content' or it isn't multipage add the footnotes to the end of the content
			if ( 'content' == self::$placement || ! $GLOBALS['multipage'] )
					return self::simple_wp_footnotes_append( $content );
			
			//return the content
			return $content;
			
	}


	/**
	 * Append footnotes
	 *
	 * @since 1.0
	 */	
	public static function simple_wp_footnotes_append( $content ) {
	
			$id = get_the_ID();
						
			// if no footnotes return content
			if ( empty( self::$footnotes[$id] ) )
					return $content;
					
			// get number of footnotes
			$count = count( self::$footnotes[ $id ] ) - 1;
					
			$content .= '<div class="simple-footnotes" id="footnotes-' . $id . '">';
			
			$content .= '<p class="notes">Footnotes: ';
			$content .= '<a href="#" class="footnote-toggle" data-id="' . $id . '" >';
			$content .= '<span class="footnote-show">Hide</span> ' . $count . ' footnotes';
			$content .= '</a>';
			$content .= '</p>';
			
			$content .= '<ol>';
			
			foreach ( array_filter( self::$footnotes[$id] ) as $num => $note )
					$content .= '<li id="footnote-' . $id . '-' . $num . '">' . do_shortcode( $note ) . ' <a href="#footnote-' . $id . '-' . $num . '-return">&#8617;</a></li>';
					
			$content .= '</ol>';
			
			$conyrny .= '</div>';
			
			return $content;
			
	}

	/**
	 * Replaces WP autop formatting 
	 *
	 * @since 1.0
	 */
	public static function simple_remove_wpautop($content) { 
	
		$content = do_shortcode( shortcode_unautop( $content ) ); 
		
		$content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content);
		
		return $content;
		
	}
	
	/**
	 * Helper function to get the URL of a given file. 
	 * 
	 * As this plugin may be used as both a stand-alone plugin and as a submodule of 
	 * a theme, the standard WP API functions, like plugins_url() can not be used. 
	 *
	 * @since 1.0
	 * @return array $post_name => $post_content
	 */
	public static function get_url( $file ) {

		// Get the path of this file after the WP content directory
		$post_content_path = substr( dirname( __FILE__ ), strpos( __FILE__, basename( WP_CONTENT_DIR ) ) + strlen( basename( WP_CONTENT_DIR ) ) );

		// Return a content URL for this path & the specified file
		return content_url( $post_content_path . $file );
	}	
	
}
 
endif;