<?php
/*
Plugin Name: Media from FTP
Plugin URI: http://wordpress.org/plugins/media-from-ftp/
Version: 2.14
Description: Register to media library from files that have been uploaded by FTP.
Author: Katsushi Kawamori
Author URI: http://gallerylink.nyanko.org/medialink/media-from-ftp/
Domain Path: /languages
*/

/*  Copyright (c) 2013- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

	load_plugin_textdomain('mediafromftp', false, basename( dirname( __FILE__ ) ) . '/languages' );

	define("MEDIAFROMFTP_PLUGIN_BASE_FILE", plugin_basename(__FILE__));
	define("MEDIAFROMFTP_PLUGIN_BASE_DIR", dirname(__FILE__));

	require_once( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/req/MediaFromFtpRegist.php' );
	$mediafromftpregist = new MediaFromFtpRegist();
	add_action('admin_init', array($mediafromftpregist, 'register_settings'));
	unset($mediafromftpregist);

	add_action( 'wp_head', wp_enqueue_script('jquery') );

	require_once( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/req/MediaFromFtpAdmin.php' );
	$mediafromftpadmin = new MediaFromFtpAdmin();
	add_filter( 'plugin_action_links', array($mediafromftpadmin, 'settings_link'), 10, 2 );
	add_action( 'admin_menu', array($mediafromftpadmin, 'add_pages'));
	unset($mediafromftpadmin);

?>