<?php

namespace Jasny\DB;

/**
 * Binary large object wrapper.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
class Blob
{
    /**
     * Filename for lazy load
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $data;
    
    
    /**
     * Class constructor
     * 
     * @param type $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }
    
    /**
     * Load BLOB from file (lazy load).
     * 
     * @param string $filename
     * @return static
     */
    public static function fromFile($filename)
    {
        $blob = new static();
        $blob->filename = $filename;
        
        return $blob;
    }
    
    /**
     * Get the filename
     * 
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
    
    
    /**
     * Get blob data
     * 
     * @return string
     */
    public function __toString()
    {
        if (!isset($this->data) && isset($this->filename)) {
            $data = file_get_contents($this->filename);
            if ($data === false) throw new \Exception("Failed to read {$this->filename}");
            
            $this->data = $data;
        }
        
        return (string)$this->data;
    }
    
    /**
     * Ouput the data
     */
    public function output()
    {
        if (!isset($this->data) && isset($this->filename)) {
            readfile($this->filename);
        } else {
            echo $this->data;
        }
    }
}
