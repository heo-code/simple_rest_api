<?php

namespace lib\common;

use common\Logger;

class XSSFilter
{
    public function __construct(){
        
    }

    /**
     * @var string
     */
    protected $scriptsAndIframesPattern = '/(<script.*script>|<frame.*frame>|<iframe.*iframe>|<object.*object>|<embed.*embed>)/isU';

    /**
     * @var string
     */
    protected $inlineListenersPattern = '/(\bon[A-z]+=(\"|\').*(\"|\')(?=.*>)|(javascript:.*(?=.(\'|")??>)(\)|;)??))/isU';

    /**
     * @var string
     */
    protected $invalidHtmlInlineListenersPattern = '/\bon[A-z]+=(\"|\')?.*(\"|\')?(?=.*>)/isU';

    public function clean( $aParams )
    {
        if( is_string( $aParams ) ) {
            $aParams = $this->escapeRun( $aParams );
        } else {
            foreach ( $aParams as $key => $value ) {
                if( is_array( $value ) ) {
                    $aParams[ $key ] = $this->clean( $value );
                }
                if( is_string( $value ) ) {
                    $aParams[ $key ] = $this->escapeRun( $value );
                }
            }
        }

        return $aParams;
    }

    public function escapeRun( $value )
    {
        $value = $this->escapeScriptsAndIframes( $value );
        $value = $this->escapeInlineEventListeners( $value );

        return $value;
    }

    protected function escapeScriptsAndIframes( $value )
    {
        preg_match_all( $this->scriptsAndIframesPattern, $value, $matches );

        foreach ($this->getMatchArray( $matches, 0 ) as $script ) {
           $value = str_replace($script, $this->getEscapeStr( $script ), $value);
        }
        return $value;
    }

    protected function escapeInlineEventListeners( $value )
    {
        $string = preg_replace_callback($this->inlineListenersPattern, [$this, 'escapeEqualSign'], $value );
        $string = preg_replace_callback($this->invalidHtmlInlineListenersPattern, [$this, 'escapeEqualSign'], $string );

        return !is_string($string) ? '' : $string;
    }

    protected function getMatchArray( $array, $key, $null=[] ) {
        if( isset( $array[$key] ) ) {
            return $array[$key];
        } else {
            return $null;
        }
    }

    protected function getEscapeStr($value, $doubleEncode = true)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }

    protected function escapeEqualSign( $matches)
    {
        return str_replace('=', '&#x3d;', $matches[0]);
    }
}