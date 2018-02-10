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
     * Create random token
     * @return bool|string
     */
    protected static function _createToken()
    {
        $token = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($token, 50)), 0, 50) . '/' . sha1($_SERVER['REMOTE_ADDR']);
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
    protected static function _parse_http_put() {
        $datas = new \stdClass();
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
                $datas->{$matches[1]} = $matches[2];
            } else {
                $datas->{$id} = $block;
            }
        }
        return $datas;
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

    /**
     * Render to json data sended
     * @param $code
     * @param null $data
     * @param bool $success
     * @param null $msg
     */
    protected static function _render($code, $data = null, $success = true, $msg = null)
    {
        $status = Status::getStatus($code);
        $render = [
            ($success ? 'success' : 'error') => true,
            'status' => [
                'code' => $code,
                'ref' => $status->ref
            ]
        ];

        if (null != $msg) $render['status']['message'] = $msg;

        if (null != $data) $render['data'] = $data;

        http_response_code($status->http_code);
        self::_toJson($render);
    }
}