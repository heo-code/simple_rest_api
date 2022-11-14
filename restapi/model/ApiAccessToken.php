<?php

namespace model;

use common\Logger;
use common\DBConnect;

class ApiAccessToken extends DBConnect
{

    protected $mdb;
    protected $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->mdb = $this->getDBConnect();
    }

    public function createToken( $sTokenName, $sAccessToken, $sAbilitie = ["*"], $sClientIP="", $sExpDate="" )
    {
        $sQuery = "INSERT INTO api_access_token ( name, token, abilities, ip_addr, exp_date, reg_date, mod_date ) VALUES ( ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP )";
        $oStmt = $this->mdb->prepare($sQuery);
        $oRes = $oStmt->execute( [ $sTokenName, $sAccessToken, json_encode( $sAbilitie ), $sClientIP, $sExpDate ] );

        return $this->isNonError( $oRes );
    }

    public function getGuardToken( $sAccessToken )
    {
        $sQuery = "SELECT idx, name, token, abilities, ip_addr, exp_date, reg_date FROM api_access_token WHERE token=:token ";
        $oStmt = $this->mdb->prepare($sQuery);
        $oRes = $oStmt->execute( [ "token" => $sAccessToken ] );
        $aRes = $oRes->fetchRow();

        if( $this->isNonError( $aRes ) ) {
            return $aRes;
        }
        return false;
    }

    public function getTokenInfo( $nTokenIdx )
    {
        $sQuery = "SELECT idx, name, token, abilities, ip_addr, exp_date, reg_date FROM api_access_token WHERE idx=:idx ";
        $oStmt = $this->mdb->prepare($sQuery);
        $oRes = $oStmt->execute( [ 'idx' => $nTokenIdx ] );
        $aRes = $oRes->fetchRow();

        if( $this->isNonError( $aRes ) ) {
            return $aRes;
        }
        return false;
    }

    public function setTokenModDate( $nTokenIdx )
    {
        $sQuery = "UPDATE api_access_token SET mod_date=CURRENT_TIMESTAMP WHERE idx=:idx ";
        $oStmt = $this->mdb->prepare($sQuery);
        $oRes = $oStmt->execute( [ 'idx' => $nTokenIdx ] );

        return $this->isNonError( $oRes );
    }

    public function deleteToken( $nTokenIdx )
    {
        $sQuery = "DELETE FROM api_access_token WHERE idx=:idx ";
        $oStmt = $this->mdb->prepare($sQuery);
        $oRes = $oStmt->execute( [ 'idx' => $nTokenIdx ] );

        return $this->isNonError( $oRes );
    }

}