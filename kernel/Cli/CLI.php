<?php

namespace Kernel\Cli;

use Kernel\Orm\Database;
use Kernel\Tools\Utils;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;
use function PHPSTORM_META\override;

class CLI
{
    /**
     * Run
     */
    public static function run()
    {
        // Start the configuration of the project
        self::start();

        // Generate all entities
        self::generateEntities();
    }

    /**
     * @param string $message
     * @param string|null $default
     * @param bool $require
     * @return string|null
     */
    private static function prompt(string $message, string $default = null, bool $require = false): ?string
    {
        $res = readline($message);

        if ($require && empty($res)) {
            $res = self::prompt($message, $default, true);
        }

        return $res != null ? $res : $default;
    }

    /**
     * Start the configuration
     */
    public static function start(): void
    {
        echo Color::colorString("Welcome to the CLI of the Lightwork-API v6.0.0\n", Color::BOLD);

        $config = [];

        echo "\nConfiguration of your API...\n";
        echo Color::colorString("[1 / 2]", Color::FOREGROUND_RED);
        $config["project"]["name"] = self::prompt(" Project name : ", null, true);
        echo Color::colorString("[2 / 2]", Color::FOREGROUND_RED);
        $config["project"]["version"] = self::prompt(" Version : ", null, true);

        echo "\nConfiguration of the database access...\n";
        echo Color::colorString("[1 / 5]", Color::FOREGROUND_RED);
        $config["database"]["host"] = self::prompt(" Host (without port) : ");
        echo Color::colorString("[2 / 5]", Color::FOREGROUND_RED);
        $config["database"]["port"] = self::prompt(" Port : ");
        echo Color::colorString("[3 / 5]", Color::FOREGROUND_RED);
        $config["database"]["name"] = self::prompt(" Name : ");
        echo Color::colorString("[4 / 5]", Color::FOREGROUND_RED);
        $config["database"]["username"] = self::prompt(" Username : ");
        echo Color::colorString("[5 / 5]", Color::FOREGROUND_RED);
        $config["database"]["password"] = self::prompt(" Password : ");

        echo "\nConfiguration of the mail access...\n";
        echo Color::colorString("[1 / 3]", Color::FOREGROUND_RED);
        $config["mail"]["host"] = self::prompt(" Host : ");
        echo Color::colorString("[2 / 3]", Color::FOREGROUND_RED);
        $config["mail"]["username"] = self::prompt(" Username : ");
        echo Color::colorString("[3 / 3]", Color::FOREGROUND_RED);
        $config["mail"]["password"] = self::prompt(" Password : ");

        echo "\nConfiguration of the token properties...\n";
        echo Color::colorString("[1 / 1]", Color::FOREGROUND_RED);
        $config["token"]["expire"] = self::prompt(" Expiration : ", 604800);

        // Get directory
        $dir = dirname(__FILE__);

        // Get the path of the config file
        $path = realpath($dir . "/..");

        // Open the file
        $file = fopen("$path/config.yml", "w");

        $contentFile = file_get_contents("$path/config-sample.yml");

        foreach ($config as $type => $array) {

            foreach ($array as $k => $v) {

                if (null != $v) {
                    $contentFile = str_replace(strtoupper($type . "_" . $k), $v, $contentFile);
                }
            }
        }

        // Write the content of class
        fwrite($file, $contentFile);

        // Close and save the file
        fclose($file);
        echo Color::colorString("CREATE", Color::FOREGROUND_BOLD_GREEN) . " $path/config.yml\n";
    }

