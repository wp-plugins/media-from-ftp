<?php
/**
 * Media from FTP
 * 
 * @package    Media from FTP
 * @subpackage MediaFromFtp Main Functions
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

class MediaFromFtp {

	/* ==================================================
	 * @param	string	$dir
	 * @return	array	$list
	 * @since	1.0
	 */
	function scan_file($dir, $extpattern) {

	   	$list = $tmp = array();
		$searchdir = glob($dir . '/*', GLOB_ONLYDIR);
		if ( is_array($searchdir) ) {
		   	foreach($searchdir as $child_dir) {
		       	if ($tmp = $this->scan_file($child_dir, $extpattern)) {
		           	$list = array_merge($list, $tmp);
		       	}
		   	}
		}

		// for mediafromftpcmd.php
		$cmdoptions = getopt("s:d:e:t:x:p:c:h");

		$mediafromftp_settings = get_option('mediafromftp_settings');
		$excludefile = '-[0-9]+x[0-9]+\.|media-from-ftp-tmp';	// thumbnail & tmp dir file
		global $blog_id;
		if ( is_multisite() && is_main_site($blog_id) ) {
			$excludefile .= '|\/sites\/';
		}
		if ( isset($cmdoptions['e']) ) {
				$excludefile .= '|'.$cmdoptions['e'];
		} else {
			if( !empty($mediafromftp_settings['exclude']) ){
				$excludefile .= '|'.$mediafromftp_settings['exclude'];
			}
		}

		$ext2typefilter = $mediafromftp_settings['ext2typefilter'];
		if ( isset($cmdoptions['t']) ) {
			$ext2typefilter = $cmdoptions['t'];
		} else {
			if (!empty($_POST['ext2type'])){
				$ext2typefilter = $_POST['ext2type'];
			}
		}

		unset($cmdoptions);

		$searchfile = glob($dir . '/*', GLOB_BRACE);
		if ( is_array($searchfile) ) {
		   	foreach($searchfile as $file) {
				if (!is_dir($file)){
					if (!preg_match("/".$excludefile."/", $file)) {
						$exts = explode('.', $file);
						$ext = end($exts);
						if (preg_match("/".$extpattern."/", $ext)) {
							if ( $ext2typefilter === wp_ext2type($ext) || $ext2typefilter === 'all' ) {
								$list[] = $file;
							}
						}
					}
				}
			}
		}

	   	return $list;

	}

	/* ==================================================
	 * @param	string	$dir
	 * @return	array	$dirlist
	 * @since	2.1
	 */
	function scan_dir($dir) {

		$excludedir = 'media-from-ftp-tmp';	// tmp dir
		global $blog_id;
		if ( is_multisite() && is_main_site($blog_id) ) {
			$excludedir .= '|\/sites\/';
		}

		$dirlist = $tmp = array();
		$searchdir = glob($dir . '/*', GLOB_ONLYDIR);
		if ( is_array($searchdir) ) {
		    foreach($searchdir as $child_dir) {
			    if ($tmp = $this->scan_dir($child_dir)) {
		   		    $dirlist = array_merge($dirlist, $tmp);
		       	}
			}

		    foreach($searchdir as $child_dir) {
				if (!preg_match("/".$excludedir."/", $child_dir)) {
					$dirlist[] = $child_dir;
				}
			}
		}

		arsort($dirlist);
		return $dirlist;

	}

	/* ==================================================
	 * @param	string	$extfilter
	 * @return	string	$extpattern
	 * @since	2.2
	 */
	function extpattern($extfilter){

		$extpattern = NULL;

		if ( $extfilter === 'all' ) {
			$mimes = wp_get_mime_types();
			foreach ($mimes as $ext => $mime) {
				$extpattern .= $ext.'|'.strtoupper($ext).'|';
			}
			$extpattern = substr($extpattern, 0, -1);
		} else {
			$extpattern = $extfilter.'|'.strtoupper($extfilter);
		}

		return $extpattern;

	}

	/* ==================================================
	 * @param	string	$suffix
	 * @return	string	$mimetype
	 * @since	1.0
	 */
	function mime_type($suffix){

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
	 * @param	string	$ext
	 * @param	string	$file
	 * @param	string	$new_url
	 * @return	string 	$view_thumb_url
	 * @since	2.36
	 */
	function create_cash($ext, $file, $new_url){

		$cash_thumb_key = md5($new_url);
		$cash_thumb_filename = MEDIAFROMFTP_PLUGIN_TMP_DIR.'/'.$cash_thumb_key.'.'.$ext;
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
					$cash_thumb->resize( 40 ,40, true );
					$cash_thumb->save( $cash_thumb_filename );
					$view_thumb_url = MEDIAFROMFTP_PLUGIN_TMP_URL.'/'.$cash_thumb_key.'.'.$ext;
				} else {
					$view_thumb_url = MEDIAFROMFTP_PLUGIN_SITE_URL. WPINC . '/images/media/default.png';
				}
			} else {
				if ( file_exists( $cash_thumb_filename )) {
					$view_thumb_url = MEDIAFROMFTP_PLUGIN_TMP_URL.'/'.$cash_thumb_key.'.'.$ext;
				} else {
					$view_thumb_url = MEDIAFROMFTP_PLUGIN_SITE_URL. WPINC . '/images/media/default.png';
				}
			}
			set_transient( $cash_thumb_key, $view_thumb_url, DAY_IN_SECONDS);
		} else {
			$view_thumb_url = $value_cash;
			set_transient( $cash_thumb_key, $value_cash, DAY_IN_SECONDS);
		}

		return $view_thumb_url;

	}

	/* ==================================================
	 * @param	string	$ext
	 * @param	string	$new_url_attach
	 * @return	none
	 * @since	2.36
	 */
	function delete_cash($ext, $new_url_attach){

		if ( wp_ext2type($ext) === 'image' ){
			$del_cash_thumb_key = md5($new_url_attach);
			$del_cash_thumb_filename = MEDIAFROMFTP_PLUGIN_TMP_DIR.'/'.$del_cash_thumb_key.'.'.$ext;
			$value_del_cash = get_transient( $del_cash_thumb_key );
			if ( $value_del_cash <> FALSE ) {
				delete_transient( $del_cash_thumb_key );
				if ( file_exists( $del_cash_thumb_filename )) {
					unlink( $del_cash_thumb_filename );
				}
			}
		}

	}

	/* ==================================================
	 * @param	none
	 * @return	int		$del_cash_count(int)
	 * @since	7.5
	 */
	function delete_all_cash(){

		global $wpdb;
		$search_transients = MEDIAFROMFTP_PLUGIN_TMP_URL;
		$del_transients = $wpdb->get_results("
						SELECT	option_value
						FROM	$wpdb->options
						WHERE	option_value LIKE '%%$search_transients%%'
						");

		$del_cash_count = 0;
		foreach ( $del_transients as $del_transient ) {
			$delfile = pathinfo($del_transient->option_value);
			$del_cash_thumb_key = $delfile['filename'];
			$value_del_cash = get_transient( $del_cash_thumb_key );
			if ( $value_del_cash <> FALSE ) {
				delete_transient( $del_cash_thumb_key );
				++$del_cash_count;
			}
		}

		$del_cash_thumb_filename = MEDIAFROMFTP_PLUGIN_TMP_DIR.'/*.*';
		foreach ( glob($del_cash_thumb_filename) as $val ) {
			unlink($val);
			++$del_cash_count;
		}

		return $del_cash_count;

	}

	/* ==================================================
	 * @param	string	$file
	 * @param	string	$dateset
	 * @return	string	$date
	 * @since	2.36
	 */
	function get_date_check($file, $dateset){

		$date = get_date_from_gmt(date("Y-m-d H:i:s", filemtime($file)));

		if ( $dateset === 'exif' ) {
			// for wp_read_image_metadata
			include_once( ABSPATH . 'wp-admin/includes/image.php' );
			$exifdata = wp_read_image_metadata( $file );

			if ( $exifdata ) {
				$exif_ux_time = $exifdata['created_timestamp'];
				if ( !empty($exif_ux_time) ) {
					$date = date_i18n( "Y-m-d H:i:s", $exif_ux_time, FALSE );
				}
			}
		}

		$date = substr( $date , 0 , strlen($date)-3 );

		return $date;

	}

	/* ==================================================
	 * @param	string	$file
	 * @param	array	$attachments
	 * @return	array	$new_file(bool), $ext(string), $new_url(string)
	 * @since	2.36
	 */
	function input_url($file, $attachments){

		$ext = NULL;
		$new_url = NULL;

		if ( is_dir($file) ) { // dirctory
			$new_file = FALSE;
		} else {
			$exts = explode('.', wp_basename($file));
			$ext = end($exts);
			$suffix_file = '.'.$ext;
			$file = wp_normalize_path($file);
			$upload_path = wp_normalize_path(MEDIAFROMFTP_PLUGIN_UPLOAD_DIR);
			$new_url = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.str_replace($upload_path, '', $file);
			$new_titles = explode('/', $new_url);
			$new_title = str_replace($suffix_file, '', end($new_titles));
			$new_title_md5 = md5($new_title);
			$new_url_md5 = str_replace($new_title.$suffix_file, '', $new_url).$new_title_md5.$suffix_file;
			$new_file = TRUE;
			foreach ( $attachments as $attachment ){
				$attach_url = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.'/'.get_post_meta( $attachment->ID, '_wp_attached_file', true );
				if ( $attach_url === $new_url || $attach_url === $new_url_md5 ) {
					$new_file = FALSE;
				}
			}
			$new_url = mb_convert_encoding($new_url, "UTF-8", "auto");
		}

		return array($new_file, $ext, $new_url);

	}

	/* ==================================================
	 * @param	string	$ext
	 * @param	string	$new_url_attach
	 * @param	string	$new_url_datetime
	 * @param	string	$dateset
	 * @param	bool	$yearmonth_folders
	 * @return	array	$attach_id(int), $new_attach_title(string), $new_url_attach(string), $metadata(array)
	 * @since	2.36
	 */
	function regist($ext, $new_url_attach, $new_url_datetime, $dateset, $yearmonth_folders){

		if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
			$char_code = 'sjis-win';
		} else {
			$char_code = 'UTF-8';
		}

		// Rename and Move file
		$suffix_attach_file = '.'.$ext;
		$new_attach_titlenames = explode('/', $new_url_attach);
		$new_attach_title = str_replace($suffix_attach_file, '', end($new_attach_titlenames));

		$path_parts = pathinfo($new_url_attach);
		$urlpath_dir = wp_make_link_relative($path_parts['dirname']);

		$relation_path_true = strstr(MEDIAFROMFTP_PLUGIN_UPLOAD_PATH, '../');
		if ( !$relation_path_true ) {
			$plus_path = ltrim(str_replace(MEDIAFROMFTP_PLUGIN_UPLOAD_PATH, '', strstr($urlpath_dir, MEDIAFROMFTP_PLUGIN_UPLOAD_PATH)), '/');
		} else {
			$plus_path_tmp = str_replace($urlpath_dir, '', str_replace('../', '', MEDIAFROMFTP_PLUGIN_UPLOAD_PATH).'/');
			$plus_path =  ltrim(str_replace($plus_path_tmp, '', $urlpath_dir), '/');
		}
		if ( !empty($plus_path) ) {
			$plus_path = trailingslashit($plus_path);
		}
		$filename = $plus_path.wp_basename( $new_url_attach );

		$err_copy = TRUE;
		$copy_file_org1 = NULL;
		$copy_file_org2 = NULL;
		$copy_file_org3 = NULL;
		$copy_file_new1 = NULL;
		$copy_file_new2 = NULL;
		$copy_file_new3 = NULL;
		$postdategmt = date_i18n( "Y-m-d H:i:s", FALSE, TRUE );
		if ( $dateset === 'server' || $dateset === 'exif' ) {
			$postdategmt = get_gmt_from_date($new_url_datetime.':00');
		}
		if ( strpos( $filename ,'/' ) === FALSE ) {
			$currentdir = '';
			$currentfile = $filename;
		} else {
			$currentfiles = explode('/', $filename);
			$currentfile = end($currentfiles);
			$currentdir = str_replace($currentfile, '', $filename);
		}
		if ( strpos($currentfile, ' ' ) ) {
			$oldfilename = $filename;
			$currentfile = str_replace(' ', '-', $currentfile);
			$filename = $currentdir.$currentfile;
			$new_url_attach = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.'/'.$filename;
			$copy_file_org1 = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$oldfilename;
			$copy_file_new1 = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$filename;
			$err_copy = @copy( $copy_file_org1, $copy_file_new1 );
			if ( !$err_copy ) {
				return array(-1, $copy_file_org1, MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$plus_path, NULL);
			}
		}
		if ( !mb_check_encoding($new_url_attach, 'ASCII') ) {
			$filename = mb_convert_encoding($filename, $char_code, "auto");
			if ( strpos( $filename ,'/' ) === FALSE ) {
				$currentdir = '';
				$currentfile = str_replace($suffix_attach_file, '', $filename);
			} else {
				$currentfiles = explode('/', $filename);
				$currentfile = end($currentfiles);
				$currentdir = str_replace($currentfile, '', $filename);
				$currentfile = str_replace($suffix_attach_file, '', $currentfile);
			}
			$currentdir = mb_convert_encoding($currentdir, $char_code, "auto");
			$currentfile = mb_convert_encoding($currentfile, $char_code, "auto");

			$oldfilename = $currentdir.$currentfile.$suffix_attach_file;
			$filename = $currentdir.md5($currentfile).$suffix_attach_file;
			$new_url_attach = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.'/'.$filename;
			$copy_file_org2 = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$oldfilename;
			$copy_file_new2 = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$filename;
			$err_copy = @copy( $copy_file_org2, $copy_file_new2 );
			if ( !$err_copy ) {
				if (!empty($copy_file_new1)) {
					$copy_file_org2 = $copy_file_org1;
					unlink( $copy_file_new1 );
				}
				return array(-1, $copy_file_org2, MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$plus_path, NULL);
			}
		}

		// Move YearMonth Folders
		if ( $yearmonth_folders == 1 ) {
			$y = substr( $postdategmt, 0, 4 );
			$m = substr( $postdategmt, 5, 2 );
			$subdir = "/$y/$m";
			$filename_base = wp_basename($filename);
			if ( MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$filename <> MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.$subdir.'/'.$filename_base ) {
				if ( !file_exists(MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.$subdir) ) {
					wp_mkdir_p(MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.$subdir);
				}
				if ( file_exists(MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.$subdir.'/'.$filename_base) ) {
					$filename_base = wp_basename($filename, $suffix_attach_file).date_i18n( "dHis", FALSE, FALSE ).$suffix_attach_file;
				}
				$copy_file_org3 = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.'/'.$filename;
				$copy_file_new3 = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.$subdir.'/'.$filename_base;
				$err_copy = copy( $copy_file_org3, $copy_file_new3 );
				if ( !$err_copy ) {
					if (!empty($copy_file_new1)) {
						$copy_file_org3 = $copy_file_org1;
						unlink( $copy_file_new1 );
					} else if (!empty($copy_file_new2)) {
						$copy_file_org3 = $copy_file_org2;
						unlink( $copy_file_new2 );
					}
					return array(-1, $copy_file_org3, MEDIAFROMFTP_PLUGIN_UPLOAD_DIR.$subdir, NULL);
				}
				$filename = ltrim($subdir, '/').'/'.$filename_base;
				$new_url_attach = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.'/'.$filename;
			}
		}
		if (!empty($copy_file_org1)) { unlink( $copy_file_org1 ); }
		if (!empty($copy_file_org2)) { unlink( $copy_file_org2 ); }
		if (!empty($copy_file_org3)) { unlink( $copy_file_org3 ); }

		$filename = mb_convert_encoding($filename, "UTF-8", "auto");
		$new_url_attach = mb_convert_encoding($new_url_attach, "UTF-8", "auto");

		$mediafromftp_settings = get_option('mediafromftp_settings');

		// File Regist
		$newfile_post = array(
			'post_title' => $new_attach_title,
			'post_content' => '',
			'post_author' => $mediafromftp_settings['cron']['user'],
			'guid' => $new_url_attach,
			'post_status' => 'inherit', 
			'post_type' => 'attachment',
			'post_mime_type' => $this->mime_type($suffix_attach_file)
			);
		$attach_id = wp_insert_attachment( $newfile_post, $filename );

		// Date Time Regist
		if ( $dateset <> 'new' ) {
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

		// for wp_read_audio_metadata and wp_read_video_metadata
		include_once( ABSPATH . 'wp-admin/includes/media.php' );
		// for wp_generate_attachment_metadata
		include_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Meta data Regist
		if ( wp_ext2type($ext) === 'image' ){
			$metadata = wp_generate_attachment_metadata( $attach_id, get_attached_file($attach_id) );
			wp_update_attachment_metadata( $attach_id, $metadata );
		}else if ( wp_ext2type($ext) === 'video' ){
			$metadata = wp_read_video_metadata( get_attached_file($attach_id) );
			wp_update_attachment_metadata( $attach_id, $metadata );
		}else if ( wp_ext2type($ext) === 'audio' ){
			$metadata = wp_read_audio_metadata( get_attached_file($attach_id) );
			wp_update_attachment_metadata( $attach_id, $metadata );
		} else {
			$metadata = NULL;
		}

		return array($attach_id, $new_attach_title, $new_url_attach, $metadata);

	}

	/* ==================================================
	 * @param	string	$ext
	 * @param	string	$attach_id
	 * @param	array	$metadata
	 * @return	array	$imagethumburls(string), $mimetype(string), $length(string), $stamptime(string), $file_size(string)
	 * @since	7.4
	 */
	function output_metadata($ext, $attach_id, $metadata){

		$imagethumburls = array();
		$mimetype = NULL;
		$length = NULL;
		if ( wp_ext2type($ext) === 'image' ){
			$imagethumburl_base = MEDIAFROMFTP_PLUGIN_UPLOAD_URL.'/'.rtrim($metadata['file'], wp_basename($metadata['file']));
			foreach ( $metadata as $key1 => $key2 ){
				if ( $key1 === 'sizes' ) {
					foreach ( $metadata[$key1] as $key2 => $key3 ){
						$imagethumburls[$key2] = $imagethumburl_base.$metadata['sizes'][$key2]['file'];
					}
				}
			}
		}else if ( wp_ext2type($ext) === 'video'||  wp_ext2type($ext) === 'audio' ){
			$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
			$length = $metadata['length_formatted'];
		} else {
			$metadata = NULL;
			$filetype = wp_check_filetype( get_attached_file($attach_id) );
			$mimetype =  $filetype['ext'].'('.$filetype['type'].')';
		}

		$stamptime = get_the_time( 'Y-n-j ', $attach_id ).get_the_time( 'G:i', $attach_id );
		if ( isset( $metadata['filesize'] ) ) {
			$file_size = $metadata['filesize'];
		} else {
			$file_size = filesize( get_attached_file($attach_id) );
		}

		return array($imagethumburls, $mimetype, $length, $stamptime, $file_size);

	}

	/* ==================================================
	 * @param	string	$base
	 * @param	string	$relationalpath
	 * @return	string	realurl
	 * @since	7.7
	 */
	function realurl( $base, $relationalpath ){
	     $parse = array(
	          "scheme" => null,
	          "user" => null,
	          "pass" => null,
	          "host" => null,
	          "port" => null,
	          "query" => null,
	          "fragment" => null
	     );
	     $parse = parse_url( $base );

	     if( strpos($parse["path"], "/", (strlen($parse["path"])-1)) !== false ){
	          $parse["path"] .= ".";
	     }

	     if( preg_match("#^https?://#", $relationalpath) ){
	          return $relationalpath;
	     }else if( preg_match("#^/.*$#", $relationalpath) ){
	          return $parse["scheme"] . "://" . $parse["host"] . $relationalpath;
	     }else{
	          $basePath = explode("/", dirname($parse["path"]));
	          $relPath = explode("/", $relationalpath);
	          foreach( $relPath as $relDirName ){
	               if( $relDirName == "." ){
	                    array_shift( $basePath );
	                    array_unshift( $basePath, "" );
	               }else if( $relDirName == ".." ){
	                    array_pop( $basePath );
	                    if( count($basePath) == 0 ){
	                         $basePath = array("");
	                    }
	               }else{
	                    array_push($basePath, $relDirName);
	               }
	          }
	          $path = implode("/", $basePath);
	          return $parse["scheme"] . "://" . $parse["host"] . $path;
	     }

	}

	/* ==================================================
	 * @param	none
	 * @return	array	$upload_dir, $upload_url, $upload_path
	 * @since	7.8
	 */
	function upload_dir_url_path(){

		$wp_uploads = wp_upload_dir();

		$relation_path_true = strpos($wp_uploads['baseurl'], '../');
		if ( $relation_path_true > 0 ) {
			$relationalpath = substr($wp_uploads['baseurl'], $relation_path_true);
			$basepath = substr($wp_uploads['baseurl'], 0, $relation_path_true);
			$upload_url = $this->realurl($basepath, $relationalpath);
			$upload_dir = wp_normalize_path(realpath($wp_uploads['basedir']));
		} else {
			$upload_url = $wp_uploads['baseurl'];
			$upload_dir = wp_normalize_path($wp_uploads['basedir']);
		}

		if(is_ssl()){
			$upload_url = str_replace('http:', 'https:', $upload_url);
		}

		if ( $relation_path_true > 0 ) {
			$upload_path = $relationalpath;
		} else {
			$wordpress_path = wp_normalize_path(ABSPATH);
			$upload_path = str_replace($wordpress_path, '', $upload_dir);
		}

		$upload_dir = untrailingslashit($upload_dir);
		$upload_url = untrailingslashit($upload_url);
		$upload_path = untrailingslashit($upload_path);

		return array($upload_dir, $upload_url, $upload_path);

	}

	/* ==================================================
	 * @param	none
	 * @return	$siteurl
	 * @since	8.5
	 */
	function siteurl(){
		if ( is_multisite() ) {
			global $blog_id;
			$siteurl = trailingslashit(get_blog_details($blog_id)->siteurl);
		} else {
			$siteurl = site_url('/');
		}
		return $siteurl;
	}

	/* ==================================================
	 * @param	string	$ext2typefilter
	 * @return	array	$extensions
	 * @since	8.2
	 */
	function scan_extensions($ext2typefilter){

		$extensions = array();
		$mimes = wp_get_mime_types();

		foreach ($mimes as $extselect => $mime) {
			if( strpos( $extselect, '|' ) ){
				$extselects = explode('|',$extselect);
				foreach ( $extselects as $extselect2 ) {
					if ( $ext2typefilter === 'all' || $ext2typefilter === wp_ext2type($extselect2) ) {
						$extensions[] = $extselect2;
					}
				}
			} else {
				if ( $ext2typefilter === 'all' || $ext2typefilter === wp_ext2type($extselect) ) {
					$extensions[] = $extselect;
				}
			}
		}

		asort($extensions);
		return $extensions;

	}

	/* ==================================================
	 * @param	int		$attach_id
	 * @param	array	$metadata
	 * @param	string	$exif_text_tag
	 * @return	string	$exif_text
	 * @since	8.9
	 */
	function exifcaption($attach_id, $metadata, $exif_text_tag){

		$exifdatas = array();
		if ( $metadata['image_meta']['title'] ) {
			$exifdatas['title'] = $metadata['image_meta']['title'];
		}
		if ( $metadata['image_meta']['credit'] ) {
			$exifdatas['credit'] = $metadata['image_meta']['credit'];
		}
		if ( $metadata['image_meta']['camera'] ) {
			$exifdatas['camera'] = $metadata['image_meta']['camera'];
		}
		if ( $metadata['image_meta']['caption'] ) {
			$exifdatas['caption'] = $metadata['image_meta']['caption'];
		}
		$exif_ux_time = $metadata['image_meta']['created_timestamp'];
		if ( !empty($exif_ux_time) ) {
			$exifdatas['created_timestamp'] = date_i18n( "Y-m-d H:i:s", $exif_ux_time, FALSE );
		}
		if ( $metadata['image_meta']['copyright'] ) {
			$exifdatas['copyright'] = $metadata['image_meta']['copyright'];
		}
		if ( $metadata['image_meta']['aperture'] ) {
			$exifdatas['aperture'] = 'f/'.$metadata['image_meta']['aperture'];
		}
		if ( $metadata['image_meta']['shutter_speed'] ) {
			if ( $metadata['image_meta']['shutter_speed'] < 1 ) {
				$shutter = round( 1 / $metadata['image_meta']['shutter_speed'] );
				$exifdatas['shutter_speed'] = '1/'.$shutter.'sec';
			} else {
				$exifdatas['shutter_speed'] = $metadata['image_meta']['shutter_speed'].'sec';
			}
		}
		if ( $metadata['image_meta']['iso'] ) {
			$exifdatas['iso'] = 'ISO-'.$metadata['image_meta']['iso'];
		}
		if ( $metadata['image_meta']['focal_length'] ) {
			$exifdatas['focal_length'] = $metadata['image_meta']['focal_length'].'mm';
		}

		$exif_text = NULL;
		if ( $exifdatas ) {
			$exif_text = $exif_text_tag;
			foreach($exifdatas as $item => $exif) {
				$exif_text = str_replace('%'.$item.'%', $exif, $exif_text);
			}
			preg_match_all('/%(.*?)%/', $exif_text, $exif_text_per_match);
			foreach($exif_text_per_match as $key1) {
				foreach($key1 as $key2) {
					$exif_text = str_replace('%'.$key2.'%', '', $exif_text);
				}
			}
		}

		if ( !empty($exif_text) ) {
			// Change DB Attachement post
			global $wpdb;
			$update_array = array(
							'post_excerpt'=> $exif_text
						);
			$id_array= array('ID'=> $attach_id);
			$wpdb->update( $wpdb->posts, $update_array, $id_array, array('%s'), array('%d') );
			unset($update_array, $id_array);
		}

		return $exif_text;

	}

}

?>