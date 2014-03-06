<?php

	if( !defined('WP_UNINSTALL_PLUGIN') )
    	exit();

	delete_option('mediafromftp_exclude_file');

?>