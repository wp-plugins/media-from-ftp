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

		$plugin_datas = get_file_data( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/mediafromftp.php', array('version' => 'Version') );
		$plugin_version = floatval($plugin_datas['version']);

		$exclude_settings = '(.ktai.)|(.backwpup_log.)|(.ps_auto_sitemap.)|.php|.js';
		$user = wp_get_current_user();
		$cron_mail = $user->get('user_email');

		// << version 2.35
		if ( get_option('mediafromftp_exclude_file') ) {
			$exclude_settings = get_option('mediafromftp_exclude_file');
			delete_option( 'mediafromftp_exclude_file' );
		}

		if ( !get_option('mediafromftp_settings') ) {
			$mediafromftp_tbl = array(
								'pagemax' => 20,
								'searchdir' => MEDIAFROMFTP_PLUGIN_UPLOAD_PATH,
								'ext2typefilter' => 'all',
								'extfilter' => 'all',
								'dateset' => 'new',
								'max_execution_time' => 300,
								'exclude' => $exclude_settings,
								'cron' => array(
											'apply' => FALSE,
											'schedule' => 'hourly',
											'mail_apply' => TRUE,
											'mail' => $cron_mail
											)
							);
			update_option( 'mediafromftp_settings', $mediafromftp_tbl );
		} else {
			$mediafromftp_settings = get_option('mediafromftp_settings');
			if ( $plugin_version >= 3.0 && $plugin_version < 3.9 ) {
				if ( array_key_exists( "cron", $mediafromftp_settings ) ) {
					$cron_apply = $mediafromftp_settings['cron']['apply'];
					$cron_schedule = $mediafromftp_settings['cron']['schedule'];
				} else {
					$cron_apply = FALSE;
					$cron_schedule = 'hourly';
				}
				$mediafromftp_tbl = array(
									'searchdir' => $mediafromftp_settings['searchdir'],
									'dateset' => $mediafromftp_settings['dateset'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $cron_apply,
												'schedule' => $cron_schedule
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			} else if ( $plugin_version >= 3.9 && $plugin_version < 5.0 ) {
				if ( array_key_exists( "max_execution_time", $mediafromftp_settings ) ) {
					$max_execution_time = $mediafromftp_settings['max_execution_time'];
				} else {
					$max_execution_time = 300;
				}
				$mediafromftp_tbl = array(
									'searchdir' => $mediafromftp_settings['searchdir'],
									'dateset' => $mediafromftp_settings['dateset'],
									'max_execution_time' => $max_execution_time,
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule']
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			} else if ( $plugin_version >= 5.0 && $plugin_version < 6.0 ) {
				if ( array_key_exists( "ext2typefilter", $mediafromftp_settings ) ) {
					$ext2typefilter = $mediafromftp_settings['ext2typefilter'];
				} else {
					$ext2typefilter = 'all';
				}
				$mediafromftp_tbl = array(
									'searchdir' => $mediafromftp_settings['searchdir'],
									'ext2typefilter' => $ext2typefilter,
									'dateset' => $mediafromftp_settings['dateset'],
									'max_execution_time' => $mediafromftp_settings['max_execution_time'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule']
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			} else if ( $plugin_version >= 6.0 && $plugin_version < 6.3 ) {
				if ( array_key_exists( "pagemax", $mediafromftp_settings ) ) {
					$pagemax = $mediafromftp_settings['pagemax'];
				} else {
					$pagemax = 20;
				}
				$mediafromftp_tbl = array(
									'pagemax' => $pagemax,
									'searchdir' => $mediafromftp_settings['searchdir'],
									'ext2typefilter' => $mediafromftp_settings['ext2typefilter'],
									'dateset' => $mediafromftp_settings['dateset'],
									'max_execution_time' => $mediafromftp_settings['max_execution_time'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule']
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			} else if ( $plugin_version >= 6.3  && $plugin_version < 7.3 ) {
				if ( array_key_exists( "extfilter", $mediafromftp_settings ) ) {
					$extfilter = $mediafromftp_settings['extfilter'];
				} else {
					$extfilter = 'all';
				}
				$mediafromftp_tbl = array(
									'pagemax' => $mediafromftp_settings['pagemax'],
									'searchdir' => $mediafromftp_settings['searchdir'],
									'ext2typefilter' => $mediafromftp_settings['ext2typefilter'],
									'extfilter' => $extfilter,
									'dateset' => $mediafromftp_settings['dateset'],
									'max_execution_time' => $mediafromftp_settings['max_execution_time'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule']
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			} else if ( $plugin_version >= 7.4 ) {
				if ( array_key_exists( "cron", $mediafromftp_settings ) ) {
					$cron_apply = $mediafromftp_settings['cron']['apply'];
					$cron_schedule = $mediafromftp_settings['cron']['schedule'];
					if ( array_key_exists( "mail_apply", $mediafromftp_settings['cron'] ) ) {
						$cron_mail_apply = $mediafromftp_settings['cron']['mail_apply'];
					} else {
						$cron_mail_apply = TRUE;
					}
				} else {
					$cron_apply = FALSE;
					$cron_schedule = 'hourly';
					$cron_mail_apply = TRUE;
				}
				$mediafromftp_tbl = array(
									'pagemax' => $mediafromftp_settings['pagemax'],
									'searchdir' => $mediafromftp_settings['searchdir'],
									'ext2typefilter' => $mediafromftp_settings['ext2typefilter'],
									'extfilter' => $mediafromftp_settings['extfilter'],
									'dateset' => $mediafromftp_settings['dateset'],
									'max_execution_time' => $mediafromftp_settings['max_execution_time'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule'],
												'mail_apply' => $cron_mail_apply,
												'mail' => $cron_mail
												)
								);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
			}
		}

	}

}

?>