    /**
     * Generate Entities
     */
    public static function generateEntities(): void
    {
        echo "Configuration of your models (identically to your tables)...\n";
        echo Color::colorString("Do you want generate DAO with the models ?", Color::BOLD);
        $generateDao = self::prompt(" [y/n] : ", null, true);
        echo "Fetch tables...\n";

        $tables = Database::getTables()->getAll();
        echo Color::colorString("FETCH", Color::FOREGROUND_BOLD_GREEN) . " tables\n";

        $numOfTables = count($tables);
        foreach ($tables as $key => $table) {

            // Get name of the table
            $table = $table['Tables_in_' . Utils::getConfigElement('database')['dbname']];
            $className = Utils::toPascalCase($table);

            // Start
            $num = $key + 1;
            $line = "============[" . Color::colorString("$num / $numOfTables", Color::FOREGROUND_RED) . "]============\n";
            echo $line;
            echo "CREATING MODEL $className...\n";

            // Config of the class
            $namespace = new PhpNamespace("Models");
            $class = $namespace->addClass($className);
            $class->addExtend("\Models\Entity");

            // Annotations class
            $class->addComment("Class $className")
                ->addComment("@package Models")
                ->addComment("@dao " . $className . "DAO")
                ->addComment("@table $table");

            // Target DAO
            $class->addProperty("dao", $className . "DAO")
                ->addComment("Data Access Object")
                ->addComment("@var string");

            // Generate constructor
            $constructor = $class->addMethod("__construct")
                ->addComment("@param int id")
                ->setBody("if (null != \$id) { \$this->id = \$id; }\n");

            // Add id param to the constructor
            $constructor->addParameter("id", null);

            // Foreign keys
            $schemas = [];
            $foreignKeys = Database::getForeignKeys($table);

            // Generate attributes, setters and getters
            foreach (Database::getColumns($table)->getAll() as $column) {
                $field = $column["Field"];

                // Create attribute
                if (strpos($field, '_id')) {
                    $foreignKey = $field;

                    $field = substr($field, 0, strlen($field) - 3);

                    $type = Utils::toPascalCase($field);

                    // Ask the user if this is a good foreign key
                    // Re execute toPascalCase (if the user enter the table name)
                    echo "=> " . Color::colorString("ACTION REQUIRED ", Color::FOREGROUND_BOLD_RED) .
                        "$foreignKey target the model " . Color::colorString($type, Color::BOLD);
                    $type = Utils::toPascalCase( self::prompt(" [Press enter or correct the target] : ", $type) );

                    // Push schema
                    $schemas[$field] = [
                        "model" => $type,
                        "table" => $foreignKeys->filter(function ($fk) use ($column) {
                            return $fk["COLUMN_NAME"] == $column["Field"];
                        })->getFirst()["REFERENCED_TABLE_NAME"]
                    ];

                    // Add the new Object to the constructor
                    $constructor->setBody($constructor->getBody() . "\$this->$field = new $type();\n");
                } else {
                    $type = self::getTypeField(strtolower($column["Type"]));
                }

                // Create attribute
                $class->addProperty($field)
                    ->setVisibility("private")
                    ->addComment("@var $type");

                // Create getter and setter
                $methodName = Utils::toPascalCase($field);

                $class->addMethod("get$methodName")
                    ->setVisibility("public")
                    ->setBody("return \$this->$field;")
                    ->addComment("@return $type");

                $param = new Parameter($field);
                if (strtolower($type) == $field) {
                    $param->setTypeHint("\\Models\\$type");
                } else {
                    $param->setTypeHint("$type");
                }

                $class->addMethod("set$methodName")
                    ->setVisibility("public")
                    ->setBody("\$this->$field = \$$field;")
                    ->setParameters([$param])
                    ->addComment("@param $type $field");

                echo "=> " . Color::colorString("ADD", Color::FOREGROUND_BOLD_GREEN) .
                    " $field(" . Color::colorString($type, Color::BOLD) . ") field\n";
            }

            // Add list of schemas
            $class->addProperty("schemas", $schemas)
                ->addComment("Schema of foreign keys")
                ->addComment("@var array");

            $content = "<?php\n\n" . $namespace;

            // Get directory
            $dir = dirname(__FILE__);

            // Get real path to the root path
            $rootDir = realpath($dir . '/../..');

            // Get the path of the file to create
            $path = "$rootDir/app/models/$className.php";

            // Open/Create the file
            $file = fopen($path, "w");

            // Write the content of class
            fwrite($file, $content);

            // Close and save the file
            fclose($file);

            echo Color::colorString("CREATE", Color::FOREGROUND_BOLD_GREEN) . " $className model\n";

            // Generate DAO
            if (strtolower($generateDao) == "y") {
                self::generateDao($className . "DAO", $className, $table);
            }

            // End
            echo str_repeat("=", strlen($line) - 12) . "\n\n";
        }
    }

    /**
     * Generate DAO
     * @param string $className
     * @param string $table
     */
    private static function generateDao(string $dao, string $model,string $table): void
    {
        // Config of the class
        $namespace = new PhpNamespace("Models\Dao");
        $class = $namespace->addClass($dao);
        $class->addExtend("\Models\Dao\Dao")
            ->setAbstract();

        // Annotations class
        $class->addComment("Class $dao")
            ->addComment("@package Models\Dao")
            ->addComment("@model $model")
            ->addComment("@table $table");

        // Target DAO
        $class->addProperty("model", $model)
            ->addComment("Model")
            ->addComment("@var string");

        // Override some methods
        foreach (ClassType::from(Database::class)->getMethods() as $method) {
            if ($method->getVisibility() == "public" && in_array($method->getName(),
                    ["whereFirst", "whereLast", "findFirst", "findLast", "getById", "getFirst", "getLast", "store"])) {

                $newMethod = $class->addMethod($method->getName())
                    ->setVisibility("public")
                    ->setStatic($method->isStatic())
                    ->setParameters($method->getParameters())
                    ->addComment("Override of " . $method->getName() . "() to indicate the real return type");

                // parameters
                if ($method->getName() == "store") {
                    $newMethod->addComment("@param \Models\\$model \$obj")
                        ->addComment("@return int|\Models\\$model");

                    $newMethod->addParameter("obj");
                } else {
                    $newMethod->addComment("@return \Models\\$model");

                    foreach ($method->getParameters() as $parameter) {
                        $newMethod->addParameter($parameter->getName())
                            ->setTypeHint($parameter->getTypeHint());
                    }
                }

                // body of method
                $parameters = implode(", ", array_map(function (Parameter $parameter) {
                    return "$" . $parameter->getName();
                }, $method->getParameters()));

                $newMethod->setBody("return parent::" . $method->getName() . "($parameters);");
            }
        }

        $content = "<?php\n\n" . $namespace;

        // Get directory
        $dir = dirname(__FILE__);

        // Get real path to the root path
        $rootDir = realpath($dir . '/../..');

        // Get the path of the file to create
        $path = "$rootDir/app/models/Dao/$dao.php";

        // Open/Create the file
        $file = fopen($path, "w");

        // Write the content of class
        fwrite($file, $content);

        // Close and save the file
        fclose($file);

        // End
        echo Color::colorString("CREATE", Color::FOREGROUND_BOLD_GREEN) . " $dao dao\n";
    }

    /**
     * Get the type of the field
     * @param string $columnType
     * @return string
     */
    private static function getTypeField(string $columnType): string
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
}
