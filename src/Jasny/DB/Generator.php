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
     * Generate a skeleton Table class for a table.
     * Define the constant MODEL_PATH to select the default directory.
     * 
     * @param Table|string $table
     * @param boolean      $return   Do not create a file but return the generated out
     * @return string|null
     */
    public static function generateTable($table, $return=false)
    {
        if (!$table instanceof Table) $table = Table::factory($table);
        $class = $table->getClass(Table::SKIP_CLASS_EXISTS) . 'Table';

        $filename = strtr($class, '\\_', '//') . '.php';
        if (!$return && file_exists(MODEL_PATH . '/' . $filename)) throw new \Exception("Model file '$filename' already exists");
        
        $namespace = "";
        if (strpos($class, '\\')) {
            $ns = explode('\\', $class);
            $class = array_pop($ns);
            $namespace = "namespace " . join('\\', $ns) . ";\n";
        }

        $base = $table->getDefaultClass('Table');

        $getFieldDefaults = static::indent("return " . var_export($table->getFieldDefaults(), true) . ";", 8);
        $getFieldTypes = static::indent("return " . var_export($table->getFieldTypes(), true) . ";", 8);
        $getPrimarykey = static::indent("return " . var_export($table->getPrimarykey(), true) . ";", 8);
        
        $code = <<<PHP
$namespace
/**
 * Table gateway for `$table`
 */
class $class extends $base
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
        file_put_contents(MODEL_PATH . '/' . $filename, "<?php\n" . $code);
    }
    
    /**
     * Generate a skeleton Record class for a table.
     * Define the constant MODEL_PATH to select the default directory.
     * 
     * @param Table|string $table
     * @param boolean      $return   Do not create a file but return the generated out
     * @return string|null
     */
    public static function generateRecord($table, $return=false)
    {
        if (!$table instanceof Table) $table = Table::factory($table);
        $class = $table->getClass(Table::SKIP_CLASS_EXISTS);

        $defaults = $table->getFieldDefaults();
        $types = $table->getFieldTypes();
        $nocast = $table->resultValueTypes();
        
        $filename = strtr($class, '\\_', '//') . '.php';
        if (!$return && file_exists(MODEL_PATH . '/' . $filename)) throw new \Exception("Model file '$filename' already exists");

        $namespace = "";
        if (strpos($class, '\\')) {
            $ns = explode('\\', $class);
            $class = array_pop($ns);
            $namespace = "namespace " . join('\\', $ns) . ";\n";
        }

        $base = Table::getDefaultClass('Record', $table->getDB()) ?: __NAMESPACE__ . '\\Record';
        if ($namespace) $base = '\\' . $base;

        $properties = "";
        foreach ($defaults as $field=>$value) {
            if (preg_match('/\W/', $field)) throw new \Exception("Can't create a property for field '$field'");
            
            $properties .= "    public \$$field" . (isset($value) ? " = " . var_export(Table::castValue($value, $type, false)) : '') . ";\n";
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
 */
class $class extends $base
{
$properties
$constructor
}

PHP;

        if ($return) return $code;
        file_put_contents(MODEL_PATH . '/' . $filename, "<?php\n" . $code);
    }
    
    
    /**
     * Automatically create classes for table gateways and records
     */
    public static function autoload($class)
    {
        if (preg_replace('/(^|\\\\)[^\\\\]+$/', '', $class) != Table::getDefaultConnection()->getModelNamespace()) return;
        
        $name = Table::uncamelcase(preg_replace('/^.+\\\\|Table$/i', '', $class));
        if (empty($name) || !Table::exists($name)) return;
        
        $code = substr($class, -5) == 'Table' ? self::generateTable($name, true) : self::generateRecord($name, true);
        eval($code);
    }
}
