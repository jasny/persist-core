<?php

class Bar extends \Jasny\DB\Record
{
    public $id;
    public $description = 'BAR';
    public $children = array();
    protected $part = 'u';
    
    public function getPart()
    {
        return $this->part;
    }
}
