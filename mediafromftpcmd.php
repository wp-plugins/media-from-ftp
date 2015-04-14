<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage mediafromftpcmd.php
 *
 * This program, run the Media from FTP on the command line.
 * Settings, please go in the normal management screen.
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

	$_SERVER = array(
		"HTTP_HOST" => "localhost",		// For your environment, please rewrite.
		"SERVER_NAME" => "localhost",	// For your environment, please rewrite.
		"REQUEST_URI" => "/",
		"REQUEST_METHOD" => "GET",
		"HTTP_USER_AGENT" => "mediafromftp"
					);
	require_once(dirname(__FILE__).'/../../../wp-load.php'); // For your environment, please rewrite.

	require_once( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/req/MediaFromFtpCron.php' );
	$mediafromftpcron = new MediaFromFtpCron();
	$mediafromftpcron->CronDo();
	unset($mediafromftpcron);

?>