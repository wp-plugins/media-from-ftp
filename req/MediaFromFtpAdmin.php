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

	public $postcount;

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
			$links[] = '<a href="'.admin_url('admin.php?page=mediafromftp').'">Media from FTP</a>';
			$links[] = '<a href="'.admin_url('admin.php?page=mediafromftp-settings').'">'.__( 'Settings').'</a>';
			$links[] = '<a href="'.admin_url('admin.php?page=mediafromftp-search-register').'">'.__('Search & Register', 'mediafromftp').'</a>';
		}
			return $links;
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function add_pages() {
		add_menu_page(
				'Media from FTP',
				'Media from FTP',
				'manage_options',
				'mediafromftp',
				array($this, 'manage_page')
		);
		add_submenu_page(
				'mediafromftp',
				__('Settings'),
				__('Settings'),
				'manage_options',
				'mediafromftp-settings',
				array($this, 'settings_page')
		);
		add_submenu_page(
				'mediafromftp',
				__('Search & Register', 'mediafromftp'),
				__('Search & Register', 'mediafromftp'),
				'manage_options',
				'mediafromftp-search-register',
				array($this, 'search_register_page')
		);
	}

	/* ==================================================
	 * Add Css and Script
	 * @since	2.23
	 */
	function load_custom_wp_admin_style() {
		wp_enqueue_style( 'jquery-datetimepicker', MEDIAFROMFTP_PLUGIN_URL.'/css/jquery.datetimepicker.css' );
		wp_enqueue_style( 'jquery-responsiveTabs', MEDIAFROMFTP_PLUGIN_URL.'/css/responsive-tabs.css' );
		wp_enqueue_style( 'jquery-responsiveTabs-style', MEDIAFROMFTP_PLUGIN_URL.'/css/style.css' );
		wp_enqueue_style( 'mediafromftp',  MEDIAFROMFTP_PLUGIN_URL.'/css/mediafromftp.css' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-datetimepicker', MEDIAFROMFTP_PLUGIN_URL.'/js/jquery.datetimepicker.js', null, '2.3.4' );
		wp_enqueue_script( 'jquery-responsiveTabs', MEDIAFROMFTP_PLUGIN_URL.'/js/jquery.responsiveTabs.min.js' );

	}

	/* ==================================================
	 * Add Script on footer
	 * @since	2.24
	 */
	function load_custom_wp_admin_style2() {
		echo $this->add_js();
	}

	/* ==================================================
	 * Main
	 */
	function manage_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$plugin_datas = get_file_data( MEDIAFROMFTP_PLUGIN_BASE_DIR.'/mediafromftp.php', array('version' => 'Version') );
		$plugin_version = __('Version:').' '.$plugin_datas['version'];

		?>

		<div class="wrap">

		<h2 style="float: left;">Media from FTP</h2>
		<div style="display: block; padding: 10px 10px;">
			<form method="post" style="float: left; margin-right: 1em;" action="<?php echo admin_url('admin.php?page=mediafromftp-settings'); ?>">
				<input type="submit" class="button" value="<?php _e('Settings'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url('admin.php?page=mediafromftp-search-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Search & Register', 'mediafromftp'); ?>" />
			</form>
		</div>
		<div style="clear: both;"></div>

		<h3><?php _e('Register to media library from files that have been uploaded by FTP.', 'mediafromftp'); ?></h3>
		<h4 style="margin: 5px; padding: 5px;">
		<?php echo $plugin_version; ?> |
		<a style="text-decoration: none;" href="https://wordpress.org/support/plugin/media-from-ftp" target="_blank"><?php _e('Support Forums') ?></a> |
		<a style="text-decoration: none;" href="https://wordpress.org/support/view/plugin-reviews/media-from-ftp" target="_blank"><?php _e('Reviews', 'mediafromftp') ?></a>
		</h4>

		<div style="width: 250px; height: 170px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php _e('Please make a donation if you like my work or would like to further the development of this plugin.', 'mediafromftp'); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
<a style="margin: 5px; padding: 5px;" href='https://pledgie.com/campaigns/28307' target="_blank"><img alt='Click here to lend your support to: Various Plugins for WordPress and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/28307.png?skin_name=chrome' border='0' ></a>
		</div>

		</div>
		<?php
	}

	/* ==================================================
	 * Sub Menu
	 */
	function settings_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$submenu = 1;
		$this->options_updated($submenu);

		$def_max_execution_time = ini_get('max_execution_time');
		$scriptname = admin_url('admin.php?page=mediafromftp-settings');
		$mediafromftp_settings = get_option('mediafromftp_settings');

		?>

		<div class="wrap">

		<h2>Media from FTP <?php _e('Settings'); ?>
			<form method="post" style="float: right;" action="<?php echo admin_url('admin.php?page=mediafromftp-search-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Search & Register', 'mediafromftp'); ?>" />
			</form>
		</h2>
		<div style="clear: both;"></div>

		<div id="mediafromftp-settings-tabs">
			<ul>
			<li><a href="#mediafromftp-settings-tabs-1"><?php _e('Settings'); ?></a></li>
			<li><a href="#mediafromftp-settings-tabs-2"><?php _e('Command-line', 'mediafromftp'); ?></a></li>
			</ul>

			<div id="mediafromftp-settings-tabs-1">
			<div style="display: block; padding: 5px 15px">
			<form method="post" action="<?php echo $scriptname; ?>">

				<div class="item-mediafromftp-settings">
					<h3><?php _e('Execution time', 'mediafromftp'); ?></h3>
					<div style="display:block; padding:5px 5px">
						<?php
							$max_execution_time_text = __('The number of seconds a script is allowed to run.', 'mediafromftp').'('.__('The max_execution_time value defined in the php.ini.', 'mediafromftp').'[<font color="red">'.$def_max_execution_time.'</font>]'.')';
							?>
							<div style="float: left;"><?php echo $max_execution_time_text; ?>:<input type="text" name="mediafromftp_max_execution_time" value="<?php echo $mediafromftp_settings['max_execution_time'] ?>" size="3" /></div>
					</div>
					<div style="clear: both;"></div>
				</div>

				<div class="item-mediafromftp-settings">
					<h3><?php _e('Date'); ?></h3>
					<div style="display: block;padding:5px 5px">
					<input type="radio" name="mediafromftp_dateset" value="new" <?php if ($mediafromftp_settings['dateset'] === 'new') echo 'checked'; ?>>
					<?php _e('Update to use of the current date/time.', 'mediafromftp'); ?>
					</div>
					<div style="display: block;padding:5px 5px">
					<input type="radio" name="mediafromftp_dateset" value="server" <?php if ($mediafromftp_settings['dateset'] === 'server') echo 'checked'; ?>>
					<?php _e('Get the date/time of the file, and updated based on it. Change it if necessary.', 'mediafromftp'); ?>
					</div>
					<div style="display: block; padding:5px 5px">
					<input type="radio" name="mediafromftp_dateset" value="exif" <?php if ($mediafromftp_settings['dateset'] === 'exif') echo 'checked'; ?>>
					<?php
					_e('Get the date/time of the file, and updated based on it. Change it if necessary.', 'mediafromftp');
					_e('Get by priority if there is date and time of the Exif information.', 'mediafromftp');
					?>
					</div>
					<div style="display: block; padding:5px 5px">
					<input type="checkbox" name="move_yearmonth_folders" value="1" <?php checked('1', get_option('uploads_use_yearmonth_folders')); ?> />
					<?php _e('Organize my uploads into month- and year-based folders'); ?>
					</div>
				</div>

				<div class="item-mediafromftp-settings">
					<h3><?php _e('Exclude file', 'mediafromftp'); ?></h3>
					<p><?php _e('Regular expression is possible.', 'mediafromftp'); ?></p>

					<textarea id="mediafromftp_exclude" name="mediafromftp_exclude" rows="3" style="width: 100%;"><?php echo $mediafromftp_settings['exclude']; ?></textarea>
					<div style="clear: both;"></div>
				</div>

				<div class="item-mediafromftp-settings">
					<h3><?php _e('Schedule', 'mediafromftp'); ?></h3>
					<div style="display:block;padding:5px 0">
					<?php _e('Set the schedule.', 'mediafromftp'); ?>
					<?php _e('Will take some time until the [Next Schedule] is reflected.', 'mediafromftp'); ?>
					</div>
					<?php
					if ( wp_next_scheduled( 'MediaFromFtpCronHook' ) ) {
						$next_schedule = ' '.get_date_from_gmt(date("Y-m-d H:i:s", wp_next_scheduled( 'MediaFromFtpCronHook' )));
					} else {
						$next_schedule = ' '.__('None');
					}
					?>
					<div style="display:block;padding:5px 0">
					<?php echo __('Next Schedule:', 'mediafromftp').$next_schedule; ?>
					</div>
					<div style="display:block;padding:5px 0">
					<input type="checkbox" name="mediafromftp_cron_apply" value="1" <?php checked('1', $mediafromftp_settings['cron']['apply']); ?> />
					<?php _e('Apply Schedule', 'mediafromftp'); ?>
					</div>
					<div style="display:block;padding:5px 10px">
					<input type="radio" name="mediafromftp_cron_schedule" value="hourly" <?php checked('hourly', $mediafromftp_settings['cron']['schedule']); ?>>
					<?php _e('hourly', 'mediafromftp'); ?>
					</div>
					<div style="display:block;padding:5px 10px">
					<input type="radio" name="mediafromftp_cron_schedule" value="twicedaily" <?php checked('twicedaily', $mediafromftp_settings['cron']['schedule']); ?>>
					<?php _e('twice daily', 'mediafromftp'); ?>
					</div>
					<div style="display:block;padding:5px 10px">
					<input type="radio" name="mediafromftp_cron_schedule" value="daily" <?php checked('daily', $mediafromftp_settings['cron']['schedule']); ?>>
					<?php _e('daily', 'mediafromftp'); ?>
					</div>
					<div style="display:block;padding:5px 10px">
					<input type="checkbox" name="mediafromftp_cron_mail_apply" value="1" <?php checked('1', $mediafromftp_settings['cron']['mail_apply']); ?> />
					<?php _e('E-mail me whenever'); ?>
					</div>
					<div style="display:block;padding:5px 20px">
						<?php echo __('Your E-mail').': '.$mediafromftp_settings['cron']['mail']; ?>
					</div>
				</div>

				<div class="item-mediafromftp-settings">
					<h3><?php _e('Remove Thumbnails Cache', 'mediafromftp'); ?></h3>
					<div style="display:block;padding:5px 0">
						<?php _e('Remove the cache of thumbnail used in the search screen. Please try out if trouble occurs in the search screen. It might become normal.', 'mediafromftp'); ?>
					</div>
				</div>

				<div style="clear: both;"></div>
				<p>
				<div>
					<input type="submit" class="button" style="float: left; margin-right: 1em;" value="<?php _e('Save Changes'); ?>" />
				</div>
			</form>

			<form method="post" action="<?php echo $scriptname; ?>" />
				<input type="hidden" name="mediafromftp_clear_cash" value="1" />
				<div>
				<input type="submit" class="button" value="<?php _e('Remove Thumbnails Cache', 'mediafromftp'); ?>" />
				</div>
			</form>

			</div>
			</div>

			<div id="mediafromftp-settings-tabs-2">
				<h3><?php _e('Command-line', 'mediafromftp'); ?></h3>
				<div style="display:block; padding:5px 10px; font-weight: bold;">
				1. <?php _e('Please [mediafromftpcmd.php] rewrite the following manner.(the line 55 from line 48)', 'mediafromftp'); ?>
				</div>
				<div style="display:block;padding:5px 20px">
				<?php
				$commandline_host = $_SERVER['HTTP_HOST'];
				$commandline_server = $_SERVER['SERVER_NAME'];
				$commandline_wpload = ABSPATH.'wp-load.php';
