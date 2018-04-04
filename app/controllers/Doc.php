<?php
namespace Controllers;

class Doc extends Controller
{
    public static function index()
    {
        self::_view('doc', [
            'description' => self::_description(),
            'httpRequests' => self::_httpRequests(),
            'successCodes' => self::_statusCodes()[0],
            'errorCodes' => self::_statusCodes()[1],
            'docs' => self::_parseJsonDoc()
        ]);
    }

    /**
     * Get all docs created in json files (in the folder public/doc)
     * @return array
     */
    private static function _parseJsonDoc()
    {
        $docs = [];
        foreach (scandir('doc') as $doc) {
            if (!in_array($doc, ['.', '..'])) {
                $json = file_get_contents("doc/{$doc}");
                $docs[str_replace('.json', '', $doc)] = json_decode($json);
            }
        }

        return $docs;
    }

    /**
     * List of http requests methods allowed
     * @return array
     */
    private static function _httpRequests()
    {
        return [
            [
                'title' => 'GET',
                'content' => 'Retrieve a resource and list of resources'
            ],
            [
                'title' => 'POST',
                'content' => 'Add a new resource'
            ],
            [
                'title' => 'PUT',
                'content' => 'Update a resource with an identifier'
            ],
            [
                'title' => 'DELETE',
                'content' => 'Delete a resource with an identifier'
            ]
        ];
    }

    /**
     * List of status codes (success and error) in the status.json file (in the folder kernel)
     * @return array
     */
    private static function _statusCodes()
    {
        $json = file_get_contents("../kernel/status.json");
        $codes = json_decode($json);

        $successCodes = [];
        $errorCodes = [];
        foreach ($codes as $code) {
            if ($code->success) {
                $successCodes[] = $code;
            } else {
                $errorCodes[] = $code;
            }
        }

        return [$successCodes, $errorCodes];
    }

    /**
     * Set a description of the API
     * @return string
     */
    private static function _description()
    {
        return 'Welcome to the documentation of the <b>Lightwork API v2</b>.<br>
        This documentation allow you to understand this RESTful API. To navigate in the documentation, 
        you can use the menu by clicking on the menu icon <i class="material-icons tiny">menu</i> at top left.<br>
        Also, in this home page, you can see all methods of HTTPs requests allowed in the API at right and at bottom the success or error codes to which refer to';
    }

}