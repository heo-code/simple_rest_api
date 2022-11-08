<?php

namespace lib\common;

class Validator
{

    protected $data;
    protected $rules;

    protected $callback = [];
    protected $fails = [];
    protected $nullVaild = [];

    public function __construct(){
        
    }

    public function make( $aData, $aRules )
    {
        // [ "dataKey_1" => "required|string|numeric|array|bool|nullable|size:255|max:255" ],
        // [ "dataKey_2" => [ "required", "reg"=>"[a-zA-Z]+" ] ],

        $this->data = $aData;
        $this->rules = $aRules;

        $param = "";
        foreach ( $this->rules as $sAttribute => $rules ) {
            if( is_string( $rules ) ) {
                $aRules = explode( "|", $rules );
            }
            if( is_array( $rules ) ) {
                $aRules = [];
                foreach ($rules as $rule => $param) {
                    if( is_numeric( $rule ) ) {
                        $aRules[] = $param;
                    }
                    if( is_string( $rule ) ) {
                        $aRules[] = [ $rule => $param ];
                    }
                }
            }
            $this->run( $sAttribute, $aRules );
        }

        return $this;
    }

    public function run( $sAttribute, $aRules )
    {
        foreach ( $aRules as $k => $sRule ) {
            if( is_array( $sRule ) ) {
                $sMethod = key( $sRule );
                $param = current( $sRule );
                $sMethodName = "rule{$sMethod}";
                $sRule = $sMethod ."=>". $param;
            } else {
                $sMethodName = "rule{$sRule}";
                if( strpos( $sRule, ":" ) ) {
                    $param = "";
                    $aSubRules = explode( ":", $sRule );
                    list( $sMethod, $param ) = $aSubRules;
                    $sMethodName = "rule{$sMethod}";
                }
            }
            $isSucc = $this->$sMethodName( $sAttribute, $this->data[ $sAttribute ], $param );
            if( !$isSucc ) {
                $this->addFailRule( $sAttribute, $sRule, $this->data[ $sAttribute ] );
            }
        }
    }

    public function addFailRule( $sAttribute, $sRule, $value )
    {
        $sKey = $sAttribute;
        if( preg_match( "/^(nullable|required)$/", $sRule ) ) {
            $sKey = $sAttribute . "_" . $sRule;
        }
        if( isset( $this->nullVaild[ $sAttribute . "_nullable" ] ) ) {
            return true;
        }
        // add fail
        if( !isset( $this->fails[ $sKey ."_required" ][0] ) ) {
            $this->fails[ $sKey ][0] = [ "attribute" => $sAttribute, "rule" => $sRule, "value" => $value ];
        }

        // nullable delete
        if( preg_match( "/^(nullable)$/", $sRule ) ) {
            $this->delNullFailRule( $sAttribute, $sRule );
        }
    }

    public function delNullFailRule( $sAttribute, $sRule )
    {
        $sKey = $sAttribute . "_" . $sRule;
        $this->delFailRule( $sKey, $sRule );
    }

    public function delFailRule( $sAttribute, $sRule )
    {
        $sKey = $sAttribute;
        unset( $this->fails[ $sKey ] );
    }

    public function isFail()
    {
        return count( $this->fails );
    }

    public function errors()
    {
        return $this->fails;
    }


    /**
     * rule function list
     */
    public function ruleRequired( $sAttribute, $value, $param )
    {
        if( is_null( $value ) ) {
            return false;
        } else if( is_string( $value ) && trim( $value ) == "" ) {
            return false;
        } else if( is_array( $value ) && count( $value ) < 1 ) {
            return false;
        }
        return true;
    }

    public function ruleString( $sAttribute, $value, $param )
    {
        return is_string( $value );
    }

    public function ruleNumeric( $sAttribute, $value, $param )
    {
        return is_numeric($value);
    }

    public function ruleReg( $sAttribute, $value, $param )
    {
        return preg_match( "/^". $param ."$/", $value ) > 0;
    }

    public function ruleBool( $sAttribute, $value, $param )
    {
        return is_bool( $value );
    }

    public function ruleSize( $sAttribute, $value, $param )
    {
        return ( mb_strlen( $value ) ) == $param;
    }

    public function ruleMax( $sAttribute, $value, $param )
    {
        return ( mb_strlen( $value ) ) <= $param;
    }

    public function ruleEmail( $sAttribute, $value, $param )
    {
        if ( preg_match( "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-_]+\.[a-zA-Z]{2,4}$/", $value ) 
           || preg_match('/^\s*[\w\~\-\.]+\@[\w\~\-]+(\.[\w\~\-]+)+\s*$/', $value ) ) {
            return true;
        }
        return false;
    }

    public function ruleNullable( $sAttribute, $value, $param )
    {
        $bSucc = false;
        if( is_null( $value ) ) {
            $bSucc = true;
        } else if( is_string( $value ) && trim( $value ) == "" ) {
            $bSucc = true;
        }
        if( $bSucc ) {
            $this->nullVaild[ $sAttribute . "_nullable" ] = $sAttribute;
        }
        return $bSucc;
    }
}