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

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();

		if ( !empty($_POST['mediafromftp_exclude_file']) ) {
			update_option( 'mediafromftp_exclude_file', $_POST['mediafromftp_exclude_file'] );
		}
		if ( !empty($_POST['upload_path']) ) {
			update_option( 'upload_path', $_POST['upload_path'] );
		}
		if ( !empty($_POST['upload_url_path']) ) {
			update_option( 'upload_url_path', $_POST['upload_url_path'] );
		}

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}

		$wp_uploads = wp_upload_dir();
		$wp_uploads_path = str_replace(site_url('/'), '', $wp_uploads['baseurl']);
		$searchdir = $wp_uploads_path;
		if (!empty($_POST['searchdir'])){
			$searchdir = str_replace(site_url('/'), '', urldecode($_POST['searchdir']));
		}

		$scriptname = admin_url('tools.php?page=mediafromftp');

		?>
		<div class="wrap">

		<h2>Media from FTP</h2>

			<div id="mediafromftp-tabs">
				<ul>
				<li><a href="#mediafromftp-tabs-1"><?php _e('Search & Register', 'mediafromftp'); ?></a></li>
				<li><a href="#mediafromftp-tabs-2"><?php _e('Exclude file', 'mediafromftp'); ?></a></li>
				<li><a href="#mediafromftp-tabs-3"><?php _e('Uploading Files'); ?></a></li>
				<!--
				<li><a href="#mediafromftp-tabs-4">FAQ</a></li>
				 -->
				</ul>
				<div id="mediafromftp-tabs-1">

		<h3><?php _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>

		<?php

		$wordpress_root = ABSPATH;
		$document_root = $wordpress_root.$searchdir;
		$dir_root = $wp_uploads['basedir'];

		// Make tmp dir
		if ( !is_dir( $dir_root.'/media-from-ftp-tmp' ) ) {
			mkdir( $dir_root.'/media-from-ftp-tmp', 0755 );
		}

		if( WPLANG === 'ja' ) {
			mb_language('Japanese');
		} else if( WPLANG === 'en' ) {
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
			$dirs = $mediafromftp->scan_dir($dir_root);
			$linkselectbox = NULL;
			foreach ($dirs as $linkdir) {
				$linkdirenc = mb_convert_encoding(str_replace($wordpress_root, "", $linkdir), "UTF-8", "auto");
				if( $searchdir === $linkdirenc ){
					$linkdirs = '<option value="'.urlencode($linkdirenc).'" selected>'.$linkdirenc.'</option>';
				}else{
					$linkdirs = '<option value="'.urlencode($linkdirenc).'">'.$linkdirenc.'</option>';
				}
				$linkselectbox = $linkselectbox.$linkdirs;
			}
			if( empty($_POST['searchdir']) || $searchdir ===  $wp_uploads_path ){
				$linkdirs = '<option value="" selected>'.$wp_uploads_path.'</option>';
			}else{
				$linkdirs = '<option value="">'.$wp_uploads_path.'</option>';
			}
			$linkselectbox = $linkselectbox.$linkdirs;
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
				<div style="display:block;padding:20px 0">
					<?php _e('Find the following directories.', 'mediafromftp'); ?>
					<select name="searchdir" style="width: 100%">
					<?php echo $linkselectbox; ?>
					</select>
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
		foreach ( $files as $file ){
			if ( is_dir($file) ) { // dirctory
				$new_file = FALSE;
			} else {
				$exts = explode('.', wp_basename($file));
				$ext = end($exts);
				$suffix_file = '.'.$ext;
				$new_url = site_url('/').str_replace($wordpress_root, '', $file);
				$new_titles = explode('/', $new_url);
				$new_title = str_replace($suffix_file, '', end($new_titles));
				$new_title_md5 = md5($new_title);
				$new_url_md5 = str_replace($new_title.$suffix_file, '', $new_url).$new_title_md5.$suffix_file;
				$new_file = TRUE;
				foreach ( $attachments as $attachment ){
					$attach_url = $attachment->guid;
					if ( $attach_url === $new_url || $attach_url === $new_url_md5 ) {
						$new_file = FALSE;
					}
				}
				$new_url = mb_convert_encoding($new_url, "UTF-8", "auto");
			}
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
								<div style="display:block;padding:5px 0">
								<input type="radio" name="dateset" value="new" checked>
								<?php _e('Update to use of the current date/time.', 'mediafromftp'); ?>
								</div>
								<div style="display:block;padding:5px 0">
								<input type="radio" name="dateset" value="server">
								<?php _e('Get the date/time of the file, and updated based on it. Change it if necessary. Get by priority if there is date and time of the Exif information.', 'mediafromftp'); ?>
								</div>
								<?php
								if (get_option( 'uploads_use_yearmonth_folders' )) {
								?>
									<div style="display:block;padding:5px 0">
									<input name="move_yearmonth_folders" type="checkbox" value="1"<?php checked('1', get_option( 'uploads_use_yearmonth_folders' )); ?> />
									<?php _e('Organize my uploads into month- and year-based folders'); ?>
									</div>
								<?php
								}
							?>
							<div class="submit">
								<input type="hidden" name="adddb" value="TRUE">
								<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
								<input type="submit" value="<?php _e('Update Media'); ?>" />
							</div>
							<table class="wp-list-table widefat" border="1">
							<tbody>
							<col span="2" width="50px">
							<tr>
							<td colspan="3">
							<input type="checkbox" id="group_media-from-ftp" class="mediafromftp-checkAll"><?php _e('Select all'); ?>
							</td>
							</tr>
							<tr>
							<td><?php _e('Select'); ?></td>
							<td><?php _e('Thumbnail'); ?></td>
							<td><?php _e('Metadata'); ?></td>
							</tr>
							<?php
						}
					}
					if ( $adddb <> 'TRUE' ) {
							$input_html = NULL;
							$input_html .= '<tr><td>';
							$input_html .= '<input name="new_url_attaches['.$this->postcount.'][url]" type="checkbox" value="'.$new_url.'" class="group_media-from-ftp">';
							$input_html .= '</td>';
							$date = get_date_from_gmt(date("Y-m-d H:i:s", filemtime($file)));

							$metadata_org = NULL;
							if ( wp_ext2type($ext) === 'image' ){
								$cash_thumb_key = md5($new_url);
								$cash_thumb_filename = $dir_root.'/media-from-ftp-tmp/'.$cash_thumb_key.$suffix_file;
								$value_cash = get_transient( $cash_thumb_key );
								if ( $value_cash <> FALSE ) {
									if ( ! file_exists( $cash_thumb_filename )) {
										delete_transient( $cash_thumb_key );
										$value_cash = FALSE;
									}
								}
								if ( $value_cash == FALSE ) {
									if ( ! file_exists( $cash_thumb_filename )) {
										$cash_thumb = wp_get_image_editor( $file );
										if ( ! is_wp_error( $cash_thumb ) ) {
											$cash_thumb->resize( 50 ,50, true );
											$cash_thumb->save( $cash_thumb_filename );
											$view_thumb_url = site_url('/').$wp_uploads_path.'/media-from-ftp-tmp/'.$cash_thumb_key.$suffix_file;
										} else {
											$view_thumb_url = site_url('/'). WPINC . '/images/media/default.png';
										}
										set_transient( $cash_thumb_key, $view_thumb_url, DAY_IN_SECONDS);
									}
								} else {
									$view_thumb_url = $value_cash;
									set_transient( $cash_thumb_key, $value_cash, DAY_IN_SECONDS);
								}
								$exifdata = wp_read_image_metadata( $file );
								if ( $exifdata ) {
									$exif_ux_time = $exifdata['created_timestamp'];
									if ( !empty($exif_ux_time) ) {
										$date = date_i18n( "Y-m-d H:i:s", $exif_ux_time, FALSE );
									}
								}
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

							$input_html .= '<td>';
							$input_html .= '<img width="50" height="50" src="'.$view_thumb_url.'">';
							$input_html .= '</td>';
							$input_html .= '<td>';
							$input_html .= '<div>URL: <a href="'.$new_url.'" target="_blank">'.$new_url.'</a></div>';
							$input_html .= $metadata_org;

							$newdate = substr( $date , 0 , strlen($date)-3 );

							$input_html .= '<div>'.__('Edit date and time').'</div>';
							$input_html .= '<input type="text" id="datetimepicker-mediafromftp'.$this->postcount.'" name="new_url_attaches['.$this->postcount.'][datetime]" value="'.$newdate.'">';

							$input_html .= '</td>';
							$input_html .= '</tr>';

							echo $input_html;

						++$this->postcount;
					}
				}
			}
		}
		?>
		</tbody>
		</table>
		<?php

		if ( $adddb === 'TRUE' ) {
			$new_url_attaches = $_POST["new_url_attaches"];
			if (!empty($new_url_attaches)) {
				?>
				<form method="post" action="<?php echo $scriptname; ?>">
					<div class="submit">
						<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
						<input type="submit" value="<?php _e('Back'); ?>" />
						<?php _e('Please try again pressing Back button, if the processing is stopped on the way.', 'mediafromftp'); ?>
					</div>
				</form>
				<table class="wp-list-table widefat" border="1">
				<tbody>
				<?php

				echo str_pad(' ',4096)."\n";
				ob_end_flush();
				ob_start('mb_output_handler');

				foreach ( $new_url_attaches as $postkey1 => $postval1 ){
					foreach ( $postval1 as $postkey2 => $postval2 ){
						if ( $postkey2 === 'url' ) {
							$new_url_attach = $postval1[$postkey2];
							$exts = explode('.', wp_basename($new_url_attach));
							$ext = end($exts);
							$suffix_attach_file = '.'.$ext;

							// Delete Cash
							if ( wp_ext2type($ext) === 'image' ){
								$del_cash_thumb_key = md5($new_url_attach);
								$del_cash_thumb_filename = $dir_root.'/media-from-ftp-tmp/'.$del_cash_thumb_key.$suffix_attach_file;
								$value_del_cash = get_transient( $del_cash_thumb_key );
								if ( $value_del_cash <> FALSE ) {
									if ( file_exists( $del_cash_thumb_filename )) {
										delete_transient( $del_cash_thumb_key );
										unlink( $dir_root.'/media-from-ftp-tmp/'.$del_cash_thumb_key.$suffix_attach_file );
									}
								}
							}

							$new_attach_titlenames = explode('/', $new_url_attach);
							$new_attach_title = str_replace($suffix_attach_file, '', end($new_attach_titlenames));
							$filename = str_replace($wp_uploads['baseurl'].'/', '', $new_url_attach);
							$postdategmt = date_i18n( "Y-m-d H:i:s", FALSE, TRUE );
							if ( $_POST["dateset"] === 'server' ) {
								$postdategmt = get_gmt_from_date($postval1['datetime'].':00');
							}
							if ( strpos($filename, ' ' ) ) {
								$oldfilename = $filename;
								$filename = str_replace(' ', '-', $oldfilename);
								$new_url_attach = str_replace(' ', '-', $new_url_attach);
								copy( $dir_root.'/'.$oldfilename, $dir_root.'/'.$filename );
								unlink( $dir_root.'/'.$oldfilename );
							}
							if (strlen($new_url_attach) <> mb_strlen($new_url_attach)) {
								if ( strpos( $filename ,'/' ) === FALSE ) {
									$currentdir = '';
									$currentfile = str_replace($suffix_attach_file, '', $filename);
								} else {
									$currentfiles = explode('/', $filename);
									$currentfile = end($currentfiles);
									$currentdir = str_replace($currentfile, '', $filename);
									$currentfile = str_replace($suffix_attach_file, '', $currentfile);
								}
								if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
									$currentdir = mb_convert_encoding($currentdir, "sjis-win", "auto");
									$currentfile = mb_convert_encoding($currentfile, "sjis-win", "auto");
								} else {
									$currentdir = mb_convert_encoding($currentdir, "UTF-8", "auto");
									$currentfile = mb_convert_encoding($currentfile, "UTF-8", "auto");
								}
								$oldfilename = $currentdir.$currentfile.$suffix_attach_file;
								$filename = $currentdir.md5($currentfile).$suffix_attach_file;
								$new_url_attach = $wp_uploads['baseurl'].'/'.$filename;
								copy( $dir_root.'/'.$oldfilename, $dir_root.'/'.$filename );
								unlink( $dir_root.'/'.$oldfilename );
								$filename = mb_convert_encoding($filename, "UTF-8", "auto");
								$new_url_attach = mb_convert_encoding($new_url_attach, "UTF-8", "auto");
							}

							if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
								if (!empty($_POST['move_yearmonth_folders'])){
									if ( $_POST["move_yearmonth_folders"] == 1 ) {
										$y = substr( $postdategmt, 0, 4 );
										$m = substr( $postdategmt, 5, 2 );
										$subdir = "/$y/$m";
										if ( $dir_root.'/'.$filename <> $dir_root.$subdir.'/'.wp_basename($filename) ) {
											if ( !file_exists($dir_root.$subdir) ) {
												mkdir($dir_root.$subdir, 0757, true);
											}
											copy( $dir_root.'/'.$filename, $dir_root.$subdir.'/'.wp_basename($filename) );
											unlink( $dir_root.'/'.$filename );
											$filename = ltrim($subdir, '/').'/'.wp_basename($filename);
											$new_url_attach = $wp_uploads['baseurl'].'/'.$filename;
										}
									}
								}
							}

							$newfile_post = array(
								'post_title' => $new_attach_title,
								'post_content' => '',
								'guid' => $new_url_attach,
								'post_status' => 'inherit', 
								'post_type' => 'attachment',
								'post_mime_type' => $mediafromftp->mime_type($suffix_attach_file)
								);
							$attach_id = wp_insert_attachment( $newfile_post, $filename );

							if ( $_POST["dateset"] === 'server' ) {
								$postdate = get_date_from_gmt($postdategmt);
								$up_post = array(
												'ID' => $attach_id,
												'post_date' => $postdate,
												'post_date_gmt' => $postdategmt,
												'post_modified' => $postdate,
												'post_modified_gmt' => $postdategmt
											);
								wp_update_post( $up_post );
							}

							if ( wp_ext2type($ext) === 'image' ){
								$metadata = wp_generate_attachment_metadata( $attach_id, get_attached_file($attach_id) );
								wp_update_attachment_metadata( $attach_id, $metadata );
								$imagethumburl_base = rtrim($new_url_attach, wp_basename($new_url_attach));
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
								wp_update_attachment_metadata( $attach_id, $metadata );
							}else if ( wp_ext2type($ext) === 'audio' ){
								$metadata = wp_read_audio_metadata( get_attached_file($attach_id) );
								$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
								$length = $metadata['length_formatted'];
								wp_update_attachment_metadata( $attach_id, $metadata );
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
							$output_html .= '<tr><td>';
							$output_html .= '<img width="50" height="50" src="'.$image_attr_thumbnail[0].'">';
							$output_html .= '<div>'.__('Title').': '.$new_attach_title.'</div>';
							$output_html .= '<div>'.__('Permalink:').' '.wp_get_attachment_link($attach_id, '', true, false, get_attachment_link($attach_id)).'</div>';
							$output_html .= '<div>URL: <a href="'.$new_url_attach.'" target="_blank">'.$new_url_attach.'</a></div>';
							$new_url_attachs = explode('/', $new_url_attach);
							$output_html .= '<div>'.__('File name:').' '.end($new_url_attachs).'</div>';

							if ( wp_ext2type($ext) === 'image' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('Images').': ';
								foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
									$output_html .= '[<a href="'.$imagethumburl.'" target="_blank">'.$thumbsize.'</a>]';
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

							$output_html .= '</td>';
							$output_html .= '</tr>';

							echo $output_html;
							ob_flush();
							flush();
						}
					}
				}
				ob_end_clean();
				?>
				</tbody>
				</table>
				<p>
				<?php _e('The above file was registered to the media library.', 'mediafromftp'); ?>
				</p>
				<?php
			}

			?>
			<div class="submit">
			<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
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
						<input type="hidden" name="adddb" value="TRUE">
						<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
						<input type="submit" value="<?php _e('Update Media'); ?>" />
					</div>
					<?php
				if ( !empty($unregisters_unwritable) ) {
					?>
					<p>
					<table class="wp-list-table widefat" border="1">
					<tbody>
					<?php
					foreach ( $unregisters_unwritable as $unregister_unwritable_url ) {
						?>
						<tr>
						<td>
						<div><?php echo $unregister_unwritable_url; ?></div>
						<div>
						<?php _e('Can not register to directory for unwritable, because generating a thumbnail in the case of image files. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
						</div>
						</td>
						</tr>
						<?php
					}
					?>
					</tbody>
					</table>
					</p>
					<?php
				}
				if ( !empty($unregisters_multibyte_file) ) {
					?>
					<p>
					<table class="wp-list-table widefat" border="1">
					<tbody>
					<?php
					foreach ( $unregisters_multibyte_file as $unregister_multibyte_file_url ) {
						?>
						<tr>
						<td>
						<div><?php echo $unregister_multibyte_file_url; ?></div>
						<div>
						<?php _e('Can not register to directory for unwritable, because to delete the previous file by converting in MD5 format from multi-byte file names. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
						</div>
						</td>
						</tr>
						<?php
					}
					?>
					</tbody>
					</table>
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
			<h2><?php _e('Exclude file', 'mediafromftp'); ?></h2>
			<p><?php _e('Regular expression is possible.', 'mediafromftp'); ?></p>
			<textarea id="mediafromftp_exclude_file" name="mediafromftp_exclude_file" rows="4" style="width: 250px;"><?php echo get_option('mediafromftp_exclude_file'); ?></textarea>
			<div class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		</div>

		<div id="mediafromftp-tabs-3">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname.'#mediafromftp-tabs-3'; ?>">
			<h2><?php _e('Uploading Files'); ?></h2>
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