<?php

namespace common;

class DBConnect
{
    protected static $oDBConnect;

    public function __construct() {
    }

    public function getDBConnect( $sDomainName="" )
    {
        if( is_object( self::$oDBConnect ) ) {
            if($sDomainName) {
                self::$oDBConnect->setDatabase( $sDomainName );
            }
            return self::$oDBConnect;
        }

        $sConStr = "mysqli://". env("DB_USERNAME") .":". env("DB_PASSWORD") ."@". env("DB_HOST") .":". env("DB_PORT") ."/". env("DB_DATABASE");
        self::$oDBConnect = \MDB2::factory( $sConStr );
        if( $this->isNonError( $this->oDBConnect ) ) {
            self::$oDBConnect->setDatabase( env("DB_DATABASE") );
            self::$oDBConnect->setFetchMode( MDB2_FETCHMODE_ASSOC );
            return self::$oDBConnect;
        }
        return false;
    }

    public function getDBName()
    {
        return self::$oDBConnect->getDatabase();
    }

    public function isNonError( $oDB )
    {
        if( \MDB2::isError( $oDB ) ) {
            return false;
        }
        return true;
    }
}