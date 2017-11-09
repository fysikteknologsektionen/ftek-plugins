<?php
/**
 *
 * Workis with .csv extension file
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

class FM_CSV{

	/**
	 *
	 * @var string $path :: This variable is the file path of the .csv file.
	 *
	 * */
	protected $path;

	/**
	 *
	 * @var array $callback :: The callback function for every row.
	 *
	 * */
	protected $callback;

	/**
	 *
	 * @var string $token :: The token that devides the column of the csv.
	 *
	 * */
	protected $token;

	/**
	 *
	 * @var file-pointer $fp :: The pointer of the .csv file.
	 *
	 * */
	protected $fp;

	/**
	 *
	 * @var array $headers :: The headers of the .csv file.
	 *
	 * */
	public $headers;

	/**
	 *
	 * @var string $message :: Any message that the class has to show.
	 *
	 * */
	protected $message;

	/**
	 *
	 * Constructor function
	 *
	 * @param string $path :: Path of the .csv file. The path should be absolute path of the file.
	 *
	 * @param array $callback_function :: This is used to callback a function with the row array.
	 *
	 * @param string $token :: The token that devides the .csv columns
	 *
	 * */
	public function __construct( $path = null, $token = ',' ){

		// Checks if the file path is valid.
		if( !$path ){
			$this->message .= '<br/>Error: File Path is empty. It is essential that you input a valid file path.<br/>';
			return;
		}

		$this->path = $path;
		$this->token = $token;

		// Opening the file
		$this->fp = fopen( $path, "r" );

		// Getting the header data
		$this->headers = fgetcsv( $this->fp, 0, $this->token );

	}

	/**
	 *
	 * get_row function
	 *
	 * This function gets the row of the .csv file.
	 *
	 * */
	public function get_row(){
		return fgetcsv( $this->fp, 0, $this->token );
	}

}
