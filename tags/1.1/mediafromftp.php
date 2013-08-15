<?php
/*
Plugin Name: Media from FTP
Plugin URI: http://wordpress.org/plugins/media-from-ftp/
Version: 1.1
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
	add_shortcode( 'mediafromftp', 'mediafromftp_func' );

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
	<h3><? _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>
	<?
	if ( $adddb <> 'TRUE' ) {
		?>
		<p><? _e('In the following directory, please upload the file :', 'mediafromftp'); ?> <b><span style="color:red"><?php echo $wp_uploads[url]; ?></span></b></p>
		<p><? _e('Find the file for each type.', 'mediafromftp'); ?></p>
		<table>
		<tbody>
		<tr>
		<td><? _e('File type:'); ?></td>
		<td>
		<form method="get" action="<?php echo $scriptname; ?>">
		<input type="hidden" name="page" value="mediafromftp">
		<select name="suffix" onchange="submit(this.form)">
		<?
		$exts = array_keys(wp_get_mime_types());
		foreach ($exts as $ext) {
			$pos = strpos($ext, '|');
			if ($pos === false){
				if( $suffix === '.'.$ext ) {
					?>
					<option value=".<?php echo $ext; ?>" selected><?php echo $ext; ?></option>
					<?
				} else {
					?>
					<option value=".<?php echo $ext; ?>"><?php echo $ext; ?></option>
					<?
				}
			} else {
				$exts2 = explode('|',$ext);
				foreach ($exts2 as $ext2) {
					if( $suffix === '.'.$ext2 ) {
						?>
						<option value=".<?php echo $ext2; ?>" selected><?php echo $ext2; ?></option>
						<?
					} else {
						?>
						<option value=".<?php echo $ext2; ?>"><?php echo $ext2; ?></option>
						<?
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
		<?
	}

	$wp_uploads_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', $wp_uploads['baseurl']);
	$topurl = $wp_uploads_path;

	$wp_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', get_bloginfo('wpurl')).'/';
	$document_root = str_replace($wp_path, '', ABSPATH).$topurl;

	$args = array(
		'post_type' => 'attachment',
		'post_mime_type' => mediafromftp_mime_type($suffix),
		'numberposts' => -1,
		'post_status' => null,
		'post_parent' => $post->ID
		);
	$attachments = get_posts($args);

	$servername = 'http://'.$_SERVER['HTTP_HOST'];
	$files = mediafromftp_scan_file($document_root,$suffix);
	$count = 0;
	$unregister_count = 0;
	foreach ( $files as $file ){
		$new_url = $servername.str_replace(str_replace($wp_path, '', ABSPATH), '', $file);
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
					<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
					<tbody>
					<?
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
					$metadata = NULL;
					if ( preg_match( "/jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico/i", $suffix) ){
						if ( preg_match( "/jpg|jpeg|jpe|gif|png/i", $suffix) ){
							$thumbfile = str_replace($suffix, '-'.get_option('thumbnail_size_w').'x'.get_option('thumbnail_size_h').$suffix, $file);
							$thumbcraetes[$thumbcount] = array(
								'file' => $file,
								'thumbfile' => $thumbfile,
								'suffix' => $suffix
								);
						}
						$metadata = wp_generate_attachment_metadata( $attach_id, get_attached_file( $attach_id ) );
						wp_update_attachment_metadata( $attach_id, $metadata );
					} else {
						wp_update_attachment_metadata( $attach_id, $metadata );
					}
					?>
					<tr><td>
					<?php echo $new_title.$suffix; ?>
					</td></tr>
					<?
				} else {
					?>
						<tr><td>
						<?php echo $new_url; ?>
						</td></tr>
					<?
				}
			}
		}
	}
	?>
	</tbody>
	</table>
	<?

	if ( $adddb === 'TRUE' ) {
		?>
		<p>
		<?php _e('The above file was registered to the media library.', 'mediafromftp'); ?>
		</p>
		<?
	} else {
		if ( $count == 0 ) {
			if ( $unregister_count == 0 ) {
				?>
				<p>
				<?php _e('There is no file that is not registered in the media library.', 'mediafromftp'); ?>
				</p>
				<?
			}
		} else {
			?>
			<p>
			<?php _e('The above file is a file that is not registered in the media library.', 'mediafromftp'); ?>
			</p>
			<?
		}
	}

	if ( $adddb === 'TRUE' ) {
		?>
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
		<?
	} else {
		?>
		<table>
		<tbody>
		<tr>
		<?
		if ( $count > 0 ) {
			?>
			<form method="get" action="<?php echo $scriptname; ?>">
				<td>
				<div class="submit">
					<input type="hidden" name="page" value="mediafromftp">
					<input type="hidden" name="adddb" value="TRUE">
					<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
					<input type="submit" value="<?php _e('Update Media') ?>" />
				</div>
				</td>
			</form>
			<?
		}
		?>
		<form method="get" action="<?php echo $scriptname; ?>">
			<td>
			<div class="submit">
				<input type="hidden" name="page" value="mediafromftp">
				<input type="hidden" name="suffix" value="<?php echo $suffix; ?>">
				<input type="submit" value="<?php _e('Search') ?>" />
			</div>
			</td>
		</form>
		</tr>
		</tbody>
		</table>
		<?
	}

	if ( !empty($unregisters) ) {
		?>
		<p>
		<?php _e('You can not register, because there are spaces in the file below. Please try again with the exception of the spaces. It is a specification for the standard of the media library.', 'mediafromftp'); ?>
		</p>
		<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
		<tbody>
		<?
		foreach ( $unregisters as $unregister_url ) {
			?>
			<tr><td>
			<?php echo $unregister_url; ?>
			</td></tr>
			<?
		}
		?>
		</tbody>
		</table>
		<?
	}

	unset($exts, $exts2 ,$args, $files, $unregisters);

	if( !empty($thumbcraetes) ){
		foreach ( $thumbcraetes as $values ){
			mediafromftp_thumbcreate_gd( $values['file'], $values['thumbfile'], $values['suffix'] );
		}
	}

	unset($thumbcraetes);

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

   	foreach(glob($dir.'/*'.$suffix, GLOB_BRACE) as $file) {
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

/* ==================================================
 * @param	string	$file
 * @param	string	$thumbfile
 * @param	string	$suffix
 * @return	none
 * @since	1.0
 */
