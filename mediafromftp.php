<?php
/*
Plugin Name: Media from FTP
Plugin URI: http://wordpress.org/plugins/media-from-ftp/
Version: 1.5
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
	add_filter( 'plugin_action_links', 'mediafromftp_settings_link', 10, 2 );
	add_action('admin_menu', 'mediafromftp_add_pages');

function mediafromftp_add_pages() {
	add_management_page('Media from FTP', 'Media from FTP', 8, 'mediafromftp', 'mediafromftp_manage_page');
}

/* ==================================================
 * Main
 */
function mediafromftp_manage_page() {

	$adddb = FALSE;
	$suffix = '.mp4';
	if (!empty($_GET['adddb'])){
		$adddb = $_GET['adddb'];
	}
	if (!empty($_GET['suffix'])){
		$suffix = $_GET['suffix'];
	}

	$scriptname = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

	$pluginurl = plugins_url($path='',$scheme=null);
	$wp_uploads = wp_upload_dir();

	?>
	<div id="icon-tools" class="icon32"><br /></div><h2>Media from FTP</h2>
	<h3><?php _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>
	<?php
	if ( $adddb <> 'TRUE' ) {
		?>
		<p><?php _e('In the following directory, please upload the file :', 'mediafromftp'); ?> <b><span style="color:red"><?php echo $wp_uploads[url]; ?></span></b></p>
		<p><?php _e('Find the file for each type.', 'mediafromftp'); ?></p>
		<table>
		<tbody>
		<tr>
		<td><?php _e('File type:'); ?></td>
		<td>
		<form method="get" action="<?php echo $scriptname; ?>">
		<input type="hidden" name="page" value="mediafromftp">
		<select name="suffix" onchange="submit(this.form)">
		<?php
		$exts = array_keys(wp_get_mime_types());
		foreach ($exts as $ext) {
			$pos = strpos($ext, '|');
			if ($pos === false){
				if( $suffix === '.'.$ext ) {
					?>
					<option value=".<?php echo $ext; ?>" selected><?php echo $ext; ?></option>
					<?php
				} else {
					?>
					<option value=".<?php echo $ext; ?>"><?php echo $ext; ?></option>
					<?php
				}
			} else {
				$exts2 = explode('|',$ext);
				foreach ($exts2 as $ext2) {
					if( $suffix === '.'.$ext2 ) {
						?>
						<option value=".<?php echo $ext2; ?>" selected><?php echo $ext2; ?></option>
						<?php
					} else {
						?>
						<option value=".<?php echo $ext2; ?>"><?php echo $ext2; ?></option>
						<?php
					}
				}
			}
		}
		?>
		</select>
		</form>
		</td>
		</tbody>
		</table>
		<?php
	}

	$wp_uploads_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', $wp_uploads['baseurl']);
	$topurl = $wp_uploads_path;

	$wp_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', get_bloginfo('wpurl')).'/';
	$document_root = str_replace($wp_path, '', str_replace("\\", "/", ABSPATH)).$topurl;
	if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
		$document_root = mb_convert_encoding($document_root, "sjis-win", "auto");
	} else {
		$document_root = mb_convert_encoding($document_root, "UTF-8", "auto");
	}

	$args = array(
		'post_type' => 'attachment',
		'post_mime_type' => mediafromftp_mime_type($suffix),
		'numberposts' => -1,
		'post_status' => null,
		'post_parent' => $post->ID
		);
	$attachments = get_posts($args);

	foreach ( $attachments as $attachment ){
		if ( preg_match( "/jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico/i", $suffix) ){
			$remake_target = wp_get_attachment_metadata( $attachment->ID );
			if ( empty($remake_target) ) {
				$remake_file = str_replace($wp_path, '', str_replace("\\", "/", ABSPATH)).$wp_uploads_path.str_replace($wp_uploads['baseurl'], '', $attachment->guid);
				$remake_tmp_file = str_replace($suffix, '', $remake_file).'tmp'.$suffix;
				copy($remake_file, $remake_tmp_file);
				wp_delete_attachment( $attachment->ID );
				rename($remake_tmp_file, $remake_file );
			}
		}
	}

	$servername = 'http://'.$_SERVER['HTTP_HOST'];
	$files = mediafromftp_scan_file($document_root,$suffix);
	$count = 0;
	$post_attachs = array();
	$unregister_count = 0;
	foreach ( $files as $file ){
		$new_url = $servername.str_replace(str_replace($wp_path, '', str_replace("\\", "/", ABSPATH)), '', $file);
		$new_title = str_replace($suffix, '', end(explode('/', $new_url)));
		$new_file = TRUE;
		foreach ( $attachments as $attachment ){
			$attach_file = end(explode('/', $attachment->guid));
			$attach_title = str_replace('.'.end(explode('.', $attach_file)), '', $attach_file);
			$attach_file_md5 = md5($attach_title).'.'.end(explode('.', $attach_file));
			if ( $attach_file === $new_title.$suffix || $attach_file_md5 === $new_title.$suffix) {
				$new_file = FALSE;
			}
		}
		if ($new_file) {
			if ( strpos($file, ' ' ) ) {
				$unregisters[$unregister_count] = $new_url;
				++$unregister_count;
			} else {
				++$count;
				if ( $count == 1 ) {
					?>
					<table>
					<tbody>
					<?php
					if ( $adddb <> 'TRUE' ) {
						?>
						<tr>
						<td>
						<form method="get" action="<?php echo $scriptname; ?>">
							<div class="submit">
								<input type="hidden" name="page" value="mediafromftp">
								<input type="hidden" name="adddb" value="TRUE">
								<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
								<input type="submit" value="<?php _e('Update Media') ?>" />
							</div>
						</form>
						</td>
						<?php
					}
					?>
					<td>
					<form method="get" action="<?php echo $scriptname; ?>">
						<div class="submit">
							<input type="hidden" name="page" value="mediafromftp">
							<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
							<input type="submit" value="<?php _e('Search') ?>" />
						</div>
					</form>
					</td>
					</tr>
					</tbody>
					</table>
					<?php
					if ( $adddb <> 'TRUE' ) {
						?>
						<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
						<tbody>
						<?php
					}
				}
				$newfile_post = array(
					'post_title' => $new_title,
					'post_content' => '',
					'guid' => $new_url,
					'post_status' => 'inherit', 
					'post_type' => 'attachment',
					'post_mime_type' => mediafromftp_mime_type($suffix)
					);
				if ( $adddb === 'TRUE' ) {
					$filename = str_replace($wp_uploads['baseurl'].'/', '', $new_url);
					$attach_id = wp_insert_attachment( $newfile_post, $filename );
					$post_attachs[$attach_id] = $new_title;
				} else {
					?>
						<tr><td>
						<?php echo $new_url; ?>
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
		?>
		<p>
		<?php _e('If the output is interrupted, please press the search button.', 'mediafromftp'); ?>
		</p>
		<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
		<tbody>
		<tr>
		<?php
		foreach ($post_attachs as $post_attach_id => $new_title) {
			?>
			<td>File:
			<?php echo $new_title.$suffix; ?>
			</td>
			<td>ID:
			<?php echo $post_attach_id; ?>
			</td>
			<?php
			echo str_pad(" ",4096);
			ob_end_flush();
			ob_start('mb_output_handler');
			if ( preg_match( "/jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico/i", $suffix) ){
				$metadata = wp_generate_attachment_metadata( $post_attach_id, get_attached_file($post_attach_id) );
			}
			wp_update_attachment_metadata( $post_attach_id, $metadata );
			ob_flush();
			flush();
			?>
			</tr>
		</tbody>
		</table>
		<?php
		}
		?>
		<p>
		<?php _e('The above file was registered to the media library.', 'mediafromftp'); ?>
		</p>
		<table>
		<tbody>
		<tr>
		<td>
		<form method="get" action="<?php echo $scriptname; ?>">
			<div class="submit">
				<input type="hidden" name="page" value="mediafromftp">
				<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
				<input type="submit" value="<?php _e('Back') ?>" />
			</div>
		</form>
		</td>
		<td>
		<form method="get" action="<?php echo admin_url( 'upload.php'); ?>">
			<div class="submit">
				<input type="submit" value="<?php _e('Media Library') ?>" />
			</div>
		</form>
		</td>
		</tr>
		</tbody>
		</table>
		<?php
	} else {
		if ( $count == 0 ) {
			if ( $unregister_count == 0 ) {
				?>
				<p>
				<?php _e('There is no file that is not registered in the media library.', 'mediafromftp'); ?>
				</p>
				<p>
				<?php _e('In the case of updating the media, again, please press the search button.', 'mediafromftp'); ?>
				</p>
				<table>
				<tbody>
				<tr>
				<?php
			}
		} else {
			?>
			<p>
			<?php _e('The above file is a file that is not registered in the media library.', 'mediafromftp'); ?>
			</p>
			<table>
			<tbody>
			<tr>
			<td>
			<form method="get" action="<?php echo $scriptname; ?>">
				<div class="submit">
					<input type="hidden" name="page" value="mediafromftp">
					<input type="hidden" name="adddb" value="TRUE">
					<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
					<input type="submit" value="<?php _e('Update Media') ?>" />
				</div>
			</form>
			</td>
			<?php
		}
		?>
		<td>
		<form method="get" action="<?php echo $scriptname; ?>">
			<div class="submit">
				<input type="hidden" name="page" value="mediafromftp">
				<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
				<input type="submit" value="<?php _e('Search') ?>" />
			</div>
		</form>
		</td>
		</tr>
		</tbody>
		</table>
		<?php
	}

	if ( !empty($unregisters) ) {
		?>
		<p>
		<?php _e('You can not register, because there are spaces in the file below. Please try again with the exception of the spaces. It is a specification for the standard of the media library.', 'mediafromftp'); ?>
		</p>
		<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
		<tbody>
		<?php
		foreach ( $unregisters as $unregister_url ) {
			?>
			<tr><td>
			<?php echo $unregister_url; ?>
			</td></tr>
			<?php
		}
		?>
		</tbody>
		</table>
		<?php
	}

}

/* ==================================================
 * Add a "Settings" link to the plugins page
 * @since	1.0
 */
function mediafromftp_settings_link( $links, $file ) {
	static $this_plugin;
	if ( empty($this_plugin) ) {
		$this_plugin = plugin_basename(__FILE__);
	}
	if ( $file == $this_plugin ) {
		$links[] = '<a href="'.admin_url('tools.php?page=mediafromftp').'">'.__( 'Settings').'</a>';
	}
		return $links;
}

/* ==================================================
 * @param	string	$dir
 * @param	string	$suffix
 * @return	array	$list
 * @since	1.0
 */
function mediafromftp_scan_file($dir,$suffix) {

   	$list = $tmp = array();
   	foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
       	if ($tmp = mediafromftp_scan_file($child_dir,$suffix)) {
           	$list = array_merge($list, $tmp);
       	}
   	}

	$pattern = $dir.'/*'.'{'.strtoupper($suffix).','.strtolower($suffix).'}';
   	foreach(glob($pattern, GLOB_BRACE) as $file) {
		if (!preg_match("/-[0-9]*x[0-9]*/", $file)) { // thumbnail
			$list[] = $file;
		}
   	}

   	return $list;
}

/* ==================================================
 * @param	string	$suffix
 * @return	string	$mimetype
 * @since	1.0
 */
function mediafromftp_mime_type($suffix){

	$suffix = str_replace('.', '', $suffix);

	$mimes = wp_get_mime_types();

	foreach ($mimes as $ext => $mime) {
    	if ( preg_match("/".$ext."/i", $suffix) ) {
			$mimetype = $mime;
		}
	}

	return $mimetype;

}

?>