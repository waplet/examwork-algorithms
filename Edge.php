<?php

class Edge
{
    public $source;
    public $sink;
    public $capacity = 1;

    /**
     * @var Edge
     */
    public $redge;

    public function __construct($u, $v, $capacity = 1)
    {
        $this->source = $u;
        $this->sink = $v;
    }

    public function getKey()
    {
        return $this->source . ":" . $this->sink;
    }
}