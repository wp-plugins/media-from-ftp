<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage MediaFromFtpRegist registered in the database
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

class MediaFromFtpRegist {

	/* ==================================================
	 * Settings register
	 * @since	2.3
	 */
	function register_settings(){

		$searchdir = str_replace(site_url('/'), '', MEDIAFROMFTP_PLUGIN_UPLOAD_URL);

		$exclude_settings = '(.ktai.)|(.backwpup_log.)|(.ps_auto_sitemap.)|.php|.js';
		// << version 2.35
		if ( get_option('mediafromftp_exclude_file') ) {
			$exclude_settings = get_option('mediafromftp_exclude_file');
			delete_option( 'mediafromftp_exclude_file' );
		}

		if ( !get_option('mediafromftp_settings') ) {
			$mediafromftp_tbl = array(
								'searchdir' => $searchdir,
								'dateset' => 'new',
								'exclude' => $exclude_settings,
								'cron' => array(
											'apply' => FALSE,
											'schedule' => 'hourly'
											)
							);
			update_option( 'mediafromftp_settings', $mediafromftp_tbl );
		} else {
			$mediafromftp_settings = get_option('mediafromftp_settings');
			if ( !array_key_exists('cron', $mediafromftp_settings) ) {
				$mediafromftp_tbl = array(
									'searchdir' => $mediafromftp_settings['searchdir'],
									'dateset' => $mediafromftp_settings['dateset'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => FALSE,
												'schedule' => 'hourly'
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			}
		}

	}

}

?>