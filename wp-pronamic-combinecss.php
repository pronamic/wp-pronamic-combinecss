<?php

/*
Plugin Name: WP Pronamic CombineCSS
Plugin URI: http://pronamic.nl
Description: Combine all registered/enqueued css into a single stylesheet using imports
Version: 1.0.0
Author: Zogot
Author URI: http://pronamic.nl
License: GPLv2
*/

/* 
Copyright (C) 2013 Pronamic

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/**
 * Loops through all styles, determines if they are absolute links, and 
 * deques and deregisters them.  Takes their src, and stores them in a global
 * 
 * `wp_pronamic_combinecss` that is made of lines of @import url()
 * 
 * For some reason, I require a call to a de/register or de/enqueue script
 * else the global $wp_styles wont show.
 * 
 * @global WP_Style $wp_styles
 * @global array $wp_pronamic_combinecss
 */
function wp_pronamic_combinecss() {
	// For some reason, must call this or any of the other style functions
	// to get $wp_styles to work.  Try it if interested. Just comment below
	wp_deregister_style( 'asdfasdfasdfasdfasdfsadasfsad' );
	
	global $wp_styles;
	global $wp_pronamic_combinecss;
	
	$wp_pronamic_combinecss = array();
	
	$needles = array( 'https', 'http' );
	
	foreach ( $wp_styles->registered as $registered_stylesheet ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $registered_stylesheet->src, $needle ) ) {
				$wp_pronamic_combinecss[] = "@import url('" . $registered_stylesheet->src . "');";
				
				wp_dequeue_style( $registered_stylesheet->handle );
				wp_deregister_style( $registered_stylesheet->handle );
			}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'wp_pronamic_combinecss', 20 );

/**
 * Takes all the combined css lines, and puts them into the
 * combinecss.css file
 * 
 * @global array $wp_pronamic_combinecss
 * @return void
 */
function wp_pronamic_combinecss_write() {
	if ( ! isset( $_GET['combinecss'] ) )
		return;
	
	if ( ! $_GET['combinecss'] == 'fix' )
		return;
	global $wp_pronamic_combinecss;
	
	@file_put_contents( dirname( __FILE__ ) . '\combinecss.css', implode( "\n\r", $wp_pronamic_combinecss ) );
	wp_redirect( '/' );
}

add_action( 'wp_enqueue_scripts', 'wp_pronamic_combinecss_write', 21 );

/**
 * Registers the combinedcss stylesheet
 */
function wp_pronamic_combinecss_stylesheet() {
	wp_register_style( 'wp-pronamic-combinecss', plugins_url( 'combinecss.css', __FILE__ ) );
	wp_enqueue_style( 'wp-pronamic-combinecss' );
}

add_action( 'wp_enqueue_scripts', 'wp_pronamic_combinecss_stylesheet', 22 );

/**
 * Dashboard Widget that makes a simple link to fix the css after
 * installing a new plugin
 * 
 * Perhaps a solution to go to this url on hooks after installing plugins.
 */
function wp_pronamic_combinecss_dashboard_widget() {
	echo "<a href='" . add_query_arg( 'combinecss', 'fix', site_url() ) . "'>Fix CSS</a>";
}

function wp_pronamic_combinecss_dashboard_widget_function() {
	wp_add_dashboard_widget( 'wp-pronamic-combinecss', 'CSS Fix', 'wp_pronamic_combinecss_dashboard_widget' );
}

add_action( 'wp_dashboard_setup', 'wp_pronamic_combinecss_dashboard_widget_function' );