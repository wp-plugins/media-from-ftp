<?php

class MediaFromFtp {

	/* ==================================================
	 * @param	string	$dir
	 * @return	array	$list
	 * @since	1.0
	 */
	function scan_file($dir, $extpattern) {

	   	$list = $tmp = array();
	   	foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
	       	if ($tmp = $this->scan_file($child_dir, $extpattern)) {
	           	$list = array_merge($list, $tmp);
	       	}
	   	}

		$excludefile = get_option('mediafromftp_exclude_file');
		$pattern = $dir.'/*';
	   	foreach(glob($pattern, GLOB_BRACE) as $file) {
			if (!is_dir($file)){
				if (!preg_match("/".$excludefile."/", $file)) { // thumbnail
					if (preg_match("/".$extpattern."/", end(explode('.', $file)))) {
						$list[] = $file;
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

		$dirlist = $tmp = array();
	    foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
		    if ($tmp = $this->scan_dir($child_dir)) {
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
	 * @param	none
	 * @return	string	$extpattern
	 * @since	2.2
	 */
	function extpattern(){

		$mimes = wp_get_mime_types();

		foreach ($mimes as $ext => $mime) {
			$extpattern .= $ext.'|'.strtoupper($ext).'|';
		}
		$extpattern = substr($extpattern, 0, -1);

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

}

?>