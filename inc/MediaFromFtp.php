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
	   	foreach(glob($dir . '/*', GLOB_ONLYDIR) as $child_dir) {
	       	if ($tmp = $this->scan_file($child_dir, $extpattern)) {
	           	$list = array_merge($list, $tmp);
	       	}
	   	}

		$excludefile = '-[0-9]*x[0-9]*';	// thumbnail
		if( get_option('mediafromftp_exclude_file') ){
			$excludefile .= '|'.get_option('mediafromftp_exclude_file');
		}

		$pattern = $dir.'/*';
	   	foreach(glob($pattern, GLOB_BRACE) as $file) {
			if (!is_dir($file)){
				if (!preg_match("/".$excludefile."/", $file)) {
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