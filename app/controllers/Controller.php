<?php
namespace Controllers;
use Kernel\Config;
use Kernel\Tools\Alert;
use Kernel\Tools\Code;
use Kernel\Tools\Status;
use Kernel\Twig;
use Models\User;

class Controller
{
    protected static $_days = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];

    /**
     * Generate view
     * @param $view
     * @param $data
     */
    protected static function _view($view, $data = [])
    {
        $twig = Twig::init();

        echo $twig->render($view . '.html.twig', $data);
        exit();
    }

    /**
     * Remove attributes on array
     * @param array $array
     * @param array $attrs
     * @param string $type
     * @return array
     */
    protected static function _removeAttrs($array = [], $attrs = [], $type = 'obj')
    {
        foreach ($array as $k => $v) {
            switch ($type) {
                case 'obj':
                    foreach ($attrs as $attr) {
                        unset($v->$attr);
                    }
                    break;

                case 'array':
                    foreach ($attrs as $attr) {
                        unset($v[$attr]);
                    }
                    break;
            }
        }
        return $array;
    }

    /**
     * Search if value (needle) is in multidimensional array
     * @param $array
     * @param $field
     * @param $needle
     * @return bool|int|string
     */
    protected static function _in_multi_array($array, $field, $needle)
    {
        foreach($array as $key => $value)
        {
            $needle = explode('/', $needle);
            if (in_array($value[$field], $needle)) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Get content header by header name
     * @param null $header
     * @return string
     */
    public static function getHeader($header = null)
    {
        if (!function_exists('getallheaders')) {
            return !empty(self::_getallheaders()[$header]) ? self::_getallheaders()[$header] : null;
        }
        return !empty(getallheaders()[$header]) ? getallheaders()[$header] : null;
    }

    /**
     * Get all headers if haven't default getallheaders() function
     * @return array
     */
    private static function _getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Create random token
     * @return bool|string
     */
    protected static function _createToken($withIp = true)
    {
        $token = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($withIp) {
            return substr(str_shuffle(str_repeat($token, 50)), 0, 50) . '/' . sha1($_SERVER['REMOTE_ADDR']);
        } else {
            return substr(str_shuffle(str_repeat($token, 50)), 0, 50);
        }
    }

    /**
     * Check if needle is between min and max values
     * @param int $needle
     * @param int $min
     * @param int $max
     * @return bool
     */
    protected static function _between($needle, $min = null, $max = null)
    {
        switch (true) {
            // If null == $min && null == $max
            case null == $min && null == $max:
                return true;

            // If needle == null && (null != $min || null != $max)
            case null == $needle:
                return false;

            // If needle != null && (null != $min && null == $max)
            case null != $min && null == $max:
                return ($needle >= $min) ? true : false;

            // If needle != null && (null == $min && null != $max)
            case null == $min && null != $max:
                return ($needle <= $max) ? true : false;

            // If all var != null
            default:
                return ($needle >= $min && $needle <= $max) ? true : false;
        }
    }

    /**
     * Transform date Fr to Us
     * @param $date
     * @return string
     */
    protected static function _dateUs($date)
    {
        $tabDate = explode('/', $date);
        return $tabDate[0] . '-' . $tabDate[1] . '-' . $tabDate[2];
    }

    /**
     * Get the timestamp of the date
     * @param $date
     * @return int
     */
    protected static function _toTimestamp($date)
    {
        $newDate = new \DateTime($date);
        return $newDate->getTimestamp();
    }

    /**
     * @param $pattern
     * @param $subject
     * @return int
     */
    protected static function _match($pattern, $subject)
    {
        return preg_match(Config::getReg()[$pattern], $subject);
    }

    /**
     * Parse PUT datas for handle
     * @return \stdClass
     */
    public static function parse_http_put() {
        $data = new \stdClass();
        $input = file_get_contents('php://input');
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        if ($matches) {
            $boundary = $matches[1];
            $a_blocks = preg_split("/-+$boundary/", $input);
            array_pop($a_blocks);
        } else {
            parse_str($input, $a_blocks);
        }

        foreach ($a_blocks as $id => $block) {
            if (empty($block)) { continue; }


            if (strpos($block, 'application/octet-stream') !== FALSE) {
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            }
            else {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            if ($matches) {
                $data->{$matches[1]} = $matches[2];
            } else {
                $data->{$id} = $block;
            }
        }
        return $data;
    }

    /**
     * @param $var
     */
    protected static function _toJson($var)
    {
        header('Content-Type:application/json');
        echo json_encode($var);
        exit();
    }

    protected static function _addEventLog($code, $status, $method, $endpoint)
    {
        $date = date('d/m/Y H:i:s');
        file_put_contents('../kernel/logs/log.csv', "\n$code, $date, $status, $method, $endpoint", FILE_APPEND);
    }

    /**
     * Render to json data sent
     * @param $code
     * @param null $data
     */
    protected static function _render($code, $data = null)
    {
        $res = Config::getResponse($code);

        // Keep response success or error
        $success = $res->success;
        // Unset to doesn't show "success": true or "success": false
        unset($res->success);

        $res->method = $_SERVER['REQUEST_METHOD'];
        $res->endpoint = $_SERVER['REQUEST_URI'];

        $render = [
            $success ? 'success' : 'error' => $res
        ];

        // Register error in logs
        if (!$success) self::_addEventLog($code, $res->status, $res->method, $res->endpoint);

        if (null != $data) $render['data'] = $data;

        http_response_code($res->status);
        self::_toJson($render);
    }
}
