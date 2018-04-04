<?php
namespace Controllers;
use Kernel\Twig;
use Models\Testt;

class Example extends Controller
{
    /**
     * Render all examples
     * Method : GET
     */
    public static function index()
    {
        // Example need to be an table in your database
        self::_render('G001', \Models\Example::getAll());
    }

    /**
     * Render slug and id passed in GET HTTP REQUEST
     * Method : GET
     * @param $slug
     * @param $id
     */
    public static function index2($params)
    {
        self::_render('G001', ['slug' => $params->slug, 'id' => $params->id]);
    }

    /**
     * Update values to Database
     * Method : PUT
     * @param $id
     */
    public static function update($put, $params)
    {
        $example = new \Models\Example($params->id);
        if (!empty($example)) {
            $example->field1 = $put->field1;
            $example->field2 = $put->field2;
            $example->field3 = $put->field3;
            $example->update();

            self::_render('PU001');
        }

        self::_render('A004');
    }

    /**
     * Add new row to Database
     * Method : POST
     */
    public static function add($post)
    {
        $example = new \Models\Example();
        $example->field1 = $post->field1;
        $example->field2 = $post->field2;
        $example->field3 = $post->field3;
        $example->insert();

        self::_render('PO001');
    }

    /**
     * Delete row to database
     * Method : DELETE
     * @param $id
     */
    public static function delete($params)
    {
        $example = new \Models\Example($params->id);
        $example->delete();

        self::_render('D001');
    }
}