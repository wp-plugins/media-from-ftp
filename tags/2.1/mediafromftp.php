<?php
/*
Plugin Name: Media from FTP
Plugin URI: http://wordpress.org/plugins/media-from-ftp/
Version: 2.1
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
	if (!empty($_POST['adddb'])){
		$adddb = $_POST['adddb'];
	}

	$scriptname = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?page=mediafromftp';

	$pluginurl = plugins_url($path='',$scheme=null);
	$wp_uploads = wp_upload_dir();

	?>
	<div id="icon-tools" class="icon32"><br /></div><h2>Media from FTP</h2>
	<h3><?php _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>
	<?php

	$wp_uploads_path = str_replace('http://'.$_SERVER["SERVER_NAME"], '', $wp_uploads['baseurl']);

	if (empty($_POST['topurl'])){
		$topurl = $wp_uploads_path;
	} else {
		$topurl = str_replace('http://'.$_SERVER["SERVER_NAME"], '', urldecode($_POST['topurl']));
	}

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
		?>
		<p><?php _e('Find the following directories.', 'mediafromftp'); ?></p>
		<?php
		$dirs = mediafromftp_scan_dir($dir_root);
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
		if(empty($_POST['topurl'])){
			$linkdirs = '<option value="" selected>'.$wp_uploads_path.'</option>';
		}else{
			$linkdirs = '<option value="">'.$wp_uploads_path.'</option>';
		}
		$linkselectbox = $linkselectbox.$linkdirs;
		?>
		<form method="post" action="<?php echo $scriptname; ?>">
		<select name="topurl" onchange="submit(this.form)">
		<?php echo $linkselectbox; ?>
		</select>
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

	foreach ( $attachments as $attachment ){
		$suffix_attach = '.'.end(explode('.', end(explode('/', $attachment->guid)))); 
		if ( preg_match( "/jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico/i", $suffix_attach) ){
			$remake_target = wp_get_attachment_metadata( $attachment->ID );
			if ( empty($remake_target) ) {
				$remake_file = $server_root.$wp_uploads_path.str_replace($wp_uploads['baseurl'], '', $attachment->guid);
				$remake_tmp_file = str_replace($suffix_attach, '', $remake_file).'tmp'.$suffix_attach;
				copy($remake_file, $remake_tmp_file);
				wp_delete_attachment( $attachment->ID );
				rename($remake_tmp_file, $remake_file );
			}
		}
	}

	$servername = 'http://'.$_SERVER['HTTP_HOST'];
	$files = mediafromftp_scan_file($document_root);
	$count = 0;
	$post_attachs = array();
	$unregister_count = 0;
	foreach ( $files as $file ){
		if ( is_dir($file) ) { // dirctory
			$new_file = FALSE;
		} else {
			$suffix_file = '.'.end(explode('.', end(explode('/', $file)))); 
			$new_url = $servername.str_replace($server_root, '', $file);
			$new_title = str_replace($suffix_file, '', end(explode('/', $new_url)));
			$new_file = TRUE;
			foreach ( $attachments as $attachment ){
				$attach_file = end(explode('/', $attachment->guid));
				$attach_title = str_replace('.'.end(explode('.', $attach_file)), '', $attach_file);
				$attach_file_md5 = md5($attach_title).'.'.end(explode('.', $attach_file));
				if ( $attach_file === $new_title.$suffix_file || $attach_file_md5 === $new_title.$suffix_file) {
					$new_file = FALSE;
				}
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
					<tr>
					<?php
					if ( $adddb <> 'TRUE' ) {
						?>
						<td>
						<form method="post" action="<?php echo $scriptname; ?>">
							<div class="submit">
								<input type="hidden" name="adddb" value="TRUE">
								<input type="submit" value="<?php _e('Update Media') ?>" />
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
						<table border="1" bordercolor="red" cellspacing="0" cellpadding="5">
						<tbody>
						<?php
					}
				}
				if ( $adddb <> 'TRUE' ) {
					?>
						<tr><td>
						 <input name="new_url_attaches[]" type="checkbox" value="<?php echo $new_url; ?>"><?php echo $new_url; ?>
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
			<?php
			foreach ( $new_url_attaches as $new_url_attach ){
				$suffix_attach_file = '.'.end(explode('.', end(explode('/', $new_url_attach)))); 
				$new_attach_title = str_replace($suffix_attach_file, '', end(explode('/', $new_url_attach)));
				$newfile_post = array(
					'post_title' => $new_attach_title,
					'post_content' => '',
					'guid' => $new_url_attach,
					'post_status' => 'inherit', 
					'post_type' => 'attachment',
					'post_mime_type' => mediafromftp_mime_type($suffix_attach_file)
					);
				$filename = str_replace($wp_uploads['baseurl'].'/', '', $new_url_attach);
				$attach_id = wp_insert_attachment( $newfile_post, $filename );

				?>
				<tr>
				<td>File:
				<?php echo $new_attach_title.$suffix_attach_file; ?>
				</td>
				<td>ID:
				<?php echo $attach_id; ?>
				</td>
				</tr>
				<?php
				echo str_pad(" ",4096);
				ob_end_flush();
				ob_start('mb_output_handler');

				if ( preg_match( "/jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico/i", $suffix_attach_file) ){
					$metadata = wp_generate_attachment_metadata( $attach_id, get_attached_file($attach_id) );
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
				<input type="submit" value="<?php _e('Back') ?>" />
			</div>
		</form>
		</td>
		<td>
		<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
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
				<div class="submit">
					<input type="hidden" name="adddb" value="TRUE">
					<input type="submit" value="<?php _e('Update Media') ?>" />
				</div>
			</form>
			</td>
			<?php
		}
		?>
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
 * @return	array	$list
 * @since	1.0
 */
function mediafromftp_scan_file($dir) {

   	$list = $tmp = array();
   	foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
       	if ($tmp = mediafromftp_scan_file($child_dir)) {
           	$list = array_merge($list, $tmp);
       	}
   	}

	$pattern = $dir.'/*';
   	foreach(glob($pattern, GLOB_BRACE) as $file) {
		if (!preg_match("/-[0-9]*x[0-9]*/", $file)) { // thumbnail
			$list[] = $file;
		}
	}

   	return $list;
}

/* ==================================================
 * @param	string	$dir
 * @return	array	$dirlist
 * @since	2.1
 */
function mediafromftp_scan_dir($dir) {

	$dirlist = $tmp = array();
    foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
	    if ($tmp = mediafromftp_scan_dir($child_dir)) {
   		    $dirlist = array_merge($dirlist, $tmp);
       	}
	}

    foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
		$dirlist[] = $child_dir;
	}

	arsort($dirlist);
	return $dirlist;

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