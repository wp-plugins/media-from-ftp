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
		add_management_page('Media from FTP', 'Media from FTP', 8, 'mediafromftp', array($this, 'manage_page'));
	}

	/* ==================================================
	 * Main
	 */
	function manage_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();

		$pluginurl = plugins_url($path='',$scheme=null);

		wp_enqueue_style( 'jquery-ui-tabs', $pluginurl.'/media-from-ftp/css/jquery-ui.css' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-tabs-in', $pluginurl.'/media-from-ftp/js/jquery-ui-tabs-in.js' );
		wp_enqueue_script( 'jquery-check-selectall-in', $pluginurl.'/media-from-ftp/js/jquery-check-selectall-in.js' );

		update_option( 'upload_path', $_POST['upload_path'] );
		update_option( 'upload_url_path', $_POST['upload_url_path'] );

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}
		$wp_uploads = wp_upload_dir();
		$wp_uploads_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', $wp_uploads['baseurl']);
		$topurl = $wp_uploads_path;
		if (!empty($_POST['topurl'])){
			$topurl = str_replace('http://'.$_SERVER["SERVER_NAME"], '', urldecode($_POST['topurl']));
		}

		$scriptname = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?page=mediafromftp';

		?>
		<div class="wrap">

		<h2>Media from FTP</h2>
			<div id="tabs">
				<ul>
				<li><a href="#tabs-1"><?php _e('Search & Register', 'mediafromftp'); ?></a></li>
				<li><a href="#tabs-2"><?php _e('Exclude file', 'mediafromftp'); ?></a></li>
				<li><a href="#tabs-3"><?php _e('Uploading Files'); ?></a></li>
				<!--
				<li><a href="#tabs-4">FAQ</a></li>
				 -->
				</ul>
				<div id="tabs-1">

		<h3><?php _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>

		<?php

		$wp_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', get_bloginfo('wpurl')).'/';
		$server_root = $_SERVER['DOCUMENT_ROOT'];
		$document_root = $server_root.$topurl;
		$dir_root = $server_root.$wp_uploads_path;

		$languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if( substr($languages[0],0,2) === 'ja' ) {
			mb_language('Japanese');
		} else if( substr($languages[0],0,2) === 'en' ) {
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
				$linkdirenc = mb_convert_encoding(str_replace($server_root, "", $linkdir), "UTF-8", "auto");
				if( $topurl === $linkdirenc ){
					$linkdirs = '<option value="'.urlencode($linkdirenc).'" selected>'.$linkdirenc.'</option>';
				}else{
					$linkdirs = '<option value="'.urlencode($linkdirenc).'">'.$linkdirenc.'</option>';
				}
				$linkselectbox = $linkselectbox.$linkdirs;
			}
			if( empty($_POST['topurl']) || $topurl ===  $wp_uploads_path ){
				$linkdirs = '<option value="" selected>'.$wp_uploads_path.'</option>';
			}else{
				$linkdirs = '<option value="">'.$wp_uploads_path.'</option>';
			}
			$linkselectbox = $linkselectbox.$linkdirs;
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
				<table>
				<tbody>
					<tr>
					<td>
					<?php _e('Find the following directories.', 'mediafromftp'); ?>
					</td>
					<td>
						<select name="topurl">
						<?php echo $linkselectbox; ?>
						</select>
					</td>
					<td>
						<div class="submit">
							<input type="submit" value="<?php _e('Search'); ?>" />
						</div>
					</td>
					</tr>
				</tbody>
				</table>
			</form>
			<?php
		}

		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID
			);
		$attachments = get_posts($args);

		$servername = 'http://'.$_SERVER['HTTP_HOST'];
		$extpattern = $mediafromftp->extpattern();
		$files = $mediafromftp->scan_file($document_root, $extpattern);
		$count = 0;
		$post_attachs = array();
		$unregister_space_count = 0;
		$unregister_unwritable_count = 0;
		$unregister_multibyte_file_count = 0;
		foreach ( $files as $file ){
			if ( is_dir($file) ) { // dirctory
				$new_file = FALSE;
			} else {
				$suffix_file = '.'.end(explode('.', end(explode('/', $file)))); 
				$new_url = $servername.str_replace($server_root, '', $file);
				$new_title = str_replace($suffix_file, '', end(explode('/', $new_url)));
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
				if ( strpos($file, ' ' ) ) {
					$unregisters_space[$unregister_space_count] = $new_url;
					++$unregister_space_count;
				} else if ( !is_writable(dirname($file)) && wp_ext2type(end(explode('.', $suffix_file))) === 'image' ) {
					$unregisters_unwritable[$unregister_unwritable_count] = $new_url;
					++$unregister_unwritable_count;
				} else if ( !is_writable(dirname($file)) && strlen($file) <> mb_strlen($file) ) {
					$unregisters_multibyte_file[$unregister_multibyte_file_count] = $new_url;
					++$unregister_multibyte_file_count;
				} else {
					++$count;
					if ( $count == 1 ) {
						?>
						<table>
						<tbody>
						<tr>
						<?php
						if ( $adddb <> 'TRUE' ) {
							?>
							<form method="post" action="<?php echo $scriptname; ?>">
								<td>
									<div class="submit">
										<input type="hidden" name="adddb" value="TRUE">
										<input type="hidden" name="topurl" value="<?php echo $topurl; ?>">
										<input type="submit" value="<?php _e('Update Media'); ?>" />
									</div>
								</td>
							<?php
						}
						?>
						</tr>
						</tbody>
						</table>
						<?php
						if ( $adddb <> 'TRUE' ) {
							?>
							<table cellspacing="0" cellpadding="6">
							<tbody>
							<tr><td>
							<input type="checkbox" id="group_media-from-ftp" class="checkAll"><?php _e('Select all'); ?>
							</td></tr>
							</tbody>
							</table>
							<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
							<tbody>
							<?php
						}
					}
					if ( $adddb <> 'TRUE' ) {
						?>
							<tr><td>
							 <input name="new_url_attaches[]" type="checkbox" value="<?php echo $new_url; ?>" class="group_media-from-ftp"><?php echo $new_url; ?>
							</td></tr>
						<?php
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
				<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
				<tbody>
				<tr>
				<td>Title</td>
				<td>attachment_id</td>
				<td>URL</td>
				<td>FileName</td>
				</tr>
				<?php
				foreach ( $new_url_attaches as $new_url_attach ){
					$suffix_attach_file = '.'.end(explode('.', end(explode('/', $new_url_attach)))); 
					$new_attach_title = str_replace($suffix_attach_file, '', end(explode('/', $new_url_attach)));
					$filename = str_replace($wp_uploads['baseurl'].'/', '', $new_url_attach);
					if (strlen($new_url_attach) <> mb_strlen($new_url_attach)) {
						if ( strpos( $filename ,'/' ) === FALSE ) {
							$currentdir = '';
							$currentfile = str_replace($suffix_attach_file, '', $filename);
						} else {
							$currentfile = end(explode('/', $filename));
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
					$newfile_post = array(
						'post_title' => $new_attach_title,
						'post_content' => '',
						'guid' => $new_url_attach,
						'post_status' => 'inherit', 
						'post_type' => 'attachment',
						'post_mime_type' => $mediafromftp->mime_type($suffix_attach_file)
						);
					$attach_id = wp_insert_attachment( $newfile_post, $filename );

					?>
					<tr>
					<td><?php echo $new_attach_title; ?></td>
					<td><?php echo $attach_id; ?></td>
					<td><?php echo $new_url_attach; ?></td>
					<td><?php echo end(explode('/', $new_url_attach)); ?></td>
					</tr>
					<?php
					echo str_pad(" ",4096);
					ob_end_flush();
					ob_start('mb_output_handler');

					if ( wp_ext2type(end(explode('.', $suffix_attach_file))) === 'image' ){
						$metadata = wp_generate_attachment_metadata( $attach_id, get_attached_file($attach_id) );
					}else if ( wp_ext2type(end(explode('.', $suffix_attach_file))) === 'video' ){
						$metadata = wp_read_video_metadata( get_attached_file($attach_id) );
					}else if ( wp_ext2type(end(explode('.', $suffix_attach_file))) === 'audio' ){
						$metadata = wp_read_audio_metadata( get_attached_file($attach_id) );
					}
					wp_update_attachment_metadata( $attach_id, $metadata );

					ob_flush();
					flush();
				}
				?>
				</tbody>
				</table>
				<p>
				<?php _e('The above file was registered to the media library.', 'mediafromftp'); ?>
				</p>
				<?php
			}

			?>
			<table>
			<tbody>
				<tr>
				<td>
					<form method="post" action="<?php echo $scriptname; ?>">
						<div class="submit">
							<input type="hidden" name="topurl" value="<?php echo $topurl; ?>">
							<input type="submit" value="<?php _e('Back'); ?>" />
						</div>
					</form>
				</td>
				<td>
					<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
						<div class="submit">
							<input type="submit" value="<?php _e('Media Library'); ?>" />
						</div>
					</form>
				</td>
				</tr>
			</tbody>
			</table>
			<?php
		} else {
			if ( $count == 0 && $unregister_space_count == 0 && $unregister_unwritable_count == 0 && $unregister_multibyte_file_count == 0) {
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
				<table>
				<tbody>
					<tr>
						<td>
							<div class="submit">
								<input type="hidden" name="adddb" value="TRUE">
								<input type="hidden" name="topurl" value="<?php echo $topurl; ?>">
								<input type="submit" value="<?php _e('Update Media'); ?>" />
							</div>
						</td>
					</form>
					</tr>
				</tbody>
				</table>
				<?php
				if ( !empty($unregisters_space) ) {
					?>
					<p>
					<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
					<tbody>
					<?php
					foreach ( $unregisters_space as $unregister_space_url ) {
						?>
						<tr>
						<td>
						<?php echo $unregister_space_url; ?>
						</td>
						<td>
						<?php _e('You can not register, because there are spaces in the filename. Please try again with the exception of the spaces. It is a specification for the standard of the media library.', 'mediafromftp'); ?>
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
				if ( !empty($unregisters_unwritable) ) {
					?>
					<p>
					<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
					<tbody>
					<?php
					foreach ( $unregisters_unwritable as $unregister_unwritable_url ) {
						?>
						<tr>
						<td>
						<?php echo $unregister_unwritable_url; ?>
						</td>
						<td>
						<?php _e('Can not register to directory for unwritable, because generating a thumbnail in the case of image files. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
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
					<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
					<tbody>
					<?php
					foreach ( $unregisters_multibyte_file as $unregister_multibyte_file_url ) {
						?>
						<tr>
						<td>
						<?php echo $unregister_multibyte_file_url; ?>
						</td>
						<td>
						<?php _e('Can not register to directory for unwritable, because to delete the previous file by converting in MD5 format from multi-byte file names. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
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

		<div id="tabs-2">
		<div class="wrap">
		<form method="post" action="options.php">
		<?php settings_fields('mediafromftp-settings-group'); ?>
			<h2><?php _e('Exclude file', 'mediafromftp'); ?></h2>
			<p><?php _e('| Specify separated by. Regular expression is possible.', 'mediafromftp'); ?></p>
				<textarea id="mediafromftp_exclude_file" name="mediafromftp_exclude_file" rows="4" cols="40"><?php echo get_option('mediafromftp_exclude_file'); ?></textarea>
			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</p>
		</form>
		</div>
		</div>

		<div id="tabs-3">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname; ?>">
			<h2><?php _e('Uploading Files'); ?></h2>
			<table>
			<tbody>
				<tr>
					<td align="right" valign="middle"><?php _e('Store uploads in this folder'); ?></td>
					<td valign="middle"><input name="upload_path" type="text" id="upload_path" value="<?php echo esc_attr(get_option('upload_path')); ?>" /></td>
					<td align="left" valign="middle"><?php _e('Default is <code>wp-content/uploads</code>'); ?></td>
				</tr>
				<tr>
					<td align="right" valign="middle"><?php _e('Full URL path to files'); ?></td>
					<td valign="middle"><input name="upload_url_path" type="text" id="upload_url_path" value="<?php echo esc_attr( get_option('upload_url_path')); ?>" /></td>
					<td align="left" valign="middle"><?php _e('Configuring this is optional. By default, it should be blank.'); ?></td>
				</tr>
			</tbody>
			</table>
			<p><font color="red"><?php _e('If you change the settings, you must be re-register the file to the media library.', 'mediafromftp'); ?></font></p>
			<p><font color="red"><?php _e('When you want to restore the original settings of the above, please be blank.', 'mediafromftp'); ?></font></p>
			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</p>
		</form>
		</div>
		</div>

		</div>
		</div>
		<?php

	}

}

?>