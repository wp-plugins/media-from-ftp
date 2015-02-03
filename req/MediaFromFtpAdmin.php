<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage MediafromFTPAdmin Main & Management screen
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

class MediaFromFtpAdmin {

	public $postcount;

	/* ==================================================
	 * Add a "Settings" link to the plugins page
	 * @since	1.0
	 */
	function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty($this_plugin) ) {
			$this_plugin = MEDIAFROMFTP_PLUGIN_BASE_FILE;
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="'.admin_url('tools.php?page=mediafromftp').'">'.__( 'Settings').'</a>';
		}
			return $links;
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function add_pages() {
		add_management_page('Media from FTP', 'Media from FTP', 'manage_options', 'mediafromftp', array($this, 'manage_page'));
	}

	/* ==================================================
	 * Add Css and Script
	 * @since	2.23
	 */
	function load_custom_wp_admin_style() {
		wp_enqueue_style( 'jquery-responsiveTabs', MEDIAFROMFTP_PLUGIN_URL.'/css/responsive-tabs.css' );
		wp_enqueue_style( 'jquery-responsiveTabs-style', MEDIAFROMFTP_PLUGIN_URL.'/css/style.css' );
		wp_enqueue_script('jquery');

		wp_enqueue_style( 'jquery-datetimepicker', MEDIAFROMFTP_PLUGIN_URL.'/css/jquery.datetimepicker.css' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-responsiveTabs', MEDIAFROMFTP_PLUGIN_URL.'/js/jquery.responsiveTabs.min.js' );
		wp_enqueue_script( 'jquery-datetimepicker', MEDIAFROMFTP_PLUGIN_URL.'/js/jquery.datetimepicker.js', null, '2.3.4' );

	}

	/* ==================================================
	 * Add Script on footer
	 * @since	2.24
	 */
	function load_custom_wp_admin_style2() {
		echo $this->add_js();
	}

	/* ==================================================
	 * Main
	 */
	function manage_page() {

		ini_set('max_execution_time', 300); 

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if ( empty($_POST['mediafromftp-tabs']) ) {
			$tabs = 1;
		} else {
			$tabs = intval($_POST['mediafromftp-tabs']);
		}

		$this->options_updated($tabs);

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();
		$mediafromftp_settings = get_option('mediafromftp_settings');
		$searchdir = $mediafromftp_settings['searchdir'];

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}

		$scriptname = admin_url('tools.php?page=mediafromftp');

		?>
		<div class="wrap">

		<h2>Media from FTP</h2>

			<div id="mediafromftp-tabs">
				<ul>
				<li><a href="#mediafromftp-tabs-1"><?php _e('Search & Register', 'mediafromftp'); ?></a></li>
				<li><a href="#mediafromftp-tabs-2"><?php _e('Settings'); ?></a></li>
				<li><a href="#mediafromftp-tabs-3"><?php _e('Exclude file', 'mediafromftp'); ?></a></li>
				<li><a href="#mediafromftp-tabs-4"><?php _e('Uploading Files'); ?></a></li>
				<li><a href="#mediafromftp-tabs-5"><?php _e('Schedule', 'mediafromftp'); ?></a></li>
				</ul>
				<div id="mediafromftp-tabs-1">

		<h3><?php _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>

		<?php

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

		if ( $adddb <> 'TRUE' ) {
			$dirs = $mediafromftp->scan_dir(MEDIAFROMFTP_PLUGIN_UPLOAD_DIR);
			$linkselectbox = NULL;
			foreach ($dirs as $linkdir) {

				$linkdirenc = mb_convert_encoding(str_replace(ABSPATH, "", $linkdir), "UTF-8", "auto");
				if( $searchdir === $linkdirenc ){
					$linkdirs = '<option value="'.urlencode($linkdirenc).'" selected>'.$linkdirenc.'</option>';
				}else{
					$linkdirs = '<option value="'.urlencode($linkdirenc).'">'.$linkdirenc.'</option>';
				}
				$linkselectbox = $linkselectbox.$linkdirs;
			}
			if( empty($_POST['searchdir']) || $searchdir ===  MEDIAFROMFTP_PLUGIN_UPLOAD_PATH ){
				$linkdirs = '<option value="" selected>'.MEDIAFROMFTP_PLUGIN_UPLOAD_PATH.'</option>';
			}else{
				$linkdirs = '<option value="">'.MEDIAFROMFTP_PLUGIN_UPLOAD_PATH.'</option>';
			}
			$linkselectbox = $linkselectbox.$linkdirs;
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
				<div style="display:block;padding:20px 0">
					<?php _e('Find the following directories.', 'mediafromftp'); ?>
					<select name="searchdir" style="width: 100%">
					<?php echo $linkselectbox; ?>
					</select>
					<input type="hidden" name="mediafromftp-tabs" value="1" />
					<input type="submit" value="<?php _e('Search'); ?>" />
				</div>
			</form>
			<?php
		}

		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1
			);
		$attachments = get_posts($args);

		$extpattern = $mediafromftp->extpattern();
		$files = $mediafromftp->scan_file($document_root, $extpattern);
		$count = 0;
		$this->postcount = 0;
		$post_attachs = array();
		$unregister_unwritable_count = 0;
		$unregister_multibyte_file_count = 0;

		echo str_pad(' ',4096)."\n";
		ob_end_flush();
		ob_start('mb_output_handler');

		foreach ( $files as $file ){

			// Input URL
			list($new_file, $ext, $new_url) = $mediafromftp->input_url($file, $attachments);

			if ($new_file) {
				if ( !is_writable(dirname($file)) && wp_ext2type($ext) === 'image' ) {
					$unregisters_unwritable[$unregister_unwritable_count] = $new_url;
					++$unregister_unwritable_count;
				} else if ( !is_writable(dirname($file)) && strlen($file) <> mb_strlen($file) ) {
					$unregisters_multibyte_file[$unregister_multibyte_file_count] = $new_url;
					++$unregister_multibyte_file_count;
				} else {
					++$count;
					if ( $count == 1 ) {
						?>
						<?php
						if ( $adddb <> 'TRUE' ) {
							?>
							<form method="post" action="<?php echo $scriptname; ?>">
							<div class="submit">
								<input type="hidden" name="mediafromftp-tabs" value="1" />
								<input type="hidden" name="adddb" value="TRUE">
								<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
								<input type="submit" value="<?php _e('Update Media'); ?>" />
							</div>
							<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
							<input type="checkbox" id="group_media-from-ftp" class="mediafromftp-checkAll"><?php _e('Select all'); ?>
							</div>
							<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
							<?php _e('Select'); ?> & <?php _e('Thumbnail'); ?> & <?php _e('Metadata'); ?>
							</div>
							<?php
						}
					}
					if ( $adddb <> 'TRUE' ) {
							$input_html = NULL;
							$input_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
							$input_html .= '<input name="new_url_attaches['.$this->postcount.'][url]" type="checkbox" value="'.$new_url.'" class="group_media-from-ftp" style="float: left; margin: 5px;">';

							$metadata_org = NULL;
							if ( wp_ext2type($ext) === 'image' ){
								$view_thumb_url = $mediafromftp->create_cash($ext, $file, $new_url);
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else if ( wp_ext2type($ext) === 'audio' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/audio.png';
								$metadata_audio = wp_read_audio_metadata( $file );
								$file_size_audio = $metadata_audio['filesize'];
								$mimetype_audio = $metadata_audio['fileformat'].'('.$metadata_audio['mime_type'].')';
								$length_audio = $metadata_audio['length_formatted'];
								$metadata_org = '<div>'.__('File type:').' '.$mimetype_audio.'</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format($file_size_audio).'</div>';
								$metadata_org .= '<div>'.__('Length:').' '.$length_audio.'</div>';
							} else if ( wp_ext2type($ext) === 'video' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/video.png';
								$metadata_video = wp_read_video_metadata( $file );
								$file_size_video = $metadata_video['filesize'];
								$mimetype_video = $metadata_video['fileformat'].'('.$metadata_video['mime_type'].')';
								$length_video = $metadata_video['length_formatted'];
								$metadata_org = '<div>'.__('File type:').' '.$mimetype_video.'</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format($file_size_video).'</div>';
								$metadata_org .= '<div>'.__('Length:').' '.$length_video.'</div>';
							} else if ( wp_ext2type($ext) === 'document' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/document.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else if ( wp_ext2type($ext) === 'spreadsheet' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/spreadsheet.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else if ( wp_ext2type($ext) === 'interactive' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/interactive.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else if ( wp_ext2type($ext) === 'text' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/text.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else if ( wp_ext2type($ext) === 'archive' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/archive.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else if ( wp_ext2type($ext) === 'code' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/code.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							} else {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/default.png';
								$metadata_org = '<div>'.__('File type:').' '.$ext.'('.$mediafromftp->mime_type($ext).')</div>';
								$metadata_org .= '<div>'.__('File size:').' '.size_format(filesize($file)).'</div>';
							}

							$input_html .= '<img width="50" height="50" src="'.$view_thumb_url.'" style="float: left; margin: 5px;">';
							$input_html .= '<div>URL:<a href="'.$new_url.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url.'</a></div>';
							$input_html .= $metadata_org;

							$date = $mediafromftp->get_date_check($file, $mediafromftp_settings['dateset']);
							if ( $mediafromftp_settings['dateset'] === 'new' ) {
								$input_html .= '<input type="hidden" id="datetimepicker-mediafromftp'.$this->postcount.'" name="new_url_attaches['.$this->postcount.'][datetime]" value="'.$date.'">';
							} else {
								$input_html .= '<div style="float: left; margin: 5px;">'.__('Edit date and time').'</div>';
								$input_html .= '<input type="text" id="datetimepicker-mediafromftp'.$this->postcount.'" name="new_url_attaches['.$this->postcount.'][datetime]" value="'.$date.'" style="width: 160px;">';
							}

							$input_html .= '</div>';

							echo $input_html;
							ob_flush();
							flush();

						++$this->postcount;
					}
				}
			}
		}
		ob_end_clean();
		?>
		<?php

		if ( $adddb === 'TRUE' ) {
			$new_url_attaches = $_POST["new_url_attaches"];
			if (!empty($new_url_attaches)) {
				?>
				<form method="post" action="<?php echo $scriptname; ?>">
					<div class="submit">
						<input type="hidden" name="mediafromftp-tabs" value="1" />
						<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
						<input type="submit" value="<?php _e('Back'); ?>" />
						<?php _e('Please try again pressing Back button, if the processing is stopped on the way.', 'mediafromftp'); ?>
					</div>
				</form>
				<?php
				$dateset = $mediafromftp_settings['dateset'];
				$yearmonth_folders = get_option('uploads_use_yearmonth_folders');

				echo str_pad(' ',4096)."\n";
				ob_start('mb_output_handler');

				foreach ( $new_url_attaches as $postkey1 => $postval1 ){
					foreach ( $postval1 as $postkey2 => $postval2 ){
						if ( $postkey2 === 'url' ) {
							$new_url_attach = $postval1[$postkey2];
							$new_url_datetime = $postval1['datetime'];
							$exts = explode('.', wp_basename($new_url_attach));
							$ext = end($exts);

							// Delete Cash
							$mediafromftp->delete_cash($ext, $new_url_attach);

							// Regist
							list($attach_id, $new_attach_title, $new_url_attach) = $mediafromftp->regist($ext, $new_url_attach, $new_url_datetime, $dateset, $yearmonth_folders);

							if ( wp_ext2type($ext) === 'image' ){
								$metadata = wp_get_attachment_metadata( $attach_id );
								$imagethumburl_base = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.'/'.rtrim($metadata['file'], wp_basename($metadata['file']));
								foreach ( $metadata as $key1 => $key2 ){
									if ( $key1 === 'sizes' ) {
										foreach ( $metadata[$key1] as $key2 => $key3 ){
											$imagethumburls[$key2] = $imagethumburl_base.$metadata['sizes'][$key2]['file'];
										}
									}
								}
								$image_attr_medium = wp_get_attachment_image_src($attach_id, 'medium');
								$image_attr_large = wp_get_attachment_image_src($attach_id, 'large');
								$image_attr_full = wp_get_attachment_image_src($attach_id, 'full');
							}else if ( wp_ext2type($ext) === 'video' ){
								$metadata = wp_read_video_metadata( get_attached_file($attach_id) );
								$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
								$length = $metadata['length_formatted'];
							}else if ( wp_ext2type($ext) === 'audio' ){
								$metadata = wp_read_audio_metadata( get_attached_file($attach_id) );
								$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
								$length = $metadata['length_formatted'];
							} else {
								$metadata = NULL;
							}

							$image_attr_thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail', true);

							$stamptime = get_the_time( 'Y-n-j ', $attach_id ).get_the_time( 'G:i', $attach_id );
							if ( isset( $metadata['filesize'] ) ) {
								$file_size = $metadata['filesize'];
							} else {
								$file_size = filesize( get_attached_file($attach_id) );
								$filetype = strtoupper($ext);
							}

							$output_html = NULL;
							$output_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
							$output_html .= '<img width="50" height="50" src="'.$image_attr_thumbnail[0].'">';
							$output_html .= '<div>'.__('Title').': '.$new_attach_title.'</div>';
							$output_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($attach_id).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($attach_id).'</a></div>';
							$output_html .= '<div>URL: <a href="'.$new_url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url_attach.'</a></div>';
							$new_url_attachs = explode('/', $new_url_attach);
							$output_html .= '<div>'.__('File name:').' '.end($new_url_attachs).'</div>';

							if ( wp_ext2type($ext) === 'image' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('Images').': ';
								foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
									$output_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
								}
								$output_html .= '</div>';
							} else if ( wp_ext2type($ext) === 'video' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
								$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
							} else if ( wp_ext2type($ext) === 'audio' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
								$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
							} else {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('File type:').' '.$filetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
							}

							$output_html .= '</div>';

							echo $output_html;
							ob_flush();
							flush();
						}
					}
				}
				ob_end_clean();
				?>
				<p>
				<?php _e('The above file was registered to the media library.', 'mediafromftp'); ?>
				</p>
				<?php
			}

			?>
			<div class="submit">
			<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
				<input type="hidden" name="mediafromftp-tabs" value="1" />
				<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
				<input type="submit" value="<?php _e('Back'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
				<input type="submit" value="<?php _e('Media Library'); ?>" />
			</form>
			</div>
			<div style="clear:both"></div>
			<?php
		} else {
			if ( $count == 0 && $unregister_unwritable_count == 0 && $unregister_multibyte_file_count == 0) {
				?>
				<p>
				<?php _e('There is no file that is not registered in the media library.', 'mediafromftp'); ?>
				</p>
				<?php
			} else {
				if ( $count > 0 ) {
					?>
					<br>
					<div>
					<?php _e('The above file is a file that is not registered in the media library. And can be registered.', 'mediafromftp'); ?>
					</div>
					<?php
				}
					?>
					<div class="submit">
						<input type="hidden" name="mediafromftp-tabs" value="1" />
						<input type="hidden" name="adddb" value="TRUE">
						<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
						<input type="submit" value="<?php _e('Update Media'); ?>" />
					</div>
					</form>
					<?php
				if ( !empty($unregisters_unwritable) ) {
					?>
					<p>
					<?php
					foreach ( $unregisters_unwritable as $unregister_unwritable_url ) {
						?>
						<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
						<div><?php echo $unregister_unwritable_url; ?></div>
						<div>
						<?php _e('Can not register to directory for unwritable, because generating a thumbnail in the case of image files. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
						</div>
						</div>
						<?php
					}
					?>
					</p>
					<?php
				}
				if ( !empty($unregisters_multibyte_file) ) {
					?>
					<p>
					<?php
					foreach ( $unregisters_multibyte_file as $unregister_multibyte_file_url ) {
						?>
						<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
						<div><?php echo $unregister_multibyte_file_url; ?></div>
						<div>
						<?php _e('Can not register to directory for unwritable, because to delete the previous file by converting in MD5 format from multi-byte file names. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
						</div>
						</div>
						<?php
					}
					?>
					</p>
					<?php
				}
			}
		}

		?>
		</div>

		<div id="mediafromftp-tabs-2">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname.'#mediafromftp-tabs-2'; ?>">
			<h3><?php _e('Settings'); ?></h3>
			<div style="display:block;padding:5px 0">
			<input type="radio" name="mediafromftp_dateset" value="new" <?php if ($mediafromftp_settings['dateset'] === 'new') echo 'checked'; ?>>
			<?php _e('Update to use of the current date/time.', 'mediafromftp'); ?>
			</div>
			<div style="display:block;padding:5px 0">
			<input type="radio" name="mediafromftp_dateset" value="server" <?php if ($mediafromftp_settings['dateset'] === 'server') echo 'checked'; ?>>
			<?php _e('Get the date/time of the file, and updated based on it. Change it if necessary.', 'mediafromftp'); ?>
			</div>
			<div style="display:block;padding:5px 0">
			<input type="radio" name="mediafromftp_dateset" value="exif" <?php if ($mediafromftp_settings['dateset'] === 'exif') echo 'checked'; ?>>
			<?php
			_e('Get the date/time of the file, and updated based on it. Change it if necessary.', 'mediafromftp');
			_e('Get by priority if there is date and time of the Exif information.', 'mediafromftp');
			?>
			</div>
			<div style="display:block;padding:5px 0">
			<input type="checkbox" name="move_yearmonth_folders" value="1" <?php checked('1', get_option('uploads_use_yearmonth_folders')); ?> />
			<?php _e('Organize my uploads into month- and year-based folders'); ?>
			</div>
			<div class="submit">
				<input type="hidden" name="mediafromftp-tabs" value="2" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		</div>

		<div id="mediafromftp-tabs-3">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname.'#mediafromftp-tabs-3'; ?>">
			<h3><?php _e('Exclude file', 'mediafromftp'); ?></h3>
			<p><?php _e('Regular expression is possible.', 'mediafromftp'); ?></p>
			<?php $mediafromftp_settings_tabs_2 = get_option('mediafromftp_settings'); ?>
			<textarea id="mediafromftp_exclude" name="mediafromftp_exclude" rows="4" style="width: 250px;"><?php echo $mediafromftp_settings_tabs_2['exclude']; ?></textarea>
			<div class="submit">
				<input type="hidden" name="mediafromftp-tabs" value="3" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		</div>

		<div id="mediafromftp-tabs-4">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname.'#mediafromftp-tabs-4'; ?>">
			<h3><?php _e('Uploading Files'); ?></h3>
			<div style="display:block; padding: 10px; border:4px red solid;width:98%; color:red; font-size: 120%;">
			<?php _e('If you want to change the upload directory, you can do so by changing the options.php upload_path, the upload_url_path. It is also possible from below. However, if it is necessary to complex settings such as the use of multi-site and sub-domains, is not recommended.', 'mediafromftp'); ?>
			</div>
			<div style="display:block;padding:20px 0;">
			<div><?php _e('Store uploads in this folder'); ?></div>
			<input name="upload_path" type="text" id="upload_path" value="<?php echo esc_attr(get_option('upload_path')); ?>" />
			<div><?php _e('Default is <code>wp-content/uploads</code>'); ?></div>
			</div>
			<div style="display:block;padding:20px 0;">
			<div><?php _e('Full URL path to files'); ?></div>
			<input name="upload_url_path" type="text" id="upload_url_path" value="<?php echo esc_attr( get_option('upload_url_path')); ?>" />
			<div><?php _e('Configuring this is optional. By default, it should be blank.'); ?></div>
			</div>
			<div style="display:block; padding:10px 0; color:red;">
			<?php _e('If you change the settings, you must be re-register the file to the media library.', 'mediafromftp'); ?>
			</div>
			<div style="display:block; padding:10px 0; color:red;">
			<?php _e('When you want to restore the original settings of the above, please be blank.', 'mediafromftp'); ?>
			</div>
			<div class="submit">
				<input type="hidden" name="mediafromftp-tabs" value="4" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		</div>

		<div id="mediafromftp-tabs-5">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname.'#mediafromftp-tabs-5'; ?>">
			<h3><?php _e('Schedule', 'mediafromftp'); ?></h3>
			<div style="display:block;padding:5px 0">
			<?php _e('Set the schedule.', 'mediafromftp'); ?>
			<?php _e('Will take some time until the [Next Schedule] is reflected.', 'mediafromftp'); ?>
			</div>
			<?php
			$mediafromftp_settings_tabs_4 = get_option('mediafromftp_settings');
			if ( wp_next_scheduled( 'MediaFromFtpCronHook' ) ) {
				$next_schedule = ' '.get_date_from_gmt(date("Y-m-d H:i:s", wp_next_scheduled( 'MediaFromFtpCronHook' )));
			} else {
				$next_schedule = ' '.__('None');
			}
			?>
			<div style="display:block;padding:5px 0">
			<?php echo __('Next Schedule:', 'mediafromftp').$next_schedule; ?>
			</div>
			<div style="display:block;padding:5px 0">
			<input type="checkbox" name="mediafromftp_cron_apply" value="1" <?php checked('1', $mediafromftp_settings_tabs_4['cron']['apply']); ?> />
			<?php _e('Apply Schedule', 'mediafromftp'); ?>
			</div>
			<div style="display:block;padding:5px 10px">
			<input type="radio" name="mediafromftp_cron_schedule" value="hourly" <?php checked('hourly', $mediafromftp_settings_tabs_4['cron']['schedule']); ?>>
			<?php _e('hourly', 'mediafromftp'); ?>
			</div>
			<div style="display:block;padding:5px 10px">
			<input type="radio" name="mediafromftp_cron_schedule" value="twicedaily" <?php checked('twicedaily', $mediafromftp_settings_tabs_4['cron']['schedule']); ?>>
			<?php _e('twice daily', 'mediafromftp'); ?>
			</div>
			<div style="display:block;padding:5px 10px">
			<input type="radio" name="mediafromftp_cron_schedule" value="daily" <?php checked('daily', $mediafromftp_settings_tabs_4['cron']['schedule']); ?>>
			<?php _e('daily', 'mediafromftp'); ?>
			</div>

			<div class="submit">
				<input type="hidden" name="mediafromftp-tabs" value="5" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		</div>

		</div>
		</div>
		<?php

	}

	/* ==================================================
	 * Update wp_options table.
	 * @param	string	$tabs
	 * @since	2.36
	 */
	function options_updated($tabs){

		$mediafromftp_settings = get_option('mediafromftp_settings');

		switch ($tabs) {
			case 1:
				if (!empty($_POST['searchdir'])){
					$searchdir = str_replace(site_url('/'), '', urldecode($_POST['searchdir']));
				} else {
					$searchdir = str_replace(site_url('/'), '', MEDIAFROMFTP_PLUGIN_UPLOAD_URL);
				}
				$mediafromftp_tbl = array(
									'searchdir' => $searchdir,
									'dateset' => $mediafromftp_settings['dateset'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule']
												)
									);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
				break;
			case 2:
				if ( !empty($_POST['mediafromftp_dateset']) ) {
					$mediafromftp_tbl = array(
										'searchdir' => $mediafromftp_settings['searchdir'],
										'dateset' => $_POST['mediafromftp_dateset'],
										'exclude' => $mediafromftp_settings['exclude'],
										'cron' => array(
													'apply' => $mediafromftp_settings['cron']['apply'],
													'schedule' => $mediafromftp_settings['cron']['schedule']
													)
										);
					update_option( 'mediafromftp_settings', $mediafromftp_tbl );
					if ( !empty($_POST['move_yearmonth_folders']) ) {
						update_option( 'uploads_use_yearmonth_folders', $_POST['move_yearmonth_folders'] );
					} else {
						update_option( 'uploads_use_yearmonth_folders', '0' );
					}
				}
				break;
			case 3:
				if ( !empty($_POST['mediafromftp_exclude']) ) {
					$mediafromftp_tbl = array(
										'searchdir' => $mediafromftp_settings['searchdir'],
										'dateset' => $mediafromftp_settings['dateset'],
										'exclude' => $_POST['mediafromftp_exclude'],
										'cron' => array(
													'apply' => $mediafromftp_settings['cron']['apply'],
													'schedule' => $mediafromftp_settings['cron']['schedule']
													)
										);
					update_option( 'mediafromftp_settings', $mediafromftp_tbl );
				}
				break;
			case 4:
				if ( !empty($_POST['upload_path']) ) {
					update_option( 'upload_path', $_POST['upload_path'] );
				}
				if ( !empty($_POST['upload_url_path']) ) {
					update_option( 'upload_url_path', $_POST['upload_url_path'] );
				}
				break;
			case 5:
				require_once( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/req/MediaFromFtpCron.php' );
				$mediafromftpcron = new MediaFromFtpCron();
				if ( !empty($_POST['mediafromftp_cron_schedule']) ) {
					if ( !empty($_POST['mediafromftp_cron_apply']) ) {
						$mediafromftp_cron_apply = $_POST['mediafromftp_cron_apply'];
					} else {
						$mediafromftp_cron_apply = FALSE;
					}
					$mediafromftp_tbl = array(
										'searchdir' => $mediafromftp_settings['searchdir'],
										'dateset' => $mediafromftp_settings['dateset'],
										'exclude' => $mediafromftp_settings['exclude'],
										'cron' => array(
													'apply' => $mediafromftp_cron_apply,
													'schedule' => $_POST['mediafromftp_cron_schedule']
													)
										);
					update_option( 'mediafromftp_settings', $mediafromftp_tbl );
					if ( !$mediafromftp_cron_apply ) {
						$mediafromftpcron->CronStop();
					} else {
						$mediafromftpcron->CronStart();
					}
				}
				unset($mediafromftpcron);
				break;
		}

	}

	/* ==================================================
	 * Add js
	 * @since	2.24
	 */
	function add_js(){

// JS
$mediafromftp_add_js = <<<MEDIAFROMFTP1

<!-- BEGIN: Media from FTP -->
<script type="text/javascript">
jQuery('#mediafromftp-tabs').responsiveTabs({
  startCollapsed: 'accordion'
});
</script>
<script type="text/javascript">
jQuery(function(){
  jQuery('.mediafromftp-checkAll').on('change', function() {
    jQuery('.' + this.id).prop('checked', this.checked);
  });
});
</script>
<script type="text/javascript">
jQuery(function(){
MEDIAFROMFTP1;

		for ($i = 0; $i < $this->postcount; $i++) {

$mediafromftp_add_js .= <<<MEDIAFROMFTP2

jQuery('#datetimepicker-mediafromftp
MEDIAFROMFTP2;
			$mediafromftp_add_js .= $i;
$mediafromftp_add_js .= <<<MEDIAFROMFTP3
').datetimepicker({format:'Y-m-d H:i'});
MEDIAFROMFTP3;

		}

$mediafromftp_add_js .= <<<MEDIAFROMFTP4

});
</script>
<!-- END: Media from FTP -->

MEDIAFROMFTP4;

		return $mediafromftp_add_js;

	}

	function modify_attachment_link($markup) {
	    return preg_replace('/^<a([^>]+)>(.*)$/', '<a\\1 target="_blank">\\2', $markup);
	}

}

?>