<?php

namespace Chameleon2die4\WPBonesExtend\Route;

use WPKirk\WPBones\Routing\API\Route as BaseRoute;

class Route extends BaseRoute
{

    /**
     * @param string $url
     * @param string|array $callback
     * @return void
     */
    public static function get(string $url, $callback) {
        self::callMethod('get', $url, $callback);
    }

    /**
     * @param string $url
     * @param string|array $callback
     * @return void
     */
    public static function post(string $url, $callback) {
        self::callMethod('post', $url, $callback);
    }

    /**
     * @param string $url
     * @param string|array $callback
     * @return void
     */
    public static function put(string $url, $callback) {
        self::callMethod('put', $url, $callback);
    }

    /**
     * @param string $url
     * @param string|array $callback
     * @return void
     * @noinspection PhpUnused
     */
    public static function patch(string $url, $callback) {
        self::callMethod('patch', $url, $callback);
    }

    /**
     * @param string $url
     * @param string|array $callback
     * @return void
     */
    public static function delete(string $url, $callback) {
        self::callMethod('delete', $url, $callback);
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public static function callMethod(string $method, string $url, $callback) {
        if (is_array($callback)) {
            $callback = implode('@', $callback);
        }

        call_user_func_array([parent::class, $method], [$url, $callback, []]);
    }

}
