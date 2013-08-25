<?php

class Bar extends \Jasny\DB\Record
{
    public $id;
    public $description = 'BAR';
    public $children = [];
    protected $part = 'u';
    
    public function getPart()
    {
        return $this->part;
    }
}
