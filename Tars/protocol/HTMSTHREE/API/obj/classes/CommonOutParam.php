<?php

namespace App\Tars\protocol\HTMSTHREE\API\obj\classes;

class CommonOutParam extends \TARS_Struct {
	const RESPONSE = 0;


	public $response; 


	protected static $_fields = array(
		self::RESPONSE => array(
			'name'=>'response',
			'required'=>true,
			'type'=>\TARS::STRING,
			),
	);

	public function __construct() {
		parent::__construct('HTMSTHREE_API_obj_CommonOutParam', self::$_fields);
	}
}
