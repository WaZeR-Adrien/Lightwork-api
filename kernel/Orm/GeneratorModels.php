<?php
namespace Kernel\Tools;

use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;

class GeneratorModels
{
    public static function run()
    {
        foreach (\Kernel\Database::getTables() as $table) {

            $table = $table[
            'Tables_in_' . \Kernel\Config::get('database')['db']
            ];

            $tableRenamed = implode(array_map('ucfirst', explode('_', $table)));

            $namespace = new PhpNamespace("Models");

            $class = $namespace->addClass($tableRenamed);

            $class->addExtend("\Kernel\Database");

            // Generate attributes
            foreach (\Kernel\Database::getColumns($table) as $column) {
                $field = $column["Field"];

                $columnType = strtolower($column["Type"]);

                $type = self::getType($columnType);

                $class->addProperty($field)
                    ->setVisibility("private")
                    ->addComment("@var $type");
            }

            // Generate setters and getters
            foreach (\Kernel\Database::getColumns($table) as $column) {
                $field = $column['Field'];

                $fieldRenamed = implode(array_map('ucfirst', explode('_', $field)));

                $columnType = strtolower($column["Type"]);

                $type = self::getType($columnType);

                $class->addMethod("get" . $fieldRenamed)
                    ->setVisibility("public")
                    ->setBody("return \$this->$field;")
                    ->addComment("@return $type");

                $param = new Parameter($field);
                $class->addMethod("set" . $fieldRenamed)
                    ->setVisibility("public")
                    ->setParameters([$param])
                    ->setBody("\$this->$field = $field;")
                    ->addComment("@params $type $field");
            }

            $content = "<?php\n\n" . $class;

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

            case preg_match('#int#', $columnType):
                $type = "int";
                break;

            case preg_match('#double#', $columnType):
                $type = "double";
                break;

            case preg_match('#float#', $columnType):
                $type = "float";
                break;

            case preg_match('#bool#', $columnType):
                $type = "boolean";
                break;

            default:
                $type = "string";

        }

        return $type;
    }
}