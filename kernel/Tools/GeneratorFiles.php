<?php
namespace Kernel\Tools;

class GeneratorFiles
{
    /**
     * Models files
     * @var bool 
     */
    private $models;

    /**
     * GeneratorFiles constructor.
     * @param bool $models
     */
    public function __construct($models = false)
    {
        $this->models = $models;
    }
    
    private function models()
    {
        foreach (\Kernel\Database::getTables() as $table) {

            $table = $table[
            'Tables_in_' . \Kernel\Config::get('database')['db']
            ];

            $tableRenamed = implode(array_map('ucfirst', explode('_', $table)));

            // Start of class
            $class = "<?php\n".
                "namespace Models;\n".
                "use Kernel\Database;\n".
                "\n".
                "class $tableRenamed extends Database\n".
                "{\n".
                "   /**\n".
                "    * Exact name of the table\n".
                "    * @var string\n".
                "    */\n".
                "   protected static \$_table = \"$table\";\n".
                "\n";

            // Generate attributes
            foreach (\Kernel\Database::getColumns($table) as $column) {
                $field = $column['Field'];

                $type = strpos("int", $column['Type']) ? "int" : "string";

                $class .= 
                    "   /**\n".
                    "    * @var $type\n".
                    "    */\n".
                    "   protected \$$field;\n".
                    "\n";

            }

            // Generate setters and getters
            foreach (\Kernel\Database::getColumns($table) as $column) {
                $field = $column['Field'];
                $fieldRenamed = implode(array_map('ucfirst', explode('_', $field)));

                $type = strpos("int", $column['Type']) ? "int" : "string";

                $class .=
                    "   /**\n".
                    "    * @return $type\n".
                    "    */\n".
                    "   public function get$fieldRenamed()\n".
                    "   {\n".
                    "       return \$this->$field;\n".
                    "   }\n".
                    "\n".
                    "   /**\n".
                    "    * @param $type \$$field\n".
                    "    */\n".
                    "   public function set$fieldRenamed(\$$field)\n".
                    "   {\n".
                    "       \$this->$field = \$$field;\n".
                    "   }\n";

            }

            // End of class
            $class .= "}";

            // Get directory
            $dir = dirname(__FILE__);

            // Get real path to the root path
            $rootDir = realpath($dir . '/../..');

            // Get the path of the file to create
            $path = "$rootDir/app/models/$tableRenamed.php";
            
            // Open/Create the file
            $file = fopen($path, "w");

            // Write the content of class
            fwrite($file, $class);

            // Close and save the file
            fclose($file);
        }
    }

    public function run()
    {
        foreach ($this as $property => $value) {
            if ($value) {
                // If the property is true, call the method with the same name
                $this->$property();
            }
        }
    }
}