$commandline_set = <<<COMMANDLINESET

&#x24_SERVER = array(
"HTTP_HOST" => "$commandline_host",
"SERVER_NAME" => "$commandline_server",
"REQUEST_URI" => "/",
"REQUEST_METHOD" => "GET",
"HTTP_USER_AGENT" => "mediafromftp"
            );
require_once('$commandline_wpload');

COMMANDLINESET;
				?>
				<textarea readonly rows="9" style="font-size: 12px; width: 100%;">
				<?php echo $commandline_set; ?>
				</textarea>
				</div>
				<div style="display:block; padding:5px 10px; font-weight: bold;">
				2. <?php _e('The execution of the command line.', 'mediafromftp'); ?>
				</div>
				<div style="display:block; padding:5px 10px;">
				<div>% <code>/usr/bin/php <?php echo MEDIAFROMFTP_PLUGIN_BASE_DIR; ?>/mediafromftpcmd.php</code></div>
				<div style="display:block; padding:5px 15px; color:red;"><code>/usr/bin/php</code> >> <?php _e('Please check with the server administrator.', 'mediafromftp'); ?></div>
					<div style="display:block;padding:5px 20px">
					<li style="font-weight: bold;"><?php _e('command line argument list', 'mediafromftp'); ?></li>
						<div style="display:block;padding:5px 40px">
						<div><code>-s</code> <?php _e('Search directory', 'mediafromftp'); ?></div>
						</div>
							<div style="display:block;padding:5px 60px">
							<div><?php _e('Example:', 'mediafromftp'); ?> <code>-s wp-content/uploads</code></div>
							</div>
						<div style="display:block;padding:5px 40px">
						<div><code>-d</code> <?php _e('Date time settings', 'mediafromftp'); ?> (new, server, exif)</div>
						</div>
							<div style="display:block;padding:5px 60px">
							<div><?php _e('Example:', 'mediafromftp'); ?> <code>-d exif</code></div>
							</div>
						<div style="display:block;padding:5px 40px">
						<div><code>-e</code> <?php _e('Exclude file', 'mediafromftp'); ?> (<?php _e('Regular expression is possible.', 'mediafromftp'); ?>)</div>
						</div>
							<div style="display:block;padding:5px 60px">
							<div><?php _e('Example:', 'mediafromftp'); ?> <code>-e "(.ktai.)|(.backwpup_log.)|(.ps_auto_sitemap.)|.php|.js"</code></div>
							</div>
						<div style="display:block;padding:5px 40px">
						<div><code>-t</code> <?php _e('File type:'); ?> (all, image, audio, video, document, spreadsheet, interactive, text, archive, code)</div>
						</div>
							<div style="display:block;padding:5px 60px">
							<div><?php _e('Example:', 'mediafromftp'); ?> <code>-t image</code></div>
							</div>
						<div style="display:block;padding:5px 40px">
						<div><code>-x</code> <?php _e('File extension' , 'mediafromftp'); ?></div>
						</div>
							<div style="display:block;padding:5px 60px">
							<div><?php _e('Example:', 'mediafromftp'); ?> <code>-x jpg</code></div>
							</div>
					<div><?php _e('If the argument is empty, use the set value of the management screen.', 'mediafromftp'); ?></div>
					</div>
					<div style="display:block;padding:5px 20px">
					<li style="font-weight: bold;"><?php _e('command line switch', 'mediafromftp'); ?></li>
						<div style="display:block;padding:5px 40px">
						<div><code>-h</code> <?php _e('Hides the display of the log.', 'mediafromftp'); ?></div>
						</div>
							<div style="display:block;padding:5px 60px">
							<div><?php _e('Example:', 'mediafromftp'); ?> <code>-h</code></div>
							</div>
					</div>
					<div style="color:red;"><?php _e('Command-line works the at plug-in deactivate.', 'mediafromftp'); ?></div>
				</div>
				<div style="display:block; padding:5px 10px; font-weight: bold;">
				3. <?php _e('Register the command-line to the server cron.', 'mediafromftp'); ?> (<?php _e('Example:', 'mediafromftp'); ?> <?php _e('Run every 10 minutes.', 'mediafromftp'); ?>)
				</div>
				<div style="display:block; padding:5px 10px;">
				<div><code>0,10,20,30,40,50 * * * * /usr/bin/php <?php echo MEDIAFROMFTP_PLUGIN_BASE_DIR; ?>/mediafromftpcmd.php</code></div>
				<div style="display:block; padding:5px 15px; color:red;"><code>/usr/bin/php</code> >> <?php _e('Please check with the server administrator.', 'mediafromftp'); ?></div>
				</div>
			</div>

		</div>
		</div>
		<?php

	}

	/* ==================================================
	 * Sub Menu
	 */
	function search_register_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$def_max_execution_time = ini_get('max_execution_time');

		$submenu = 2;
		$this->options_updated($submenu);

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();
		$mediafromftp_settings = get_option('mediafromftp_settings');
		$pagemax = $mediafromftp_settings['pagemax'];
		$searchdir = $mediafromftp_settings['searchdir'];
		$ext2typefilter = $mediafromftp_settings['ext2typefilter'];
		$extfilter = $mediafromftp_settings['extfilter'];
		$max_execution_time = $mediafromftp_settings['max_execution_time'];

		set_time_limit($max_execution_time);

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}
		if ( !empty($_POST['ShowToPage']) ) {
			echo '<div class="updated"><ul><li>'.__('Settings saved.').'</li></ul></div>';
		}
		if (!empty($_POST['searchdir'])){
			$searchdir = urldecode($_POST['searchdir']);
		}

		$scriptname = admin_url('admin.php?page=mediafromftp-search-register');

		$document_root = ABSPATH.$searchdir;
		if( get_option('WPLANG') === 'ja' ) {
			mb_language('Japanese');
		} else if( get_option('WPLANG') === 'en' ) {
			mb_language('English');
		} else {
			mb_language('uni');
		}
		if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
			$document_root = mb_convert_encoding($document_root, "sjis-win", "auto");
		} else {
			$document_root = mb_convert_encoding($document_root, "UTF-8", "auto");
		}

		?>

		<div class="wrap">

		<h2>Media from FTP <?php _e('Search & Register', 'mediafromftp'); ?>
			<form method="post" style="float: right;" action="<?php echo admin_url('admin.php?page=mediafromftp-settings'); ?>">
				<input type="submit" class="button" value="<?php _e('Settings'); ?>" />
			</form>
		</h2>
		<div style="clear: both;"></div>

		<div id="mediafromftp-loading"><img src="<?php echo MEDIAFROMFTP_PLUGIN_URL.'/css/loading.gif'; ?>"></div>
		<div id="mediafromftp-loading-container">
		<?php
		if ( $adddb <> 'TRUE' ) { // Search mode
			$dirs = $mediafromftp->scan_dir(MEDIAFROMFTP_PLUGIN_UPLOAD_DIR);
			$linkselectbox = NULL;
			foreach ($dirs as $linkdir) {
				$linkdirenc = mb_convert_encoding(str_replace(ABSPATH, "", $linkdir), "UTF-8", "auto");
				if( $searchdir === $linkdirenc ){
					$linkdirs = '<option value="'.urlencode($linkdirenc).'" selected>'.$linkdirenc.'</option>';
				}else{
					$linkdirs = '<option value="'.urlencode($linkdirenc).'">'.$linkdirenc.'</option>';
				}
				$linkselectbox = $linkselectbox.$linkdirs;
			}
			if( $searchdir ===  MEDIAFROMFTP_PLUGIN_UPLOAD_PATH ){
				$linkdirs = '<option value="'.urlencode(MEDIAFROMFTP_PLUGIN_UPLOAD_PATH).'" selected>'.MEDIAFROMFTP_PLUGIN_UPLOAD_PATH.'</option>';
			}else{
				$linkdirs = '<option value="'.urlencode(MEDIAFROMFTP_PLUGIN_UPLOAD_PATH).'">'.MEDIAFROMFTP_PLUGIN_UPLOAD_PATH.'</option>';
			}
			$linkselectbox = $linkselectbox.$linkdirs;
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
				<div style="float:left;"><?php _e('Number of items per page:'); ?><input type="text" name="mediafromftp_pagemax" value="<?php echo $pagemax; ?>" size="3" /></div>
				<input type="submit" name="ShowToPage" class="button" value="<?php _e('Save') ?>" />
				<div style="clear: both;"></div>
				<div>
					<select name="searchdir" style="width: 250px">
					<?php echo $linkselectbox; ?>
					</select>
					<input type="hidden" name="adddb" value="FALSE">
					<input type="submit" class="button" value="<?php _e('Search'); ?>" />
					<span style="margin-right: 1em;"></span>
					<select name="ext2type" style="width: 110px;">
					<option value="all" <?php if ($ext2typefilter === 'all') echo 'selected';?>><?php echo esc_attr( __('All types', 'mediafromftp') ); ?></option> 
					<option value="image" <?php if ($ext2typefilter === 'image') echo 'selected';?>>image</option>
					<option value="audio" <?php if ($ext2typefilter === 'audio') echo 'selected';?>>audio</option>
					<option value="video" <?php if ($ext2typefilter === 'video') echo 'selected';?>>video</option>
					<option value="document" <?php if ($ext2typefilter === 'document') echo 'selected';?>>document</option>
					<option value="spreadsheet" <?php if ($ext2typefilter === 'spreadsheet') echo 'selected';?>>spreadsheet</option>
					<option value="interactive" <?php if ($ext2typefilter === 'interactive') echo 'selected';?>>interactive</option>
					<option value="text" <?php if ($ext2typefilter === 'text') echo 'selected';?>>text</option>
					<option value="archive" <?php if ($ext2typefilter === 'archive') echo 'selected';?>>archive</option>
					<option value="code" <?php if ($ext2typefilter === 'code') echo 'selected';?>>code</option>
					</select>
					<select name="extension" style="width: 120px;">
					<option value="all" <?php if ($extfilter === 'all') echo 'selected';?>><?php echo esc_attr( __('All extensions', 'mediafromftp') ); ?></option>
					<?php
					$mimes = wp_get_mime_types();
					foreach ($mimes as $extselect => $mime) {
						if( strpos( $extselect, '|' ) ){
							$extselects = explode('|',$extselect);
							foreach ( $extselects as $extselect2 ) {
								?>
								<option value="<?php echo $extselect2; ?>" <?php if ($extfilter === $extselect2) echo 'selected';?>><?php echo $extselect2; ?></option>
								<?php
							}
						} else {
							?>
							<option value="<?php echo $extselect; ?>" <?php if ($extfilter === $extselect) echo 'selected';?>><?php echo $extselect; ?></option>
							<?php
						}
					}
					?>
					</select>
					<input type="submit" class="button" value="<?php _e('Filter'); ?>">
				</div>
			</form>
			<?php
			global $wpdb;
			$attachments = $wpdb->get_results("
							SELECT guid
							FROM $wpdb->posts
							WHERE post_type = 'attachment'
							");

			$extpattern = $mediafromftp->extpattern($extfilter);
			$files = $mediafromftp->scan_file($document_root, $extpattern);

			$unregister_unwritable_count = 0;
			$unregister_multibyte_file_count = 0;
			$searchfiles = array();
			$search_ext = array();
			$search_new_url = array();
			$pageallcount = 0;
			foreach ( $files as $file ){
				// Input URL
				list($new_file, $ext, $new_url) = $mediafromftp->input_url($file, $attachments);
				if ($new_file) {
					if ( !is_writable(dirname($file)) && wp_ext2type($ext) === 'image' ) {
						$unregisters_unwritable[$unregister_unwritable_count] = $new_url;
						++$unregister_unwritable_count;
					} else if ( !is_writable(dirname($file)) && strlen($file) <> mb_strlen($file) ) {
						$unregisters_multibyte_file[$unregister_multibyte_file_count] = $new_url;
						++$unregister_multibyte_file_count;
					} else {
						$searchfiles[$pageallcount] = $file;
						$search_ext[$pageallcount] = $ext;
						$search_new_url[$pageallcount] = $new_url;
						++$pageallcount;
					}
				}
			}
			unset($files);

			// pagenation
			if( !empty($_GET['ext2typefilter']) ) {
				$ext2typefilter = $_GET['ext2typefilter'];
			}
			if( !empty($_GET['extfilter']) ) {
				$extfilter = $_GET['extfilter'];
			}
			if (!empty($_GET['p'])){
				$page = $_GET['p'];
			} else {
				$page = 1;
			}
			$pagebegin = (($page - 1) * $pagemax) + 1;
			$pageend = $page * $pagemax;
			$pagelast = ceil($pageallcount / $pagemax);

			if ( $pagelast > 1 ) {
				$this->pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname, $ext2typefilter, $extfilter);
			}
			if ( $pageallcount > 0 ) {
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
			<div class="submit" style="padding-top: 5px; padding-bottom: 5px;">
				<input type="hidden" name="adddb" value="TRUE">
				<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
				<input type="hidden" name="ext2type" value="<?php echo $ext2typefilter; ?>">
				<input type="hidden" name="extension" value="<?php echo $extfilter; ?>">
				<input type="submit" class="button-primary button-large" value="<?php _e('Update Media'); ?>" />
			</div>
			<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
			<input type="checkbox" id="group_media-from-ftp" class="mediafromftp-checkAll"><?php _e('Select all'); ?>
			</div>
			<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
			<?php _e('Select'); ?> & <?php _e('Thumbnail'); ?> & <?php _e('Metadata'); ?>
			</div>
			<div style="clear: both;"></div>
			<?php
			}

			$this->postcount = 0;
			for ( $i = 0; $i < $pageallcount; $i++ ) {
				$file = $searchfiles[$i];
				$ext = $search_ext[$i];
				$new_url = $search_new_url[$i];
				if ( $pagebegin <= $i+1 && $i+1 <= $pageend ) {
					if ( $adddb <> 'TRUE' ) {
							$input_html = NULL;
							$input_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
							$input_html .= '<input name="new_url_attaches['.$this->postcount.'][url]" type="checkbox" value="'.$new_url.'" class="group_media-from-ftp" style="float: left; margin: 5px;">';
							$file_size = size_format(filesize($file));
							$mimetype = $ext.'('.$mediafromftp->mime_type($ext).')';
							if ( wp_ext2type($ext) === 'image' ){
								$view_thumb_url = $mediafromftp->create_cash($ext, $file, $new_url);
							} else if ( wp_ext2type($ext) === 'audio' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/audio.png';
								$metadata_audio = wp_read_audio_metadata( $file );
								$file_size = size_format($metadata_audio['filesize']);
								$mimetype = $metadata_audio['fileformat'].'('.$metadata_audio['mime_type'].')';
								$length = $metadata_audio['length_formatted'];
							} else if ( wp_ext2type($ext) === 'video' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/video.png';
								$metadata_video = wp_read_video_metadata( $file );
								$file_size = size_format($metadata_video['filesize']);
								$mimetype = $metadata_video['fileformat'].'('.$metadata_video['mime_type'].')';
								$length = $metadata_video['length_formatted'];
							} else if ( wp_ext2type($ext) === 'NULL' ) {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/default.png';
							} else {
								$view_thumb_url = site_url('/'). WPINC . '/images/media/'.wp_ext2type($ext).'.png';
							}
							$input_html .= '<img width="40" height="40" src="'.$view_thumb_url.'" style="float: left; margin: 5px;">';
							$input_html .= '<div style="overflow: hidden;">';
							$input_html .= '<div>URL:<a href="'.$new_url.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url.'</a></div>';
							$input_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
							$input_html .= '<div>'.__('File size:').' '.$file_size.'</div>';
							if ( wp_ext2type($ext) === 'audio' || wp_ext2type($ext) === 'video' ) {
								$input_html .= '<div>'.__('Length:').' '.$length.'</div>';
							}
							$date = $mediafromftp->get_date_check($file, $mediafromftp_settings['dateset']);
							if ( $mediafromftp_settings['dateset'] === 'new' ) {
								$input_html .= '<input type="hidden" id="datetimepicker-mediafromftp'.$this->postcount.'" name="new_url_attaches['.$this->postcount.'][datetime]" value="'.$date.'">';
							} else {
								$input_html .= '<div style="float: left; margin: 5px 5px 0px 0px;">'.__('Edit date and time').'</div>';
								$input_html .= '<input type="text" id="datetimepicker-mediafromftp'.$this->postcount.'" name="new_url_attaches['.$this->postcount.'][datetime]" value="'.$date.'" style="width: 160px;">';
							}
							$input_html .= '</div></div>';

							echo $input_html;

						++$this->postcount;
					}
				}
			}
			unset($searchfiles, $search_ext, $search_new_url, $attachments);

			if ( $this->postcount == 0 && $unregister_unwritable_count == 0 && $unregister_multibyte_file_count == 0) {
				echo '<div class="updated"><ul><li>'.__('There is no file that is not registered in the media library.', 'mediafromftp').'</li></ul></div>';
			} else {
				?>
				<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
				<?php _e('Select'); ?> & <?php _e('Thumbnail'); ?> & <?php _e('Metadata'); ?>
				</div>
				<div style="padding-top: 5px; padding-bottom: 5px;">
				<input type="checkbox" id="group_media-from-ftp" class="mediafromftp-checkAll"><?php _e('Select all'); ?>
				</div>
				<div class="submit" style="padding-top: 5px; padding-bottom: 5px;">
					<input type="hidden" name="adddb" value="TRUE">
					<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
					<input type="hidden" name="ext2type" value="<?php echo $ext2typefilter; ?>">
					<input type="hidden" name="extension" value="<?php echo $extfilter; ?>">
					<input type="submit" class="button-primary button-large" value="<?php _e('Update Media'); ?>" />
				</div>
				</form>
				<?php
				if ( $pagelast > 1 ) {
					$this->pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname, $ext2typefilter, $extfilter);
				}
				if ( $this->postcount > 0 ) {
					echo '<div class="updated"><ul><li>'.__('These files is a file that is not registered in the media library. And can be registered.', 'mediafromftp').'</li></ul></div>';
				}
				if ( !empty($unregisters_unwritable) ) {
					foreach ( $unregisters_unwritable as $unregister_unwritable_url ) {
						echo '<div class="error"><ul><li>'.$unregister_unwritable_url.' --> '.__('Can not register to directory for unwritable, because generating a thumbnail in the case of image files. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp').'</li></ul></div>';
					}
				}
				if ( !empty($unregisters_multibyte_file) ) {
					foreach ( $unregisters_multibyte_file as $unregister_multibyte_file_url ) {
						echo '<div class="error"><ul><li>'.$unregister_multibyte_file_url.' --> '.__('Can not register to directory for unwritable, because to delete the previous file by converting in MD5 format from multi-byte file names. Must be writable(757 or 777) of attributes of the directory that contains the files required for registration.', 'mediafromftp').'</li></ul></div>';
					}
				}
			}
		} else { // Register mode ($adddb === 'TRUE')
			$new_url_attaches = $_POST["new_url_attaches"];
			if (!empty($new_url_attaches)) {
				?>
				<div class="submit">
				<form method="post" style="float: left; margin-right: 1em;" action="<?php echo $scriptname; ?>">
					<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
					<input type="hidden" name="ext2type" value="<?php echo $ext2typefilter; ?>">
					<input type="hidden" name="extension" value="<?php echo $extfilter; ?>">
					<input type="submit" class="button" value="<?php _e('Search'); ?>" />
				</form>
				<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
					<input type="submit" class="button" value="<?php _e('Media Library'); ?>" />
				</form>
				</div>
				<div style="clear: both;"></div>
				<?php
				$dateset = $mediafromftp_settings['dateset'];
				$yearmonth_folders = get_option('uploads_use_yearmonth_folders');

				foreach ( $new_url_attaches as $postkey1 => $postval1 ){
					foreach ( $postval1 as $postkey2 => $postval2 ){
						if ( $postkey2 === 'url' ) {
							$new_url_attach = $postval1[$postkey2];
							$new_url_datetime = $postval1['datetime'];
							$exts = explode('.', wp_basename($new_url_attach));
							$ext = end($exts);

							// Delete Cash
							$mediafromftp->delete_cash($ext, $new_url_attach);

							// Regist
							list($attach_id, $new_attach_title, $new_url_attach, $metadata) = $mediafromftp->regist($ext, $new_url_attach, $new_url_datetime, $dateset, $yearmonth_folders);

							// Outputdata
							list($imagethumburls, $mimetype, $length, $stamptime, $file_size) = $mediafromftp->output_metadata($ext, $attach_id, $metadata);

							$image_attr_thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail', true);

							$output_html = NULL;
							$output_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
							$output_html .= '<img width="40" height="40" src="'.$image_attr_thumbnail[0].'" style="float: left; margin: 5px;">';
							$output_html .= '<div style="overflow: hidden;">';
							$output_html .= '<div>'.__('Title').': '.$new_attach_title.'</div>';
							$output_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($attach_id).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($attach_id).'</a></div>';
							$output_html .= '<div>URL: <a href="'.$new_url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url_attach.'</a></div>';
							$new_url_attachs = explode('/', $new_url_attach);
							$output_html .= '<div>'.__('File name:').' '.end($new_url_attachs).'</div>';

							$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
							if ( wp_ext2type($ext) === 'image' ) {
								$output_html .= '<div>'.__('Images').': ';
								foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
									$output_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
								}
								$output_html .= '</div>';
							} else {
								$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
								if ( wp_ext2type($ext) === 'video' || wp_ext2type($ext) === 'audio' ) {
									$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
								}
							}
							$output_html .= '</div></div>';

							echo $output_html;

						}
					}
				}
				echo '<div class="updated"><ul><li>'.__('These files was registered to the media library.', 'mediafromftp').'</li></ul></div>';
			}
			unset($new_url_attaches);

			?>
			<div class="submit">
			<form method="post" style="float: left; margin-right: 1em;" action="<?php echo $scriptname; ?>">
				<input type="hidden" name="searchdir" value="<?php echo $searchdir; ?>">
				<input type="hidden" name="ext2type" value="<?php echo $ext2typefilter; ?>">
				<input type="hidden" name="extension" value="<?php echo $extfilter; ?>">
				<input type="submit" class="button" value="<?php _e('Search'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
				<input type="submit" class="button" value="<?php _e('Media Library'); ?>" />
			</form>
			</div>
			<div style="clear: both;"></div>
			<?php
		}
		?>
		</div>
		</div>
		<?php
	}

	/* ==================================================
	 * Pagenation
	 * @since	5.1
	 * string	$page
	 * string	$pagebegin
	 * string	$pageend
	 * string	$pagelast
	 * string	$scriptname
	 * string	$ext2typefilter
	 * string	$extfilter
	 * return	$html
	 */
	function pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname, $ext2typefilter, $extfilter){

			$pageprev = $page - 1;
			$pagenext = $page + 1;
			$scriptnamefirst = add_query_arg( array('p' => '1', 'ext2typefilter' => $ext2typefilter, 'extfilter' => $extfilter ),  $scriptname);
			$scriptnameprev = add_query_arg( array('p' => $pageprev, 'ext2typefilter' => $ext2typefilter, 'extfilter' => $extfilter ),  $scriptname);
			$scriptnamenext = add_query_arg( array('p' => $pagenext, 'ext2typefilter' => $ext2typefilter, 'extfilter' => $extfilter ),  $scriptname);
			$scriptnamelast = add_query_arg( array('p' => $pagelast, 'ext2typefilter' => $ext2typefilter, 'extfilter' => $extfilter ),  $scriptname);
			?>
			<div class="mediafromftp-pages">
			<span class="mediafromftp-links">
			<?php
			if ( $page <> 1 ){
				?><a title='<?php _e('Go to the first page'); ?>' href='<?php echo $scriptnamefirst; ?>'>&laquo;</a>
				<a title='<?php _e('Go to the previous page'); ?>' href='<?php echo $scriptnameprev; ?>'>&lsaquo;</a>
			<?php
			}
			echo $page; ?> / <?php echo $pagelast;
			?>
			<?php
			if ( $page <> $pagelast ){
				?><a title='<?php _e('Go to the next page'); ?>' href='<?php echo $scriptnamenext; ?>'>&rsaquo;</a>
				<a title='<?php _e('Go to the last page'); ?>' href='<?php echo $scriptnamelast; ?>'>&raquo;</a>
			<?php
			}
			?>
			</span>
			</div>
			<?php

	}

	/* ==================================================
	 * Update wp_options table.
	 * @param	string	$submenu
	 * @since	2.36
	 */
	function options_updated($submenu){

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/req/MediaFromFtpCron.php';
		$mediafromftpcron = new MediaFromFtpCron();

		include_once MEDIAFROMFTP_PLUGIN_BASE_DIR.'/inc/MediaFromFtp.php';
		$mediafromftp = new MediaFromFtp();

		$mediafromftp_settings = get_option('mediafromftp_settings');

		switch ($submenu) {
			case 1:
				if ( !empty($_POST['mediafromftp_dateset']) ) {
					if ( !empty($_POST['mediafromftp_cron_apply']) ) {
						$mediafromftp_cron_apply = $_POST['mediafromftp_cron_apply'];
					} else {
						$mediafromftp_cron_apply = FALSE;
					}
					if ( !empty($_POST['mediafromftp_cron_mail_apply']) ) {
						$mediafromftp_cron_mail_apply = $_POST['mediafromftp_cron_mail_apply'];
					} else {
						$mediafromftp_cron_mail_apply = FALSE;
					}
					$mediafromftp_tbl = array(
										'pagemax' => $mediafromftp_settings['pagemax'],
										'searchdir' => $mediafromftp_settings['searchdir'],
										'ext2typefilter' => $mediafromftp_settings['ext2typefilter'],
										'extfilter' => $mediafromftp_settings['extfilter'],
										'dateset' => $_POST['mediafromftp_dateset'],
										'max_execution_time' => intval($_POST['mediafromftp_max_execution_time']),
										'exclude' => $_POST['mediafromftp_exclude'],
										'cron' => array(
													'apply' => $mediafromftp_cron_apply,
													'schedule' => $_POST['mediafromftp_cron_schedule'],
													'mail_apply' => $mediafromftp_cron_mail_apply,
													'mail' => $mediafromftp_settings['cron']['mail']
													)
										);
					update_option( 'mediafromftp_settings', $mediafromftp_tbl );
					if ( !empty($_POST['move_yearmonth_folders']) ) {
						update_option( 'uploads_use_yearmonth_folders', $_POST['move_yearmonth_folders'] );
					} else {
						update_option( 'uploads_use_yearmonth_folders', '0' );
					}
					if ( !$mediafromftp_cron_apply ) {
						$mediafromftpcron->CronStop();
					} else {
						$mediafromftpcron->CronStart();
					}
					echo '<div class="updated"><ul><li>'.__('Settings').' --> '.__('Changes saved.').'</li></ul></div>';
				}
				if ( !empty($_POST['mediafromftp_clear_cash']) ) {
					$del_cash_count = $mediafromftp->delete_all_cash();
					if ( $del_cash_count > 0 ) {
						echo '<div class="updated"><ul><li>'.__('Thumbnails Cache', 'mediafromftp').' --> '.__('Delete').'</li></ul></div>';
					} else {
						echo '<div class="error"><ul><li>'.__('No Thumbnails Cache', 'mediafromftp').'</li></ul></div>';
					}
				}
				break;
			case 2:
				if (!empty($_POST['ShowToPage'])){
					$pagemax = intval($_POST['mediafromftp_pagemax']);
				} else {
					$pagemax = $mediafromftp_settings['pagemax'];
				}
				if (!empty($_POST['searchdir'])){
					$searchdir = urldecode($_POST['searchdir']);
				} else {
					$searchdir = $mediafromftp_settings['searchdir'];
				}
				if (!empty($_POST['ext2type'])){
					$ext2typefilter = $_POST['ext2type'];
					$extfilter = $_POST['extension'];
				} else {
					$ext2typefilter = $mediafromftp_settings['ext2typefilter'];
					$extfilter = $mediafromftp_settings['extfilter'];
				}
				$mediafromftp_tbl = array(
									'pagemax' => $pagemax,
									'searchdir' => $searchdir,
									'ext2typefilter' => $ext2typefilter,
									'extfilter' => $extfilter,
									'dateset' => $mediafromftp_settings['dateset'],
									'max_execution_time' => $mediafromftp_settings['max_execution_time'],
									'exclude' => $mediafromftp_settings['exclude'],
									'cron' => array(
												'apply' => $mediafromftp_settings['cron']['apply'],
												'schedule' => $mediafromftp_settings['cron']['schedule']
												)
									);
				update_option( 'mediafromftp_settings', $mediafromftp_tbl );
				break;
		}

	}

	/* ==================================================
	 * Add js
	 * @since	2.24
	 */
	function add_js(){

// JS
$mediafromftp_add_js = <<<MEDIAFROMFTP1

<!-- BEGIN: Media from FTP -->
<script type="text/javascript">
jQuery(function(){
  jQuery('.mediafromftp-checkAll').on('change', function() {
    jQuery('.' + this.id).prop('checked', this.checked);
  });
});
</script>
<script type="text/javascript">
jQuery('#mediafromftp-settings-tabs').responsiveTabs({
  startCollapsed: 'accordion'
});
</script>
<script type="text/javascript">
jQuery(function(){
MEDIAFROMFTP1;

		for ($i = 0; $i < $this->postcount; $i++) {

$mediafromftp_add_js .= <<<MEDIAFROMFTP2

jQuery('#datetimepicker-mediafromftp
MEDIAFROMFTP2;
			$mediafromftp_add_js .= $i;
$mediafromftp_add_js .= <<<MEDIAFROMFTP3
').datetimepicker({format:'Y-m-d H:i'});
MEDIAFROMFTP3;

		}

$mediafromftp_add_js .= <<<MEDIAFROMFTP4

});
</script>
<script type="text/javascript">
window.addEventListener( "load", function(){
  jQuery("#mediafromftp-loading").delay(2000).fadeOut();
  jQuery("#mediafromftp-loading-container").delay(2000).fadeIn();
}, false );
</script>
<!-- END: Media from FTP -->

MEDIAFROMFTP4;

		return $mediafromftp_add_js;

	}

	function modify_attachment_link($markup) {
	    return preg_replace('/^<a([^>]+)>(.*)$/', '<a\\1 target="_blank">\\2', $markup);
	}

}

?>