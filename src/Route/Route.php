<?php

namespace Chameleon2die4\WPBonesExtend\Route;

use WPKirk\WPBones\Routing\API\Route as BaseRoute;

class Route extends BaseRoute
{

    /**
     * @param string $url
     * @param $callback
     * @param array $args
     * @return void
     */
    public static function get(string $url, $callback, $args = [])
    {
        self::callMethod('get', $url, $callback, $args);
    }

    /**
     * @param string $url
     * @param $callback
     * @param $args
     * @return void
     */
    public static function post(string $url, $callback, $args = [])
    {
        self::callMethod('post', $url, $callback, $args);
    }

    /**
     * @param string $url
     * @param $callback
     * @param array $args
     * @return void
     */
    public static function put(string $url, $callback, $args = [])
    {
        self::callMethod('put', $url, $callback, $args);
    }

    /**
     * @param string $url
     * @param $callback
     * @param array $args
     * @return void
     */
    public static function patch(string $url, $callback, $args = [])
    {
        self::callMethod('patch', $url, $callback, $args);
    }

    /**
     * @param string $url
     * @param $callback
     * @param array $args
     * @return void
     */
    public static function delete(string $url, $callback, $args = [])
    {
        self::callMethod('delete', $url, $callback, $args);
    }

    /**
     * @param string $method
     * @param string $url
     * @param $callback
     * @param array $args
     * @return void
     */
    public static function callMethod(string $method, string $url, $callback, $args = [])
    {
        if (is_array($callback)) {
            $callback = implode('@', $callback);
        }

        call_user_func_array([parent::class, $method], [$url, $callback, $args]);
    }
}
