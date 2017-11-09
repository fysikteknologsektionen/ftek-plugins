<?php

// Security Check
defined('ABSPATH') or die();

// Directory Seperator
if( !defined( 'DS' ) ){

  PHP_OS == "Windows" || PHP_OS == "WINNT" ? define("DS", "\\") : define("DS", "/");

} 

/**
 *
 * The starter file that holds everything togather.
 *
 * @package BootStart_1_0_0
 *
 * @since version 0.1.1
 *
 * */

/**
 *
 * Holds almost all the functionality that this nano framework supports.
 *
 *
 * We will eventually add more detailed description later.
 *
 * */
abstract class FM_BootStart2{

  /**
   *
   * @var string $name name of the plugin
   *
   * */
  protected $name;

  /**
   *
   * @var string $prefix Plugin wide prefix that will be used to differentiate from other plugin / or system vars
   *
   * */
  protected $prefix;

  /**
   *
   * @var string $path Absolute path of the plugin.
   *
   * */
  protected $path;

  /**
   *
   * @var bool $devEnv This variable is used to determine if the plugin will use the DevEnv variable which is defined at wp-config file.
   *
   * */
  protected $devEnv;

  /**
   *
   * @var array $CPTD Custom Post Type Data
   *
   * */
  protected $CPTD;

  /**
   *
   * @var array $SCD Short Code Data
   *
   * */
  protected $SCD;

  /**
   *
   * @var object $options The object of the options class
   *
   * */
  protected $options;

  /**
   *
   * @var string $upload_path :: This variable holds the path of the default upload folder
   *
   * */
  public $upload_path;

  /**
   *
   * @var string $upload_url :: This variable holds the url of the default upload folder
   *
   * */
  public $upload_url;

  /**
   *
   * @var array $menu :: Defines how the menu would be
   *
   * */
  protected $menu_data;

  /**
   *
   * Constructor function
   *
   *
   * This function does the works that every plugin must do like checking ABSPATH,
   * triggering activation and deactivation hooks etc.
   *
   * @todo Add an uninstall function
   *
   * */
  function __construct($name){

    // Assigning name
    $this->name = trim($name);

    // Assigning prefix
    $this->prefix = str_replace( ' ', '-', strtolower(trim($this->name)) );

    // Assigning path
    $this->path = __FILE__;

    // Assigning DevEnv
    $this->devEnv = false;

    // Upload folder path
    $upload = wp_upload_dir();
    $this->upload_path = $upload['basedir'] . DS . $this->prefix;

    // Upload folder url
    $upload = wp_upload_dir();
    $this->upload_url = $upload['baseurl'] . '/' . $this->prefix;

    // Setting php.ini variables
    $this->php_ini_settings();

    // Loading Options

    $this->options = new FM_OptionsManager($this->name);

    // Creating upload folder.
    $this->upload_folder();

    // Frontend asset loading
    add_action('wp_enqueue_scripts', array(&$this, 'assets') );

    // Adding a menu at admin area
    add_action( 'admin_menu', array(&$this, 'menu') );

    // Custom post hook
    add_action( 'init', array(&$this, 'custom_post') );

    // Shortcode hook
    add_action( 'init', array(&$this, 'shortcode') );

  }

  /**
   *
   * Set the all necessary variables of php.ini file.
   *
   * @todo Add some php.ini variables.
   *
   * */
  protected function php_ini_settings(){

    // This should have a standard variable list.
    /**
     * 
     * ## Increase file upload limit
     * ## Turn on error if of php if debugging variable is defined and set to true.
     * 
     * */
    ini_set('post_max_size', '128M');
    ini_set('upload_max_filesize', '128M');
  }

  /**
   *
   * Loads frontend assets
   *
   * */
  public function assets(){

    // Including front-style.css
    wp_enqueue_style($this->__('front-style'), $this->url('css/front-style.css'), false);

    // Including front-script.js
    wp_enqueue_script($this->__('front-script'), $this->url('js/front-script.js'), array(), '1.0.0', true );

    // Including media for media upload
    wp_enqueue_media();

  }

  /*
   *
   * Loads the backend / admin assets
   *
   * */
  public function admin_assets(){

    // Jquery UI CSS
    wp_enqueue_style( $this->__('jquery-ui-css'), $this->url('jquery-ui-1.11.4/jquery-ui.min.css') );

    // Jquery UI theme
    wp_enqueue_style( $this->__('jquery-ui-css-theme'), $this->url('jquery-ui-1.11.4/jquery-ui.theme.css') );

    // elFinder CSS
    wp_enqueue_style( $this->__('elfinder-css'), $this->url('elFinder/css/elfinder.min.css') );

    // elFinder theme CSS
    wp_enqueue_style( $this->__('elfinder-theme-css'), $this->url('elFinder/css/theme.css') );

    // Including admin-style.css
    wp_enqueue_style( $this->__('admin-style'), $this->url('css/admin-style.css') );

    // Including admin-script.js
    wp_enqueue_script( $this->__('admin-script'), $this->url('js/admin-script.js'), array('jquery') );

    // elFinder Scripts depends on jQuery UI core, selectable, draggable, droppable, resizable, dialog and slider.
    wp_enqueue_script( $this->__('elfinder-script'), $this->url('elFinder/js/elfinder.full.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-selectable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-resizable', 'jquery-ui-dialog', 'jquery-ui-slider', ) );

    // Adding lightbox plugin for jquery
    wp_enqueue_script( $this->__('fmp-lightbox-js'), $this->url('lightbox/js/lightbox.min.js'), array( 'jquery' ) );

  }

