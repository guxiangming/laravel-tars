<?php

namespace App\Tars\protocol\HTMSTHREE\API\obj;

use App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonInParam;
use App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonOutParam;
interface ApiServiceServant {
	/**
	 * @param struct $inParam \App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonInParam
	 * @param struct $outParam \App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonOutParam =out=
	 * @return void
	 */
	public function Controller(CommonInParam $inParam,CommonOutParam &$outParam);
}

