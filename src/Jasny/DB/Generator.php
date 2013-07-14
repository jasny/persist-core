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
     * Generate a skeleton Record class for a table.
     * Define the constant MODEL_PATH to select the default directory.
     * 
     * @param Table|string $table
     */
    public static function generateRecord($table)
    {
        if (!$table instanceof Table) $table = Table::factory($table);
        $class = $table->getClass(Table::SKIP_CLASS_EXISTS);

        $defaults = $table->getDefaults();
        $types = $table->getFieldTypes();
        $nocast = $table->resultValueTypes();
        
        $filename = strtr($class, '\\_', '//') . '.php';
        if (file_exists($filename)) throw new Exception("Model file '$filename' already exists");

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
            $properties .= "    public \$$field" . (isset($value) && !is_object($value) ? " = " . var_export($value, true) : "") . ";\n";
        }

        $cast = "";
        foreach ($types as $field=>$type) {
            if (in_array($type, $nocast)) {
                // Do nothing
            } elseif (in_array($type, array('bool', 'boolean', 'int', 'integer', 'float', 'string', 'array'))) {
                $cast .= "        if (isset(\$this->$field)) \$this->$field = ($type)\$this->$field;\n";
            } else {
                $cast .= "        \$this->$field = new \\$type(\$this->$field);\n";
            }
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
<?php
$namespace 
/**
 * Entity for table `$table`.
 */
class $class extends $base
{
$properties
$constructor
}

PHP;

        file_put_contents(MODEL_PATH . '/' . $filename, $code);
    }
}