  /**
   *
   * Adds a sidebar/sub/top menu
   *
   * */
  public function menu(){

    if( empty( $this->menu_data ) ) return;

    if($this->menu_data['type'] == 'menu'){

      add_menu_page( $this->name, $this->name, 'manage_vbl_files', $this->prefix, array(&$this, 'admin_panel'), $this->url('img/icon-24x24.png'), 7 );

    }

  }

  /**
   *
   * Adds an admin page to the backend.
   *
   * */
  public function admin_panel(){

    if(!current_user_can('manage_vbl_files')) die( $this->render('', 'access-denied') );

    $this->render('', 'admin' . DS . 'index');

  }

  /**
   * Adds a settings page
   * 
   * */
  public function settings(){

    if(!current_user_can('manage_vbl_files')) die( $this->render('', 'access-denied') );

    $this->render('', 'admin' . DS . 'settings');

  }

  /**
   *
   * Absolute path finder
   *
   * @param string $relative_path relative path to the this plugin root folder.
   * */
  protected function path($relative_path){

    return ABSPATH.'wp-content' . DS . 'plugins' . DS . $this->prefix. DS .$relative_path;

  }

  /**
   *
   * Absolute URL finder
   *
   * @param string $string the relative url
   *
   * */
  public function url($string){

    return plugins_url( '/' . $this->prefix . '/' . $string );

  }

  /**
   *
   * Prefixes
   *
   * @param string $string Takes any literal string.
   *
   * */
  private function __($string){

    return $string = $this->prefix.'__'.$string;

  }

  /**
   *
   * Shows the debugs
   *
   * @param string/array/int $data
   *
   * */
  public function pr($data){

    if($this->devEnv) if( !defined('DevEnv') || DevEnv == false ) return;
    echo "<pre>";
    print_r($data);
    echo "</pre>";

  }

  /**
   *
   * Adds ajax hooks and functions automatically
   *
   *
   * @param string $name Name of the function
   *
   * @param bool $guest Should the function work for guests *Default: false*
   *
   * */
  public function add_ajax($name, $guest = false){

    // Adds admin ajax
    $hook = 'wp_ajax_'.$name;
    add_action( $hook, array($this, $name) );

    // Allow guests
    if(!$guest) return;

    $hook = 'wp_ajax_nopriv_'.$name;
    add_action( $hook, array($this, $name) );

  }

  /**
   *
   * Get the script for ajax request
   *
   *
   * @param string $name Name of the ajax request fuction.
   *
   * @param array $data Post data to send
   *
   * @return string $script A jQuery.post() request function to show on the the main page.
   *
   * */
  public function get_ajax_script($name, $data){

    $data['action'] = $name;

    ?>

      jQuery.post(
          '<?php echo admin_url('admin-ajax.php'); ?>',
          <?php echo json_encode($data);?>
          '<?php echo $name; ?>'
          );

    <?php

  }

  /**
   *
   * Adds custom post
   *
   * */
  public function custom_post(){

    if(empty($this->CPTD)) return;

    foreach ( $this->CPTD as $custom_post ){

      $ret = register_post_type($custom_post[0], $custom_post[1]);

    }

  }

  /**
   *
   * Adds Shortcodes
   *
   * */
  public function shortcode(){

    if( empty($this->STD) ) return;

    foreach ( $this->STD as $std ){

      $ret = add_shortcode($std, array($this, $std.'_view') );

    }

  }

  /**
   *
   * Includes a view file form the view folder which matches the called functions name
   *
   * @param string $view_file Name of the view file.
   *
   * */
  protected function render($data=null, $view_file = null){

    if($view_file == null){

      // Generates file name from function name
      $trace = debug_backtrace();
      $view_file = $trace[1]['function'].'.php';

    } else {

      $view_file .='.php';

    }

    include( $this->path( 'views' . DS . $view_file ) );

  }

  /**
   *
   * @function upload_folder Checks if the upload folder is present. If not creates a upload folder.
   *
   * */
  protected function upload_folder(){

    // Creats upload directory for this specific plugin
    if( !is_dir($this->upload_path ) ) mkdir( $this->upload_path , 0777 );

  }

  /**
   * 
   * For persentable version of slugs
   * 
   * */
  public function __p($string){
    $string = str_replace('_', ' ', $string);
    $string = str_replace('-', ' ', $string);
    $string[0] = strtoupper($string[0]);
    return $string;
  }

  /**
   * 
   * string compression function
   * 
   * */
  public function zip($string){

    $string = trim($string);
    $string = str_replace(' ', '-', $string);
    $string = strtolower($string);
    return $string;

  }
}

