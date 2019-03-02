<?php
namespace Controllers;
use DusanKasan\Knapsack\Collection;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;

class Example extends Controller
{

    /**
     * @name Get slug and id
     * @token
     * @codes S_G001
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function index(Request $request, Response $response)
    {
        $response->getBody()
            ->add($request->getArgs()->get("slug"), "slug")
            ->add($request->getArgs()->get("id"), "id");

        return $response->fromApi("S_G001")->toJson();
    }

    /**
     * @name Get all examples
     * @codes S_G001
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function getAll(Request $request, Response $response)
    {
        $response->setBody(new \Kernel\Tools\Collection\Collection(
            \Models\Example::getAll()
        ));

        return $response->fromApi('S_G001')->toJson();
    }

    /**
     * @name Edit an example
     * @token
     * @codes S_PU001, E_A005
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function update(Request $request, Response $response)
    {
        $example = new \Models\Example($request->getArgs()->get("id"));

        $body = $request->getBody();

        if (!empty($example)) {
            $example->setField1($body->get("field1"));
            $example->setField2($body->get("field2"));
            $example->setField3($body->get("field3"));
            $example->store();

            return $response->fromApi('S_PU001')->toJson();
        }

        return $response->fromApi('E_A005', 'Example')->toJson();
    }

    /**
     * @name Add a new example
     * @codes S_PO001
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function add(Request $request, Response $response)
    {
        $example = new \Models\Example();

        Utils::setValuesInObject($example, $request->getBodies());

        $example->store();

        return $response->fromApi('S_PO001')->toJson();
    }

    /**
     * @name Remove an example
     * @codes S_D001
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function delete(Request $request, Response $response)
    {
        $example = new \Models\Example($request->getArgs()->get("id"));
        $example->delete();

        return $response->fromApi('S_D001')->toJson();
    }
}
