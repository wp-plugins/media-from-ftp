<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage MediafromFTPCron Cron
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

class MediaFromFtpCron {

	/* ==================================================
	 * Cron Start
	 * @since	3.0
	 */
	function CronStart() {

		$mediafromftp_settings = get_option('mediafromftp_settings');

		if ( $mediafromftp_settings['cron']['apply'] ) {
			if ( !wp_next_scheduled( 'MediaFromFtpCronHook' ) ) {
				wp_schedule_event(time(), $mediafromftp_settings['cron']['schedule'], 'MediaFromFtpCronHook');
			} else {
				if ( wp_get_schedule( 'MediaFromFtpCronHook' ) <> $mediafromftp_settings['cron']['schedule'] ) {
					wp_clear_scheduled_hook('MediaFromFtpCronHook');
					wp_schedule_event(time(), $mediafromftp_settings['cron']['schedule'], 'MediaFromFtpCronHook');
				}
			}
		}

	}


	/* ==================================================
	 * Cron Stop
	 * @since	3.0
	 */
	function CronStop() {

		wp_clear_scheduled_hook('MediaFromFtpCronHook');

	}

	/* ==================================================
	 * Cron
	 * @since	3.0
	 */
	function CronDo(){

		ini_set('max_execution_time', 300); 

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();

		$mediafromftp_settings = get_option('mediafromftp_settings');
		$searchdir = $mediafromftp_settings['searchdir'];
		$dateset = $mediafromftp_settings['dateset'];
		$yearmonth_folders = get_option('uploads_use_yearmonth_folders');
		$document_root = ABSPATH.$searchdir;

		if( get_option('WPLANG') === 'ja' ) {
			mb_language('Japanese');
		} else if( get_option('WPLANG') === 'en' ) {
			mb_language('English');
		} else {
			mb_language('uni');
		}
		if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
			$document_root = mb_convert_encoding($document_root, "sjis-win", "auto");
		} else {
			$document_root = mb_convert_encoding($document_root, "UTF-8", "auto");
		}

		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1
			);
		$attachments = get_posts($args);

		$extpattern = $mediafromftp->extpattern();
		$files = $mediafromftp->scan_file($document_root, $extpattern);

		foreach ( $files as $file ){
			// Input URL
			list($new_file, $ext, $new_url) = $mediafromftp->input_url($file, $attachments);
			if ($new_file) {
				if ( !is_writable(dirname($file)) && wp_ext2type($ext) === 'image' ) {
					// skip
				} else if ( !is_writable(dirname($file)) && strlen($file) <> mb_strlen($file) ) {
					// skip
				} else {
					$date = $mediafromftp->get_date_check($file, $dateset);
					// Regist
					list($attach_id, $new_attach_title, $new_url_attach) = $mediafromftp->regist($ext, $new_url, $date, $dateset, $yearmonth_folders);
				}
			}
		}

	}

}

?>