<?php

namespace common;

class Logger
{
    protected static $logger;

    public function __construct(){
    }

    public static function getInstance()
    {
        if( is_object( self::$logger ) ) {
            return self::$logger;
        }

        $aLogConf = array( 'mode'=>0644, 'lineFormat'=>'%1$s %2$s [%3$s] <%5$s> at line %6$s func{%7$s} %4$s' );
        if( !is_dir( __DIR__."/../log/api/". date("Y") ) ) {
            @mkdir( __DIR__."/../log/api/". date("Y") );
        }
        if( !is_dir( __DIR__."/../log/api/". date("Y") ."/". date("m") ) ) {
            @mkdir( __DIR__."/../log/api/". date("Y") ."/". date("m") );
        }
        self::$logger = &\Log::singleton( "file", __DIR__."/../log/api/". date("Y") ."/". date("m") ."/". date("d") .".log", "[restApi]", $aLogConf );

        return self::$logger;
    }

    public static function log( $logMsg, $logType="INFO" )
    {
        $oLogger = self::getInstance();
        $type = ( trim( strtoupper( $logType ) ) == "ERROR" ) ? PEAR_LOG_ERR : PEAR_LOG_INFO;

        $oLogger->log( $logMsg, $type );
    }
}