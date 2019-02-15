<?php
namespace Controllers;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;

class Example extends Controller
{

    /**
     * Render slug and id passed in GET HTTP REQUEST
     * Method : GET
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function index(Request $request, Response $response)
    {
        //$response->setData(['slug' => $request->getParams()->slug, 'id' => $request->getParams()->id]);

        $response->getBody()
            ->add("slug", $request->getParams()->slug)
            ->add("id", $request->getParams()->id);

        return $response->fromApi("S_G001")->toYaml();
    }

    /**
     * Render all examples
     * Method : GET
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function getAll(Request $request, Response $response)
    {
        $response->setData(\Models\Example::getAll());

        return $response->render('S_PU001')->toJson();
    }

    /**
     * Update values to Database
     * Method : PUT
     * @param $id
     */
    public static function update(Request $request, Response $response)
    {
        $example = new \Models\Example($request->getParams()->id);

        $bodies = $request->getBodies();

        if (!empty($example)) {
            $example->setField1($bodies->field1);
            $example->setField2($bodies->field2);
            $example->setField3($bodies->field3);
            $example->store();

            return $response->render('S_PU001')->toJson();
        }

        return $response->render('E_A006', 'Example')->toJson();
    }

    /**
     * Add new row to Database
     * Method : POST
     */
    public static function add(Request $request, Response $response)
    {
        $example = new \Models\Example();

        Utils::setValuesInObject($example, $request->getBodies());

        $example->store();

        return $response->render('S_PO001')->toJson();
    }

    /**
     * Delete row to database
     * Method : DELETE
     * @param $id
     */
    public static function delete(Request $request, Response $response)
    {
        $example = new \Models\Example($request->getParams()->id);
        $example->delete();

        return $response->render('S_D001')->toJson();
    }
}