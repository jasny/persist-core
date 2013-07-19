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
     * Generate a skeleton class for a table
     * 
     * @param Table|string $table
     * @return 
     */
    public static function generateModel($table)
    {
        $table = Table::factory($argc[0]);
        $class = $table->getClass(Table::SKIP_CLASS_EXISTS);

        $filename = strtr($class, '\\_', '//') . '.php';
        if (file_exists($filename)) throw new Exception("Model file '$filename' already exists");

        $namespace = "";
        if (strpos($class, '\\')) {
            $ns = explode('\\', $class);
            $class = array_pop($ns);

            $namespace = "namespace " . join('\\', $ns) . ";\n";
        }

        $properties = "";
        foreach ($table->getDefaults() as $field=>$value) {
            $properties .= "public $field = $value\n";
        }

        $code = <<<PHP
<?php
$namespace 
/**
 * Entity for table `$table`.
 */
class $class extends \Jasny\DB\Record
{
    $properties 
}
PHP;

        file_put_contents(MODEL_PATH . '/' . $filename, $code);
    }
}
