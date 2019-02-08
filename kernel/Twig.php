<?php
namespace Kernel;

use http\Url;
use Twig\TwigFilter;

class Twig
{
    public static function init()
    {
        $loader = new \Twig_Loader_Filesystem('../app/views/');
        $twig = new \Twig_Environment($loader, [
            'debug' => true
        ]);

        self::addFilter($twig);
        self::addExtension($twig);
        self::addGlobal($twig);

        return $twig;
    }

    public static function addFilter($twig)
    {
        $twig->addFilter( new \Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
            return (array) $stdClassObject;
        }));

        $twig->addFilter( new \Twig_SimpleFilter('ksort', function ($array) {
            ksort($array);
            return $array;
        }));
    }

    public static function addExtension($twig)
    {
        $twig->addExtension(new \Twig_Extension_Debug());
    }

    public static function addGlobal($twig)
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $path = substr($url, -4, 4) == 'docs' ? './' : '../';

        $twig->addGlobal('g_api_name', Config::get('project'));
        $twig->addGlobal('g_base_uri', 'http://' . $_SERVER['HTTP_HOST']);
        $twig->addGlobal('g_url', $url);
        $twig->addGlobal('g_postman_collection_id', '3ce1ea80bfce0b207a8b');
        $twig->addGlobal('g_path', $path);
        $twig->addGlobal('g_js', $path . 'js/');
        $twig->addGlobal('g_css', $path . 'css/');
        $twig->addGlobal('g_img', $path . 'img/');
    }
}