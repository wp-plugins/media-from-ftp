<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage mediafromftpcmd.php
 *
 * This program, run the Media from FTP on the command line.
 *
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

/*
 * command line argument list
 *
 * -s Search directory
 *    example -s wp-content/uploads
 * -d Date time settings (new, server, exif)
 *    example -d exif
 * -e Exclude file (Regular expression is possible.)
 *    example -e "(.ktai.)|(.backwpup_log.)|(.ps_auto_sitemap.)|.php|.js"
 * -t File type (all, image, audio, video, document, spreadsheet, interactive, text, archive, code)
 * 	  example -t image
 * -x File extension
 * 	  example -x jpg
 *
 * If the argument is empty, use the set value of the management screen.
 *
 * command line switch
 *
 * -h Hides the display of the log.
 * 	  example -h
 *
*/
// For your environment, please rewrite.
	$_SERVER = array(
		"HTTP_HOST" => "localhost",
		"SERVER_NAME" => "localhost",
		"REQUEST_URI" => "/",
		"REQUEST_METHOD" => "GET",
		"HTTP_USER_AGENT" => "mediafromftp"
					);
	require_once(dirname(__FILE__).'/../../../wp-load.php');
// For your environment, please rewrite.

	$activeplugins = get_option('active_plugins');
	$mediafromftp_active = FALSE;
	foreach ( $activeplugins as $plugin_number => $plugin_name ) {
		if ( $plugin_name === 'media-from-ftp/mediafromftp.php' ) {
			$mediafromftp_active = TRUE;
		}
	}

	if ( !$mediafromftp_active ) {
		define("MEDIAFROMFTP_PLUGIN_BASE_DIR", dirname(__FILE__));
		$wp_uploads = wp_upload_dir();
		if(is_ssl()){
			define("MEDIAFROMFTP_PLUGIN_UPLOAD_URL", str_replace('http:', 'https:', $wp_uploads['baseurl']));
		} else {
			define("MEDIAFROMFTP_PLUGIN_UPLOAD_URL", $wp_uploads['baseurl']);
		}
		define("MEDIAFROMFTP_PLUGIN_UPLOAD_DIR", $wp_uploads['basedir']);
	}

	require_once( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/req/MediaFromFtpCron.php' );
	$mediafromftpcron = new MediaFromFtpCron();
	$mediafromftpcron->CronDo();
	unset($mediafromftpcron);

?>