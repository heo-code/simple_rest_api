<?php

namespace lib\http;

use lib\common\XSSFilter;

class ParameterBag extends XSSFilter
{
    protected $parameters;

    public function __construct($parameters = [])
    {
        // $this->parameters = $parameters;
        $this->parameters = $this->clean( $parameters );
    }

    public function all()
    {
        return $this->parameters;
    }

    public function keys()
    {
        return array_keys($this->parameters);
    }
    
    public function add($parameters = [])
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

}