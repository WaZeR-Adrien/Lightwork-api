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
        self::_render(200, \Models\Example::getAll());
    }

    /**
     * Render slug and id passed in GET HTTP REQUEST
     * Method : GET
     * @param $slug
     * @param $id
     */
    public static function index2($slug, $id)
    {
        self::_render(200, ['slug' => $slug, 'id' => $id]);
    }

    /**
     * _parse_http_put() allow to retrieve datas sended to PUT HTTP REQUEST
     * Update values to Database
     * Method : PUT
     * @param $id
     */
    public static function update($id)
    {
        $datas = self::_parse_http_put();
        $example = new \Models\Example($id);
        $example->field1 = $datas->field1;
        $example->field2 = $datas->field2;
        $example->field3 = $datas->field3;
        $example->update();
    }

    /**
     * Add new row to Database
     * Method : POST
     */
    public static function add()
    {
        $example = new \Models\Example();
        $example->field1 = $_POST['key'];
        $example->field2 = $_POST['key'];
        $example->field3 = $_POST['key'];
        $example->insert();
    }

    /**
     * Delete row to database
     * Method : DELETE
     * @param $id
     */
    public static function delete($id)
    {
        $example = new \Models\Example($id);
        $example->delete();
    }
}