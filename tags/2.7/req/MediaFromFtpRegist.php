<?php

class MediaFromFtpRegist {

	/* ==================================================
	 * Settings register
	 * @since	2.3
	 */
	function register_settings(){
		register_setting( 'mediafromftp-settings-group', 'mediafromftp_exclude_file');
		add_option('mediafromftp_exclude_file','-[0-9]*x[0-9]*|.php');
	}

}

?>