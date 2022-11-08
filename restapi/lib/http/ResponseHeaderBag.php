<?php

namespace lib\http;

use lib\http\HeaderBag;

class ResponseHeaderBag extends HeaderBag
{

    protected $cookies = [];
    protected $headerNames = [];

    public function __construct( $headers = [] )
    {
        parent::__construct( $headers );

        if ( !isset( $this->headers['cache-control'] ) ) {
            $this->set('Cache-Control', 'no-store, no-cache');
        }

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if ( !isset($this->headers['date'] ) ) {
            $this->initDate();
        }
    }

    /**
     * Returns the headers, with original capitalizations.
     *
     * @return array
     */
    public function allPreserveCase()
    {
        $headers = [];
        foreach ( $this->all() as $name => $value ) {
            $key = $this->headerNames[$name] ? $this->headerNames[$name] : $name;
            $headers[$key] = $value;
        }

        return $headers;
    }

    public function allPreserveCaseWithoutCookies()
    {
        $headers = $this->allPreserveCase();
        if ( isset($this->headerNames['set-cookie'] ) ) {
            unset( $headers[$this->headerNames['set-cookie']] );
        }

        return $headers;
    }

    private function initDate()
    {
        $this->set('Date', gmdate('D, d M Y H:i:s').' GMT');
    }

}