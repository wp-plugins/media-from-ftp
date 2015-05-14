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

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();

		$mediafromftp_settings = get_option('mediafromftp_settings');

		// for mediafromftpcmd.php
		$cmdoptions = getopt("s:d:e:t:x:h");

		$cmdlinedebugs = debug_backtrace();
		if ( basename($cmdlinedebugs['0']['file']) === 'mediafromftpcmd.php' ) {
			$cmdline = TRUE;
		} else {
			$cmdline = FALSE;
			$max_execution_time = $mediafromftp_settings['max_execution_time'];
			set_time_limit($max_execution_time);
		}

		if ( isset($cmdoptions['s']) ) {
			$searchdir = $cmdoptions['s'];
		} else {
			$searchdir = $mediafromftp_settings['searchdir'];
		}

		if ( isset($cmdoptions['d']) ) {
			if ( $cmdoptions['d'] === 'new' || $cmdoptions['d'] === 'server' || $cmdoptions['d'] === 'exif' ) {
				$dateset = $cmdoptions['d'];
			} else {
				$dateset = $mediafromftp_settings['dateset'];
			}
		} else {
			$dateset = $mediafromftp_settings['dateset'];
		}

		if ( isset($cmdoptions['x']) ) {
			$extfilter = $cmdoptions['x'];
		} else {
			$extfilter = $mediafromftp_settings['extfilter'];
		}

		$hide = FALSE;
		if ( isset($cmdoptions['h']) ) {
			$hide = TRUE;
		}

		unset($cmdoptions);

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

		global $wpdb;
		$attachments = $wpdb->get_results("
						SELECT guid
						FROM $wpdb->posts
						WHERE post_type = 'attachment'
						");

		$extpattern = $mediafromftp->extpattern($extfilter);
		$files = $mediafromftp->scan_file($document_root, $extpattern);

		$count = 0;
		$output_mail = NULL;
		$mail_apply = $mediafromftp_settings['cron']['mail_apply'];
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
					list($attach_id, $new_attach_title, $new_url_attach, $metadata) = $mediafromftp->regist($ext, $new_url, $date, $dateset, $yearmonth_folders);

					if ( ($mail_apply && !$cmdline) || (!$hide && $cmdline) ) {
						// OutputMetaData
						list($imagethumburls, $mimetype, $length, $stamptime, $file_size) = $mediafromftp->output_metadata($ext, $attach_id, $metadata);
						$new_url_attachs = explode('/', $new_url_attach);
						++$count;

						$output_text = NULL;
						$output_text .= __('Count').': '.$count."\n";
						$output_text .= __('Title').': '.$new_attach_title."\n";
						$output_text .= __('Permalink:').' '.get_attachment_link($attach_id)."\n";
						$output_text .= 'URL: '.$new_url_attach."\n";
						$output_text .= __('File name:').' '.end($new_url_attachs)."\n";
						$output_text .= __('Date/Time').': '.$stamptime."\n";
						if ( wp_ext2type($ext) === 'image' ) {
							foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
								$output_text .= $thumbsize.': '.$imagethumburl."\n";
							}
						} else {
							$output_text .= __('File type:').' '.$mimetype."\n";
							$output_text .= __('File size:').' '.size_format($file_size)."\n";
							if ( wp_ext2type($ext) === 'video' || wp_ext2type($ext) === 'audio' ) {
								$output_text .= __('Length:').' '.$length."\n";
							}
						}
						$output_text .= "\n";

						if ( $cmdline ) {
							echo $output_text;
						} else {
							$output_mail .= $output_text;
						}
					}
				}
			}
		}
		if ( !empty($output_mail) ) {
			$to = $mediafromftp_settings['cron']['mail'];
			$subject = 'Media from FTP Schedule';
			wp_mail( $to, $subject, $output_mail );
		}

	}

}

?>