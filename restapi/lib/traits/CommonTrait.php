<?php

namespace lib\traits;

use common\Logger;
use lib\common\Validator;

trait CommonTrait
{
    function checkRequestParams( $request, $aRules )
    {
        // [ "dataKey_1" => "required|string|numeric|array|bool|nullable|nameset|size:255|max:255" ],
        // [ "dataKey_2" => [ "required", "reg"=>"[a-zA-Z]+" ] ],
        // [ "dataKey_2" => [ "reg"=>"[a-zA-Z]+" ] ],
        $logger = (new Logger())->getLogger();

        if( !is_Array( $aRules ) || !count( $aRules ) ) return false;

        $aParamData = $request->all();
        $validator = ( new Validator)->make( $aParamData, $aRules );
        if( $validator->isFail() ) {
            $aErrors = $validator->errors();
            $sMsg = "\n[INFO] Fail Validate";
            $idx = 0;
            foreach ( $aErrors as $k => $aError ) {
                $idx++;
                $sMsg .= "\n{$idx}. [Rule] : {$aError[0]['rule']}\n - [Attribute] : {$aError[0]['attribute']}\n - [Value] : {$aError[0]['value']}\n";
            }
            $logger->log( $sMsg );
            return false;
        }
        return true;
    }
}
