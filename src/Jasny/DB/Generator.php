<?php
/**
 * Jasny DB - A DB layer for the masses.
 * 
 * PHP version 5.3+
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
/** */
namespace Jasny\DB;

if (!defined('MODEL_PATH')) define('MODEL_PATH', (defined('BASE_PATH') ? BASE_PATH : getcwd()) . '/model');

/**
 * Generate skelton classes.
 */
class Generator
{
    /**
     * Indent each line
     * 
     * @param string $code
     * @return string
     */
    protected static function indent($code, $spaces = 4)
    {
        return preg_replace('~^~m', str_repeat(' ', $spaces), $code);
    }
    
    /**
     * Get a table gateway (ignoring custom table gateways).
     * 
     * @param string $name
     * @return Table
     */
    protected static function getTable($name)
    {
        $class = Table::getDefaultClass('Table');
        return $class::factory($name);
    }
    
    /**
     * Split full class in class name and namespase
     * 
     * @param string $class
     * @param string $ns     Replace namespace
     * @return array (class name, namespace, full class)
     */
    protected static function splitClass($class, $ns=null)
    {
        $parts = explode('\\', $class);
        $classname = array_pop($parts);

        if (!isset($ns)) $ns = join('\\', $parts);
        return array($classname, $ns, ($ns ? $ns . '\\' : '') . $class);
    }
    
    /**
     * Check if we need to grate the base class instead
     * 
     * @param string $class
     * @return boolean
     */
    protected static function usesBase($class)
    {
        $filename = strtr($class, '\\_', '//') . '.php';
        include_once $filename;
        
        if (!class_exists($class, false)) return false;
        
        list($class, $ns) = self::splitClass($class);
        $base_class = ($ns ? $ns . '\\' : '') . 'Base\\' . $class;
        
        return is_a($class, $base_class, true);
    }


    /**
     * Save code to a file.
     * 
     * @param string $class
     * @param string $code
     */
    protected static function save($class, $code)
    {
        $filename = MODEL_PATH . '/' . strtr($class, '\\_', '//') . '.php';
        $hash = md5($code);
        
        // If the file exists, check if it has been modified
        if (file_exists($filename)) {
            $relative_filename = preg_replace('#^' . getcwd() . '/#', '', $filename);
            
            $source = file_get_contents($filename);
            if (!preg_match('/^ * @genhash (\w{32})$/m', $source, $match)) throw new \Exception("Won't overwrite '$relative_filename': the file wasn't generated");
            if (md5(preg_replace('/^ * @genhash \w{32}$/m', ' * @genhash {hash}', $source)) != $hash) throw new \Exception("Won't overwrite '$relative_filename': the file has been modified");
        }
        
        if (!file_exists(dirname($filename))) mkdir(dirname($filename));
        file_put_contents($filename, $code);
    }


    /**
     * Generate a skeleton Table class for a table.
     * Define the constant MODEL_PATH to select the default directory.
     * 
     * @param Table|string $table
     * @param string       $ns       Replace namespace
     * @param boolean      $return   Do not create a file but return the generated out
     * @return string|null
     */
    public static function generateTable($table, $ns=null, $return=false)
    {
        // Init and check
        if (!$table instanceof Table) $table = static::getTable($table);
        $class = $table->getClass(Table::SKIP_CLASS_EXISTS) . 'Table';

        list($classname, $ns, $class) = static::splitClass($class, $ns);
        
        if (!$return && static::usesBase($class)) {
            $ns = ($ns ? $ns . '\\' : '') . 'Base';
            $class = $ns . '\\' . $classname;
        }
        
        // Generate code
        $namespace = $ns ? "namespace $ns;\n" : '';
        
        $base = ($namespace ? '\\' : '') .get_class($table);

        $getFieldDefaults = static::indent("return " . var_export($table->getFieldDefaults(), true) . ";", 8);
        $getFieldTypes = static::indent("return " . var_export($table->getFieldTypes(), true) . ";", 8);
        $getPrimarykey = static::indent("return " . var_export($table->getPrimarykey(), true) . ";", 8);
        
        $code = <<<PHP
$namespace
/**
 * Table gateway for `$table`
 *
 * @genhash {hash}
 */
class $classname extends $base
{
    /**
     * Get all the default value for each field for this table.
     * 
     * @return array
     */
    public function getFieldDefaults()
    {
$getFieldDefaults
    }

    /**
     * Get the php type for each field of this table.
     * 
     * @return array
     */
    public function getFieldTypes()
    {
$getFieldTypes
    }
    
    /**
     * Get primary key.
     * 
     * @return string
     */
    public function getPrimarykey()
    {
$getPrimarykey
    }
}
PHP;
        
        if ($return) return $code;
        static::save($class, "<?php\n" . $code);
    }
    
