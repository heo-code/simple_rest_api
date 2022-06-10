<?php

namespace lib\http;

use common\DBConnect;
use lib\http\FileBag;
use lib\http\HeaderBag;
use lib\http\ServerBag;
use lib\http\ParameterBag;

class Request
{
    protected $oDB;
    public $server;
    public $headers;
    public $request;
    public $params;
    public $query;
    public $json;
    public $files;

    public function __construct(){
        $this->server = new ServerBag( $_SERVER );
        $this->headers = new HeaderBag( $this->server->getHeaders() );
        $this->request = new ParameterBag( $_POST );
        $this->query = new ParameterBag( $_GET );
        $this->params = $this->getInputSource();
        $this->files = new FileBag( $_FILES );

        if( $this->isJson() ) {
            $this->request = $this->params;
        }
    }

    public function get( $key )
    {
        $data = $this->request->get( $key );
        return $data ? $data : "";
    }

    public function all()
    {
        return $this->request->all();
    }

    public function set($key, $value)
    {
        return $this->request->set( $key, $value );
    }

    public function header( $key, $default )
    {
        return $this->headers->get( $key, $default );
    }

    public function jsonBag()
    {
        if (! isset($this->json)) {
            $this->json = new ParameterBag((array) json_decode($this->getContent(), true));
        }
        return $this->json;
    }

    public function getContent($asResource = false)
    {
        $currentContentIsResource = is_resource($this->content);

        if (true === $asResource) {
            if ($currentContentIsResource) {
                rewind($this->content);

                return $this->content;
            }

            if (is_string($this->content)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);

                return $resource;
            }

            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if ($currentContentIsResource) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->jsonBag();
        }
        $input = in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
        return $input;
    }

    public function getRealMethod()
    {
        return strtoupper($this->headers->get('REQUEST_METHOD', 'GET'));
    }

    public function isJson()
    {
        return $this->strContains($this->headers->get('CONTENT_TYPE'), ['/json', '+json']);
    }

    public function strContains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}