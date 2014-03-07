<?php

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

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}

		$scriptname = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?page=mediafromftp';

		$pluginurl = plugins_url($path='',$scheme=null);
		$wp_uploads = wp_upload_dir();

		?>
		<div class="wrap">

		<h2>Media from FTP</h2>
			<div id="tabs">
				<ul>
				<li><a href="#tabs-1"><?php _e('Search & Register', 'mediafromftp'); ?></a></li>
				<li><a href="#tabs-2"><?php _e('Exclude file', 'mediafromftp'); ?></a></li>
				<!--
				<li><a href="#tabs-3">FAQ</a></li>
				 -->
				</ul>
				<div id="tabs-1">

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

		$servername = 'http://'.$_SERVER['HTTP_HOST'];
		$extpattern = $mediafromftp->extpattern();
		$files = $mediafromftp->scan_file($document_root, $extpattern);
		$count = 0;
		$post_attachs = array();
		$unregister_space_count = 0;
		$unregister_unwritable_count = 0;
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
					$unregisters_space[$unregister_space_count] = $new_url;
					++$unregister_space_count;
				} else if ( !is_writable(dirname($file)) && preg_match( "/jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico/i", $suffix_file) ) {
					$unregisters_unwritable[$unregister_unwritable_count] = $new_url;
					++$unregister_unwritable_count;
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
					$newfile_post = array(
						'post_title' => $new_attach_title,
						'post_content' => '',
						'guid' => $new_url_attach,
						'post_status' => 'inherit', 
						'post_type' => 'attachment',
						'post_mime_type' => $mediafromftp->mime_type($suffix_attach_file)
						);
					$filename = str_replace($wp_uploads['baseurl'].'/', '', $new_url_attach);
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
			if ( $count == 0 && $unregister_space_count == 0 && $unregister_unwritable_count == 0 ) {
				?>
				<p>
				<?php _e('There is no file that is not registered in the media library.', 'mediafromftp'); ?>
				</p>
				<?php
			} else {
				if ( $count > 0 ) {
					?>
					<p>
					<?php _e('The above file is a file that is not registered in the media library. And can be registered.', 'mediafromftp'); ?>
					</p>
					<?php
				}
				?>
				<table>
				<tbody>
				<tr>
				<td>
					<div class="submit">
						<input type="hidden" name="adddb" value="TRUE">
						<input type="submit" value="<?php _e('Update Media') ?>" />
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
						<?php _e('You can not register directory is unwritable, because generates a thumbnail In the case of image files. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp'); ?>
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
				<h2><?php _e('Exclude file', 'mediafromftp') ?></h2>	
				<table border="1" bgcolor="#dddddd">
				<tbody>
					<tr>
						<td align="center" valign="middle">
							<textarea id="mediafromftp_exclude_file" name="mediafromftp_exclude_file" rows="4" cols="120"><?php echo get_option('mediafromftp_exclude_file'); ?></textarea>
						</td>
						<td align="left" valign="middle"><?php _e('| Specify separated by. Regular expression is possible.', 'mediafromftp'); ?></td>
					</tr>
				</tbody>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
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