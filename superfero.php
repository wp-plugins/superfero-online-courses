<?php
/*
Plugin Name: Superfero Online Courses
Plugin URI: http://wordpress.org/plugins/superfero-online-courses/
Description: Superfero Online Courses Plugin collects the template courses from superfero.com to show up your blog page
Author: Lan Nguyen
Version: 2.2
Author URI: http://wordpress.org/plugins/superfero-online-courses/
*/
/*  Copyright 2014 Lan Nguyen (email: lan.nguyen at superfero.com)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Include plugin class files
require_once( 'settings.php' );
require_once( 'includes/class-superfero.php' );
require_once( 'includes/class-superfero-settings.php' );

/**
 * Returns the main instance of Superfero to prevent the need to use globals.
 *
 * @since  2.0
 * @return object Superfero
 */
function Superfero () {
	$instance = Superfero::instance( __FILE__ , '1.0' );
	if( is_null( $instance->settings ) ) {
		$instance->settings = Superfero_Settings::instance( $instance );
	}
	return $instance;
}

Superfero();