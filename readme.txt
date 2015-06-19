=== Media from FTP ===
Contributors: Katsushi Kawamori
Donate link: http://pledgie.com/campaigns/28307
Tags: admin, attachment, attachments, ftp, gallery, image preview, image upload, images, import, importer, media, media library, schedule, sync, synchronize, upload, uploader
Requires at least: 3.6.0
Tested up to: 4.2
Stable tag: 7.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Register to media library from files that have been uploaded by FTP.

== Description ==

* Register to media library from files that have been uploaded by FTP.
* This create a thumbnail of the image file.
* This create a metadata(Images, Videos, Audios).
* Change the date/time.
* Work with [DateTimePicker](http://xdsoft.net/jqplugins/datetimepicker/). jQuery plugin select date/time.
* If use the Schedule options, can periodically run.
* The execution of the command line is supported.(mediafromftpcmd.php)

Why I made this?
In the media uploader, you may not be able to upload by the environment of server.
That's when the files are large.
You do not mind the size of the file if FTP.

Translators
*   Japanese (ja) - [<a href="http://riverforest-wp.info/">Katsushi Kawamori</a>]

== Installation ==

1. Upload `mediafromftp` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. Settings
2. Search file display
3. Registration file selection
4. File registration result

== Changelog ==

= 7.6 =
Add thumbnails cache remove to uninstall script.
Fixed problem of remove thumbnails cache.

= 7.5 =
Add remove thumbnails cache.
Change /languages.

= 7.4 =
Remove the unnecessary code.
Add the send mail function of scheduler log.
Log display on the command-line.
Fixed display.
Change /languages.

= 7.3 =
Fixed display.
Change /languages.

= 7.2 =
Fixed display.

= 7.1 =
Fixed display.
Change /languages.

= 7.0 =
Separate the Search and Register page and Settings page. 
Fixed problem of database read.
Fixed problem of search & register screen.

= 6.3 =
Add FileExtension filter.
Change Command-line manual.
Change /languages.

= 6.2 =
Remove item of the change of upload directory.
Fixed display.

= 6.1 =
Fixed problem of max_execution_time.

= 6.0 =
Add pagination.
Add progress display.
Command-line works the at plug-in deactivate.
Change /languages.

= 5.0 =
Add FileType filter.
Fixed problem of saved search directory.
Add command line argument for FileType.

= 4.4 =
Add command line argument for Exclude file.

= 4.3 =
Add command line argument.

= 4.2 =
Fixed problem of read of FileType for video and audio.
Fixed problem of read of metadata for video and audio.
Add a run in the command line.

= 4.1 =
Fixed problem of file copy at SSL connection.

= 4.0 =
Fixed problem of overwriting when there is a file with the same name in years month based folders.
Optimization.

= 3.9 =
Can set the number of seconds a script is allowed to run.
Change /languages.

= 3.8 =
Fixed problem of the display of multi-byte characters.

= 3.7 =
Change the display of the message.
Change /languages.

= 3.6 =
Fixed a problem of the cache files for the thumbnail of the search screen.
Add screen of donate.
Change readme.txt.
Change /languages.

= 3.5 =
Fixed a problem of management screen.

= 3.4 =
Fixed a problem of management screen.

= 3.3 =
Fixed a problem of management screen.

= 3.2 =
Divided the setting section.
Change /languages.

= 3.1 =
Remove unnecessary code.
Change readme.txt.

= 3.0 =
Add Schedule function.
Change /languages.

= 2.37 =
Fixed a problem of cash.

= 2.36 =
Add to save the setting.

= 2.35 =
Fixed display.

= 2.34 =
Be able to automatic deletion the cache files for the thumbnail of the search screen.

= 2.33 =
Fixed a problem of search screen.

= 2.32 =
In the search screen, display thumbnails and metadata.

= 2.31 =
Set the maximum execution time of the script to 300 seconds.
Fixed a problem of get the Exif information.

= 2.30 =
Can get the Exif information.
Change /languages.

= 2.29 =
Additional note about the change of upload directory.
Change /languages.

= 2.28 =
Fixed a problem of Java Script.
Modification of the appearance of the select boxes of directory.

= 2.27 =
Fixed a problem of when save the exclude file.
Modification of the appearance of the select boxes of directory.

= 2.26 =
Change management screen to responsive tab menu design.
Change /languages.

= 2.25 =
Fixed a problem of management screen.

= 2.24 =
Change the date/time.

= 2.23 =
Attachments organize into month- and year-based folders by automatic.

= 2.22 =
Change output information for images.

= 2.21 =
Fixed the problem of get the site address.

= 2.20 =
Fixed a problem of search of files on virtualhost.

= 2.19 =
Can update to use of time stamp of the file.
Change /languages.

= 2.18 =
Fixed of problem of file search and directory search.

= 2.17 =
Fixed of problem of file name with spaces.
Change /languages.

= 2.16 =
Fixed of problem of error in debug mode.

= 2.15 =
Enrich the output information.
For when the process is stopped in the middle, added the back button.
Change /languages.

= 2.14 =
Fixed a problem of search by same filename of different directoryname.
Fixed CSS.

= 2.13 =
Add setting for directory of uploading files.
Change /languages.

= 2.12 =
Add generate metadata for video / audio.

= 2.11 =
Add select all button.

= 2.10 =
Change readme.txt.

= 2.9 =
Add screenshot.
Change readme.txt.

= 2.7 =
Supported multi-byte-directoryname and multi-byte-filename.
Change /languages.

= 2.6 =
Add search button.

= 2.5 =
Fixed display.

= 2.4 =
Adding a message to a file that can not be registered.
Delete unnecessary code.
Change /languages.

= 2.3 =
Add a setting of exclude file.
Change /languages.

= 2.2 =
Find the only file types that can be registered in the media library.

= 2.1 =
Can the selection of directories to search.
Change /languages.

= 2.0 =
Can select a file in the check box.
Change /languages.
Change readme.txt.

= 1.5 =
Supported Xampp(Microsoft Windows).
Supported the file extension of the upper case.
Change /languages.

= 1.4 =
Delete unnecessary code.

= 1.3 =
Fixed the problem of metadata that occur at the time of registration of the image file.

= 1.2 =
Fixed the problem of thumbnail creation.

= 1.1 =
Specifications to exclude files that contain spaces.

= 1.0 =

== Upgrade Notice ==

= 7.6 =
= 7.5 =
= 7.4 =
= 7.3 =
= 7.2 =
= 7.1 =
= 7.0 =
= 6.3 =
= 6.2 =
= 6.1 =
= 6.0 =
= 5.0 =
= 4.4 =
= 4.3 =
= 4.2 =
= 4.1 =
= 4.0 =
= 3.9 =
= 3.8 =
= 3.7 =
= 3.6 =
= 3.5 =
= 3.4 =
= 3.3 =
= 3.2 =
= 3.1 =
= 3.0 =
= 2.37 =
= 2.36 =
= 2.35 =
= 2.34 =
= 2.33 =
= 2.32 =
= 2.31 =
= 2.30 =
= 2.29 =
= 2.28 =
= 2.27 =
= 2.26 =
= 2.25 =
= 2.24 =
= 2.23 =
= 2.22 =
= 2.21 =
= 2.20 =
= 2.19 =
= 2.18 =
= 2.17 =
= 2.16 =
= 2.15 =
= 2.14 =
= 2.13 =
= 2.12 =
= 2.11 =
= 2.10 =
= 2.9 =
= 2.7 =
= 2.6 =
= 2.5 =
= 2.4 =
= 2.3 =
= 2.2 =
= 2.1 =
= 2.0 =
= 1.5 =
= 1.4 =
= 1.3 =
= 1.2 =
= 1.1 =
= 1.0 =

