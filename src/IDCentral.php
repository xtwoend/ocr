<?php


namespace Growinc\Ocr;

use Growinc\Ocr\Client;

class IDCentral
{
    protected $client;

    public function __call($name, $arguments)
    {
        return $this->__client()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $co = new self;
        return $co->__client()->{$name}(...$arguments);
    }

    protected function __client()
    {
        return new Client();
    }
}