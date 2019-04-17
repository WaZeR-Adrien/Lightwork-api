<?php
namespace Kernel\Orm;

use Kernel\Tools\Utils;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;

class Generator
{
    public static function run()
    {
        // Generate the config of the project
        self::generateConfig();

        // Generate all entities
        self::generateEntities();
    }

    /**
     * @param $message
     * @param null $default
     * @param bool $require
     * @return string|null
     */
    private static function prompt($message, $default = null, $require = false)
    {
        $res = readline($message);

        if ($require && empty($res)) { $res = self::prompt($message, $default, true); }

        return $res != null ? $res : $default;
    }

    public static function generateConfig()
    {
        echo "Welcome to the generator of the Lightwork-API v5.0.0\n";

        $config = [];

        echo "\nBasic configuration of your API...\n";
        $config["project"]["name"] = self::prompt("[1 / 2] Project name : ", null,true);
        $config["project"]["version"] = self::prompt("[2 / 2] Version : ", null,true);

        echo "\nConfiguration of the database access...\n";
        $config["database"]["host"] = self::prompt("[1 / 5] Host (without port) : ");
        $config["database"]["port"] = self::prompt("[2 / 5] Port : ");
        $config["database"]["name"] = self::prompt("[3 / 5] Name : ");
        $config["database"]["username"] = self::prompt("[4 / 5] Username : ");
        $config["database"]["password"] = self::prompt("[5 / 5] Password : ");

        echo "\nConfiguration of the mail access...\n";
        $config["mail"]["host"] = self::prompt("[1 / 3] Host : ");
        $config["mail"]["username"] = self::prompt("[2 / 3] Username : ");
        $config["mail"]["password"] = self::prompt("[3 / 3] Password : ");

        echo "\nConfiguration of the token properties...\n";
        $config["token"]["expire"] = self::prompt("[1 / 1] Expiration : ", 604800);

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
                    $contentFile = str_replace(strtoupper($type ."_" . $k), $v, $contentFile);
                }
            }
        }

        // Write the content of class
        fwrite($file, $contentFile);

        // Close and save the file
        fclose($file);
    }

    public static function generateEntities()
    {
        echo "Configuration of your models (identically to your tables)...\n";
        echo "Fetch tables...";

        $tables = Database::getTables();

        echo " OK\n";

        $numOfTables = count($tables);
        foreach ($tables as $key => $table) {

            // Get name of the table
            $table = $table[
            'Tables_in_' . Utils::getConfigElement('database')['dbname']
            ];

            // Start
            $num = $key + 1;
            $line = "============[$num / $numOfTables]============\n";
            echo $line;
            echo "CREATING MODEL $table...\n";

            $className = Utils::toPascalCase($table);

            // Config of the class
            $namespace = new PhpNamespace("Models");
            $class = $namespace->addClass($className);
            $class->addExtend("\Models\Entity");

            // Annotations class
            $class->addComment("Class $className")
                ->addComment("@package Models")
                ->addComment("@table $table");

            // Generate constructor
            $constructor = $class->addMethod("__construct")
                ->addComment("@param int id")
                ->setBody("\$this->id = \$id;\n");

            // Add id param to the constructor
            $constructor->addParameter("id", null);

            // Generate attributes, setters and getters
            foreach (Database::getColumns($table) as $column) {
                $field = $column["Field"];

                // Create attribute
                if (strpos($field, '_id')) {
                    $foreignKey = $field;

                    $field = substr($field, 0, strlen($field) - 3);

                    $type = Utils::toPascalCase($field);

                    // Ask the user if this is a good foreign key
                    // Re execute toPascalCase (if the user enter the table name)
                    $type = Utils::toPascalCase(
                        self::prompt("The $foreignKey target the table $type [If is not good, enter the good table, else just press enter] : ", $type)
                    );

                    // Add the new Object to the constructor
                    $constructor->setBody( $constructor->getBody() . "\$this->$field = new $type();\n" );
                } else {
                    $type = self::getTypeEntity( strtolower($column["Type"]) );
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
                }

                $class->addMethod("set$methodName")
                    ->setVisibility("public")
                    ->setBody("\$this->$field = \$$field;")
                    ->setParameters([$param])
                    ->addComment("@param $type $field");

                echo "=> $field... OK\n";
            }

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

            // End
            echo "CREATING MODEL $table... OK\n";
            echo str_repeat("=", strlen($line) - 1) . "\n\n";
        }
    }

    private static function getTypeEntity($columnType)
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
