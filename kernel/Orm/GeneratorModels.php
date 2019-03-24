<?php
namespace Kernel\Orm;

use Kernel\Tools\Utils;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;

class GeneratorModels
{
    public static function run()
    {
        foreach (Database::getTables() as $table) {

            $table = $table[
            'Tables_in_' . Utils::getConfigElement('database')['dbname']
            ];

            $tableRenamed = Utils::toPascalCase($table);

            $namespace = new PhpNamespace("Models");

            $namespace->addUse("\Kernel\Orm\Database");

            $class = $namespace->addClass($tableRenamed);

            $class->addExtend("\Kernel\Orm\Database");

            // Generate attributes
            foreach (Database::getColumns($table) as $column) {
                $field = $column["Field"];

                $type = strtolower($column["Type"]);

                $type = self::getType($type);

                self::checkForeignKey($field, $type);

                $class->addProperty($field)
                    ->setVisibility("private")
                    ->addComment("@var $type");
            }

            // Generate setters and getters
            foreach (Database::getColumns($table) as $column) {
                $field = $column['Field'];

                $fieldRenamed = Utils::toPascalCase($field);

                if (strpos($field, "_id")) {
                    $fieldRenamed = substr($fieldRenamed, 0, -2);
                }

                $type = strtolower($column["Type"]);

                $type = self::getType($type);

                self::checkForeignKey($field, $type);

                $class->addMethod("get$fieldRenamed")
                    ->setVisibility("public")
                    ->setBody("return \$this->$field;")
                    ->addComment("@return $type");

                $param = new Parameter($field);
                if (strtolower($type) == $field) {
                    $param->setTypeHint("\\Models\\$type");
                }

                $class->addMethod("set$fieldRenamed")
                    ->setVisibility("public")
                    ->setBody("\$this->$field = \$$field;")
                    ->setParameters([$param])
                    ->addComment("@param $type $field");
            }

            $content = "<?php\n\n" . $namespace;

            // Get directory
            $dir = dirname(__FILE__);

            // Get real path to the root path
            $rootDir = realpath($dir . '/../..');

            // Get the path of the file to create
            $path = "$rootDir/app/models/$tableRenamed.php";

            // Open/Create the file
            $file = fopen($path, "w");

            // Write the content of class
            fwrite($file, $content);

            // Close and save the file
            fclose($file);
        }
    }

    private static function getType($columnType)
    {
        // Get type of the field
        switch (true) {

            case preg_match("#int#", $columnType):
                $type = "int";
                break;

            case preg_match("#double#", $columnType):
                $type = "double";
                break;

            case preg_match("#float#", $columnType):
                $type = "float";
                break;

            case preg_match("#bool#", $columnType):
                $type = "boolean";
                break;

            default:
                $type = "string";

        }

        return $type;
    }

    public static function checkForeignKey(&$field, &$type)
    {
        if (strpos($field, '_id')) {
            $field = substr($field, 0, strlen($field) - 3);

            $type = Utils::toPascalCase($field);
        }
    }
}