    /**
     * Generate a skeleton Record class for a table.
     * Define the constant MODEL_PATH to select the default directory.
     * 
     * @param Table|string $table
     * @param string       $ns       Replace namespace
     * @param boolean      $return   Do not create a file but return the generated out
     * @return string|null
     */
    public static function generateRecord($table, $ns=null, $return=false)
    {
        // Init and check
        if (!$table instanceof Table) $table = static::getTable($table);
        $class = $table->getClass(Table::SKIP_CLASS_EXISTS);

        list($classname, $ns, $class) = static::splitClass($class, $ns);
        
        if (!$return && static::usesBase($class)) {
            $ns = ($ns ? $ns . '\\' : '') . 'Base';
            $class = $ns . '\\' . $classname;
        }
        
        // Get information
        $defaults = $table->getFieldDefaults();
        $types = $table->getFieldTypes();
        $nocast = $table->resultValueTypes();
        
        // Generate code
        $namespace = $ns ? "namespace $ns;\n" : '';

        $base = Table::getDefaultClass('Record', $table->getDB()) ?: __NAMESPACE__ . '\\Record';
        if ($namespace) $base = '\\' . $base;

        $properties = "";
        foreach ($defaults as $field=>$value) {
            if (preg_match('/\W/', $field)) throw new \Exception("Can't create a property for field '$field'");
            $properties .= "    public \$$field" . (isset($value) ? " = " . var_export($table->castValue($value, $types[$field], false), true) : '') . ";\n";
        }

        $cast = "";
        foreach ($types as $field=>$type) {
            if (in_array($type, $nocast)) continue;
            
            $internal_type = in_array($type, array('bool', 'boolean', 'int', 'integer', 'float', 'string', 'array'));
            $cast .= "        if (isset(\$this->$field)) \$this->$field = " . ($internal_type ? "($type)\$this->$field" : "new \\$type(\$this->$field)") . ";\n";
        }
        $cast = rtrim($cast);
        
        $constructor = "";
        if ($cast) $constructor = <<<PHP
    /**
     * Class constructor
     */
    public function __construct()
    {
$cast
    }
PHP;
        
        $code = <<<PHP
$namespace 
/**
 * Record of table `$table`.
 *
 * @genhash {hash}
 */
class $classname extends $base
{
$properties
$constructor
}

PHP;

        if ($return) return $code;
        static::save($class, "<?php\n" . $code);
    }
    
    
    /**
     * Automatically create classes for table gateways and records
     */
    public static function autoload($class)
    {
        list($class, $ns) = static::splitClass($class);
        if (preg_replace('/(^|\\\\)Base$/', '', $ns) != Table::getDefaultConnection()->getModelNamespace()) return;
        
        $name = Table::uncamelcase(preg_replace('/Table$/i', '', $class));
        if (empty($name) || !Table::exists($name)) return;
        
        $code = substr($class, -5) == 'Table' ? static::generateTable($name, $ns, true) : static::generateRecord($name, $ns, true);
        eval($code);
    }
}
