<?php

namespace App\Tars\protocol\HTMSTHREE\SSO\tarsObj\classes;

class CommonInParam extends \TARS_Struct {
	const METHOD = 0;
	const ROUTE = 1;
	const PARAMS = 2;


	public $method; 
	public $route; 
	public $params; 


	protected static $_fields = array(
		self::METHOD => array(
			'name'=>'method',
			'required'=>false,
			'type'=>\TARS::STRING,
			),
		self::ROUTE => array(
			'name'=>'route',
			'required'=>false,
			'type'=>\TARS::STRING,
			),
		self::PARAMS => array(
			'name'=>'params',
			'required'=>false,
			'type'=>\TARS::STRING,
			),
	);

	public function __construct() {
		parent::__construct('HTMSTHREE_SSO_tarsObj_CommonInParam', self::$_fields);
	}
}
