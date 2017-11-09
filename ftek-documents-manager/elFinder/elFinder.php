<?php
/**
 * 
 * Security check. No one can access without Wordpress itself
 * 
 * */
defined('ABSPATH') or die();

// Including necessary files
include_once( 'php' . DS . 'elFinderConnector.class.php' );
include_once( 'php' . DS . 'elFinder.class.php' );
include_once( 'php' . DS . 'elFinderVolumeDriver.class.php' );
include_once( 'php' . DS . 'elFinderVolumeLocalFileSystem.class.php' );

/**
 * 
 * elFinder class to manipulate elfinder
 * 
 * */

if (!class_exists('FM_EL_Finder')) {
  class FM_EL_Finder{

    // Important data

    /**
     * 
     * @var array $base_path Base url(s) for the current user
     * 
     * */
    public $base_path;

    /**
     * 
     * Constructor function
     * 
     * */
    public function __construct(){


    }

    /**
     * 
     * Connect function
     * @return object
     * 
     * */
    public function connect($options){

      return new elFinderConnector(new elFinder($options));

    }

  }
}
