<?php

namespace common;

use lib\http\Request;
use common\Logger;
use model\ApiAccessToken;

class ApiGuard extends Request
{
    protected $modelToken;
    protected $logger;

    public function __construct(){
        parent::__construct();
        $this->modelToken = new ApiAccessToken();
        $this->logger = Logger::getInstance();
    }

    public function guard()
    {
        $sAccessToken= $this->getBearerToken();
        if( $sAccessToken && preg_match('/^([a-z0-9])+$/i', $sAccessToken ) ) {
            $aGuardInfo = $this->modelToken->getGuardToken( $sAccessToken );
            if( $aGuardInfo ) {
                $bCan = false;
                $sAbilities = $aGuardInfo['abilities'];
                $sVaildateIP = $aGuardInfo['ip_addr'];
                $sExpDate = $aGuardInfo['exp_date'];

                // exp date
                if( strlen( $sExpDate ) ) {
                    if( !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sExpDate ) || strtotime( date("Y-m-d") ) > strtotime( $sExpDate ) ) {
                        $this->logger->log( "[guard] Fail Over ExpDate" );
                        return false;
                    }
                }

                // ip addr
                if( strlen( $sVaildateIP ) ) {
                    if( !$this->checkAccessIP( $sVaildateIP ) ) {
                        $this->logger->log( "[guard] Fail Is Not Allow IP" );
                        return false;
                    }
                }

                // token can
                $sAccessCtrlDir = $this->getAccessControllerDirToUrl();
                if( $sAccessCtrlDir ) {
                    $aAbilities = (array) json_decode( $sAbilities, 1 );
                    $bCan = $this->tokenCan( $sAccessCtrlDir, $aAbilities );
                }
                if( !$bCan ) {
                    $this->logger->log( "[guard] Fail Is Not Allow Dir" );
                    return false;
                }

                // set Access Date
                $this->modelToken->setTokenModDate( $aGuardInfo['idx'] );
                return true;
            }
        }
        return false;
    }

    public function checkAccessIP( $sVaildateIP )
    {
        $sXForwardForIP = $this->server->get("HTTP_X_FORWARDED_FOR");
        $sRemoteIp = $this->server->get("REMOTE_ADDR");

        $sAccessIp = isset( $sXForwardForIP ) ? $sXForwardForIP : $sRemoteIp;
        $aVaildateIP = explode( "|", $sVaildateIP );
        $isOk = false;
        foreach ( $aVaildateIP as $key => $sChkIP ) {
            $aChkIP = explode( ".", $sChkIP );
            if ( count( $aChkIP ) == 4 && $aChkIP[3] ) {
                if ( preg_match('/^('.$sChkIP.')$/', $sAccessIp ) ) {
                    $isOk = true;
                    break;
                }
            } else if ( count( $aChkIP ) < 4 || ( count( $aChkIP ) == 4 && !$aChkIP[3] ) ) {
                $sChkIP .= ".";
                if ( preg_match('/^('.$sChkIP.')/', $sAccessIp ) ) {
                    $isOk = true;
                    break;
                }
            }
        }
        return $isOk;
    }

    public function getBearerToken()
    {
        $header = $this->header('Authorization', '');

        if ($this->strStartsWith($header, 'Bearer ' )) {
            return $this->strSubstr($header, 7);
        }
        return $header;
    }

    public function tokenCan( $sAccessCtrlDir, $aAbilities )
    {
        // url == aAbilities
        return in_array( '*', $aAbilities ) ||
               array_key_exists( $sAccessCtrlDir, array_flip( $aAbilities ) );
    }

    public function getAccessControllerDirToUrl()
    {
        $sUrl = $this->server->get("REQUEST_URI");
        $aUrl = explode("/", $sUrl );
        $sCtrDirName = "";
        foreach ($aUrl as $key => $value ) {
            if( strtolower( $value ) == PREFIX_URL ) {
                $sCtrDirName = $aUrl[ ( $key + 1 ) ];
            }
        }

        $aApiRootDir = dirname( dirname(__FILE__) );
        $sAccDir = $aApiRootDir . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $sCtrDirName;
        if(  is_dir( $sAccDir ) ) {
            return $sCtrDirName;
        }
        return false;
    }

    function strStartsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
                return true;
            }
        }

        return false;
    }

    public function strSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }
}