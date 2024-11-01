<?php
/*
Plugin Name: WP Auto Save Management
Plugin URI: http://www.tigerstrikemedia.com/plugins/wp-auto-save-management
Description: This Plugin adds some options to the general options page to manage the wp auto save feature.
Version: 0.3
Author: Ben Casey
Author URI: http://www.tigerstrikemedia.com
License: GPL3
*/

/*
	Copyright 2011  Ben Casey  (email : bcasey@tigerstrikemedia.com)

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * Actions And Filters
 */
add_action( 'admin_init' , 'wpasm_add_options_page_option' ) ;
add_action ( 'plugins_loaded' , 'wpasm_define_constants' ) ; 
add_filter ( 'whitelist_options' , 'wpasm_update_whitelist_options' , 10 , 1 ) ;

/*
 * Need To Tell Wordpress we have new options
 */
//apply_filters( 'whitelist_options', $whitelist_options );
function wpasm_update_whitelist_options ( $whitelist_options ) {
	
	$whitelist_options['general'][] = 'disable-auto-save';
	$whitelist_options['general'][] = 'limit-post-revisions';
	$whitelist_options['general'][] = 'autosave-time';
	
	return $whitelist_options ;
}



/*
 * Use The Proper Parameters To Add An Option To The Page.
 */
function wpasm_add_options_page_option(){
	global $wp_settings_fields;
	
	$wp_settings_fields['general']['default'] = array(
		0 => array( 
			'callback' => 'wpasm_create_option_disable' , 
			'args' => '',
			'title' => 'Disable Auto Save'
		),
		
		1 => array (
			'callback' => 'wpasm_create_option_revision_limit',
			'args' => '',
			'title' => 'Limit Number Of Post Revisions'
		),
		
		2 => array (
			'callback' => 'wpasm_create_option_autosave_time',
			'args' => '',
			'title' => 'Auto Save Time'
		)
	) ;	
	
}

function wpasm_define_constants () {
	$disable_autosave = get_option ( 'disable-auto-save' ) ;
	$limit_revisions = get_option ( 'limit-post-revisions' ) ;
	$autosave_time = ( int )get_option ( 'autosave-time' ) ;
	
	//And Deal With Them:
	if( $autosave_time > 0 )
	    define ( 'AUTOSAVE_INTERVAL' , $autosave_time ) ;

	if ( $disable_autosave != '1' && ! empty( $limit_revisions ) ){
        add_filter( 'wp_revisions_to_keep', 'wpasm_limit_revisions_filter' );
	} else{
        add_filter( 'wp_revisions_to_keep', '__return_zero' );
		add_action( 'admin_enqueue_scripts', 'wpasm_dequeue_autosave' );
	}	
	
}

/**
 * Wrapper function to limit the revisions stored.
 *
 * @return mixed|void
 */
function wpasm_limit_revisions_filter(){
    return get_option ( 'limit-post-revisions' );
}

//wp_revisions_to_keep

/**
 * Dequeue the autosave scripting.
 */
function wpasm_dequeue_autosave(){
    wp_dequeue_script('autosave');
}


/*
 * Create The Option HTML To Disable auto save
 */
function wpasm_create_option_disable () { ?>
	
	<label for="disable-auto-save">
	<input type="checkbox" name="disable-auto-save" value="1" value="1" <?php checked('1', get_option('disable-auto-save')); ?> />
	<?php _e( 'Auto Save Disabled' ); ?>
	</label>
	
<?php }

/*
 * Create The Option HTML To Limit Revisions
 */
function wpasm_create_option_revision_limit () { ?>

	<label for="limit-post-revisions">
	<input type="text" name="limit-post-revisions" value="<?php echo get_option( 'limit-post-revisions' ) ; ?>" />
	<?php _e( 'Number Of Post Revisions To Save' ); ?>
	</label>
	
<?php }

/*
 * Create The Option HTML To Edit autosave Time
 */
function wpasm_create_option_autosave_time () { ?>

	<label for="autosave-time">
	<input type="text" name="autosave-time" value="<?php echo get_option( 'autosave-time' ) ; ?>" />
	<?php _e( 'Time (In Seconds) that Wordpress will save the current post' ); ?>
	</label>
	
<?php }

?>