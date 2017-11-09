<?php
/**
 * Plugin Name: VBL Documents Manager
 * Author Name: Ingrid Strandberg
 * License: GPLv2
 * Description: För Veckobladeriets filer.
 *
 **/

 /** 
 * Based on plugin File Manager
 * by  Aftabul Islam
 * License: GPLv2
 * Description: Manage your file the way you like. You can upload, delete, copy, move, rename, compress, extract files. You don't need to worry about ftp. It is realy simple and easy to use.
 *
 * */

// Directory Seperator
if( !defined( 'DS' ) ){
	
	PHP_OS == "Windows" || PHP_OS == "WINNT" ? define("DS", "\\") : define("DS", "/");
	
} 

// Including elFinder class
require_once('elFinder' . DS . 'elFinder.php');

// Including bootstarter
require_once('BootStart' . DS . '__init__.php');

  class FM2 extends FM_BootStart2 {

    public function __construct($name){

      // Adding Menu
      $this->menu_data = array(
          'type' => 'menu',
          );

      // Adding Ajax
      $this->add_ajax('connector2'); // elFinder ajax call
      $this->add_ajax('valid_directory'); // Checks if the directory is valid or not

      parent::__construct($name);

    }

    /**
     *
     * File manager connector function
     *
     * */
    public function connector2(){

      // Checks if the current user have enough authorization to operate.
      if( !current_user_can('manage_vbl_files') ) die();

      //~ Holds the list of avilable file operations.
      $file_operation_list = array( 
          'open', // Open directory
          'ls',   // File list inside a directory
          'tree', // Subdirectory for required directory
          'parents', // Parent directory for required directory 
          'tmb', // Newly created thumbnail list  
          'size', // Count total file size 
          'mkdir', // Create directory
          'mkfile', // Create empty file
          'rm', // Remove dir/file
          'rename', // Rename file
          'duplicate', // Duplicate file - create copy with "copy %d" suffix
          'paste', // Copy/move files into new destination
          'upload', // Save uploaded file
          'get', // Return file content
          'put', // Save content into text file
          'archive', // Create archive
          'extract', // Extract files from archive
          'search', // Search files
          'info', // File info
          'dim', // Image dimmensions 
          'resize', // Resize image
          'url', // content URL
          'ban', // Ban a user
          'copy', // Copy a file/folder to another location
          'cut', // Cut for file/folder
          'edit', // Edit for files
          'upload', // Upload A file
          'download', // download A file
          );

      // Disabled file operations
      $file_operation_disabled = array( 'url', 'info' );

      // Allowed mime types 
      $mime_allowed = array( 
          'text',
          'image', 
          'video', 
          'audio', 
          'application',
          'model',
          'chemical',
          'x-conference',
          'message',

          );

      $mime_denied = array();

      $opts2 = array(
          'bind' => array(
            '*' => 'logger'
            ),
          'debug' => true,
          'roots' => array(
            array(
              'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
              'path'          => '/srv/www/wp-content/uploads/vbl-documents',                     // path to files (REQUIRED)
              'URL'           => site_url() . '/wp-content/uploads/vbl-documents',                  // URL to files (REQUIRED)
              'uploadDeny'    => $mime_denied,                // All Mimetypes not allowed to upload
              'uploadAllow'   => $mime_allowed,               // Mimetype `image` and `text/plain` allowed to upload
              'uploadOrder'   => array('allow', 'deny'),      // allowed Mimetype `image` and `text/plain` only
              'accessControl' => 'access',
              'disabled'      => $file_operations_disabled    // List of disabled operations
              //~ 'attributes'
              )
            )
          );

      $elFinder = new FM_EL_Finder();
      $elFinder = $elFinder->connect($opts2);
      $elFinder->run();

      die();
    }

  }

/**
 * 
 * @function logger
 * 
 * Logs file file manager actions
 * 
 * */
if (!function_exists('logger'))   {
  function logger($cmd, $result, $args, $elfinder) {

    global $FileManager2;

    $log = sprintf("[%s] %s: %s \n", date('r'), strtoupper($cmd), var_export($result, true));
    $logfile = $FileManager2->upload_path . DS . 'log.txt';
    $dir = dirname($logfile);
    if (!is_dir($dir) && !mkdir($dir)) {
      return;
    }
    if (($fp = fopen($logfile, 'a'))) {
      fwrite($fp, $log);
      fclose($fp);
    }
    return;

    foreach ($result as $key => $value) {
      if (empty($value)) {
        continue;
      }
      $data = array();
      if (in_array($key, array('error', 'warning'))) {
        array_push($data, implode(' ', $value));
      } else {
        if (is_array($value)) { // changes made to files
          foreach ($value as $file) {
            $filepath = (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
            array_push($data, $filepath);
          }
        } else { // other value (ex. header)
          array_push($data, $value);
        }
      }
      $log .= sprintf(' %s(%s)', $key, implode(', ', $data));
    }
    $log .= "\n";

    $logfile = $FileManager2->upload_path . DS . 'log.txt';
    $dir = dirname($logfile);
    if (!is_dir($dir) && !mkdir($dir)) {
      return;
    }
    if (($fp = fopen($logfile, 'a'))) {
      fwrite($fp, $log);
      fclose($fp);
    }
  }
}
global $FileManager2;
$FileManager2 = new FM2('VBL Documents Manager');
