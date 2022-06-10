<?php

namespace lib\http;

class HeaderBag
{
    const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    protected $headers;

    public function __construct( $headers = [] ){
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }

    public function all($key = null)
    {
        if (null !== $key) {
            $data = $this->headers[strtr($key, self::UPPER, self::LOWER)];
            return $data ? $data : [];
        }

        return $this->headers;
    }

    public function keys()
    {
        return array_keys($this->all());
    }

    public function get($key, $default = null)
    {
        $headers = $this->all($key);

        if (!$headers) {
            return $default;
        }

        if (null === $headers[0]) {
            return null;
        }

        return (string) $headers[0];
    }

    public function set( $key, $values, $replace = true)
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        if (is_array($values)) {
            $values = array_values($values);

            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $values);
            }
        } else {
            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = [$values];
            } else {
                $this->headers[$key][] = $values;
            }
        }
    }

    public function remove($key)
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        unset($this->headers[$key]);

    }
}