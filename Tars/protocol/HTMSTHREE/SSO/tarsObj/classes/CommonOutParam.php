<?php

namespace App\Tars\protocol\HTMSTHREE\SSO\tarsObj\classes;

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
		parent::__construct('HTMSTHREE_SSO_tarsObj_CommonOutParam', self::$_fields);
	}
}
