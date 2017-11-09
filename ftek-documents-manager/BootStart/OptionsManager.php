<?php
/**
 *
 * Managers options. Works with BootStart Class
 *
 * @package BootStart_0_1_0
 *
 * @since version 0.1.0
 *
 *
 *
 * @name CSV
 *
 * @author Aftabul Islam <toaihimel@gmail.com>
 *
 * @license GNU/GPLv3 or later
 *
 *
 *
 * */

if (!class_exists('FM_OptionsManager')) {
  class FM_OptionsManager{

    /**
     *
     * @var string $name Name of the plugin.
     *
     * */
    protected $name;

    /**
     *
     * @var string $prefix Prefix of the plugin calculated from then name.
     *
     * */
    protected $prefix;

    /**
     *
     * @var string $options_name Name of the option which is going to be stored in the wpdb.
     * It is same as the prefix but added for clearification.
     *
     * */
    protected $options_name;

    /**
     *
     * @var array $options The options are stored in this variable.
     *
     * */
    public $options;

    /**
     *
     * The main constructor function. Initializes and loads the options from the database.
     *
     * @param string $name Name of the plugin. It helps to retrive option data.
     *
     * */
    function __construct($name = null){

      $this->name = $name;
      $this->prefix = str_replace(' ', '-', trim($name) );
      $this->options_name = $this->prefix;

      // Retrive the options from the database.
      $this->options = $this->retrive();

      // Registering shutdown function as destructor
      register_shutdown_function(array($this, 'destruct'));

    }

    /**
     *
     * This function retrives the options from the database.
     * If the options data is not present then it creates and retrives the data.
     *
     * @return array $options
     *
     * */
    protected function retrive(){

      $this->options = get_option($this->prefix);

      if( empty($this->options) ){

        update_option($this->prefix,array());
        $this->options = array();

      } else return $this->options;

    }

    /**
     *
     * This function sets the value value of an option.
     *
     * @params string $name Name of the option.
     * @params mixed $value Value against the name.
     *
     * @return bool/string $error Error or successess message.
     *
     * */
    public function set($name, $value){

      $this->options[$name] = $value;

    }

    /**
     *
     * This function returns the value of the option
     *
     * @param string $name Name of the option.
     *
     * @reurn mixed $value Value set against the name.
     *
     * */
    public function get($name){

      return $this->options[$name];

    }

    /**
     *
     * Returns a form that manipulates the options.
     *
     * @params array $list List of the options that is the forms to be generated.
     *
     * @retrun A HTML form to manipulate options.
     *
     * */
    public function get_form(array $list){


      $form = "<form method='post' action='' >";
      $form .='<table><tbody>';

      // Traversing the entire list
      foreach($list as $li){

        $name = $li['key'];
        $type = isset($li['type']) && !empty($li['type']) ? $li['type'] : 'text';
        $label = $li['label'];
        $id = $name.'_id';
        $class = "OMDC_input ".$li['class'];
        $required = isset($li['required']) && $li['required'] ? 'required' : '' ;
        $default = isset($li['default']) ? $li['default'] : '';

        $value = $this->path_to_val($name);

        $value = $value ? $value : $default;

        // Genarating input fields.
        $input ='<tr><td>';
        $input .= "<label for='{$id}' class='OMDC_label'>{$label}</label></td>";
        $input .= "<td/><input type='{$type}' name='{$name}' id='{$id}' class='{$class}' value='{$value}' {$required} /><br/>";
        $input .= "</td></tr>";
        $form .= $input;
      }
      $form .= "<tr><td><input type='submit' value='Save' id='op_submit' /></td><td id='op_message'></td></tr>";
      $form .= "</tbody></table>";
      $form .= "</form>";

      return $form;

    }

    /**
     *
     * Saves the data from the settings form
     *
     * @param array $list List of the settings
     *
     * @return string $string Success/Error Message.
     *
     * @todo Sanitize post data
     *
     * */
    public function save_form($list){

      if( empty($_POST) ) return;

      $posts = $_POST;

      $keys = array_keys($posts);
      foreach ($keys as $key){

        $value = $posts[$key];
        $this->path_to_val($key, $value);

      }

    }

    /**
     *
     * Converts string to value
     *
     * @param string $name The array string path.
     *
     * @param string $set If the value needs to be stored. If false the value is searched and returned. Default is false.
     *
     * @param mixed $val The value which has to be set.
     *
     * @return mixed $value The value of the options. Returns false if the value doesn't exists.
     *
     * */
    protected function path_to_val($name, $val = '' ){

      // calculating path
      $nested = explode(':', $name);
      $path = '';
      foreach($nested as $ne){
        $path .= "['{$ne}']";
      }
      $statement = '$setted = isset($this->options'.$path.');';
      eval($statement);

      if( !empty($val) ){

        $statement = '$this->options'.$path." = '{$val}';";
        eval($statement);
        return true;

      } elseif($setted){

        $statement = '$value = $this->options'.$path.';';
        eval($statement);
        return $value;

      }else {

        return false;

      }

    }

    /**
     *
     * Destructor function which saves the options value to the database.
     *
     * */
    public function destruct(){

      update_option($this->prefix, $this->options);

    }
  }
}