function mediafromftp_thumbcreate_gd($file, $thumbfile, $suffix){

	if ( $suffix === '.jpg' || $suffix === '.jpeg' || $suffix === '.jpe') {
		$image = imagecreatefromjpeg( $file );
	} else if ( $suffix === '.gif' ) {
		$image = imagecreatefromgif( $file );
	} else if ( $suffix === '.png' ) {
		$image = imagecreatefrompng( $file );
	}

	$width  = imagesx( $image );
	$height = imagesy( $image );
	if ( $width >= $height ) {
	    $side = $height;
    	$x = floor( ( $width - $height ) / 2 );
	    $y = 0;
    	$width = $side;
	} else {
	    $side = $width;
    	$y = floor( ( $height - $width ) / 2 );
	    $x = 0;
    	$height = $side;
	}
	$thumbnail_width  = get_option('thumbnail_size_w');
	$thumbnail_height = get_option('thumbnail_size_h');
	$thumbnail = imagecreatetruecolor( $thumbnail_width, $thumbnail_height );
	imagecopyresized( $thumbnail, $image, 0, 0, $x, $y, $thumbnail_width, $thumbnail_height, $width, $height );

	if ( $suffix === '.jpg' || $suffix === '.jpeg' || $suffix === '.jpe') {
		imagejpeg( $thumbnail, $thumbfile );
	} else if ( $suffix === '.gif' ) {
		imagegif( $thumbnail, $thumbfile );
	} else if ( $suffix === '.png' ) {
		imagepng( $thumbnail, $thumbfile );
	}

	imagedestroy ( $image );

}

?>