<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Services;

use Chameleon2die4\WPBonesExtend\Storage;
use WPKirk\WPBones\Foundation\Plugin;

class Service
{

    /**
     * @var Plugin
     */
    static Plugin $plugin;

    private static Service $instance;
    /**
     * @var string
     */
    private static string $pluginPath;
    /**
     * @var string
     */
    private static string $pluginUrl;

    public function __construct(Plugin $plugin = null)
    {
        /** @noinspection PhpFullyQualifiedNameUsageInspection,PhpUndefinedClassInspection */
        $plugin = $plugin ?? \WPKirk::$plugin;
        self::init($plugin);
    }

    public static function init(Plugin $plugin = null) {
        self::$plugin = $plugin;
        self::$pluginPath = Storage::getPluginPath();
        self::$pluginUrl = Storage::getPluginUrl();
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function sendRequest(string $route, array $data = [], string $method = 'GET')
    {
        $plugin = self::getPlugin();
        $token = $plugin->options->get('general.token');
        $url = $plugin->options->get('general.api_url');
        $base = $plugin->config('casinomass.base');
        $method = strtoupper($method);
        /** @noinspection PhpUndefinedFunctionInspection */
        $response = wp_remote_request(
            $url . $base . $route,
            [
                'method'  => $method,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'body'    => $method === 'POST' ? json_encode($data) : $data,
            ]
        );

        /** @noinspection PhpUndefinedFunctionInspection */
        if (is_wp_error($response)) {
            // Handle the error
            $error_message = $response->get_error_message();
            return ['error' => $error_message];
        }

        /** @noinspection PhpUndefinedFunctionInspection */
        $body = wp_remote_retrieve_body($response);

        if ($body === null) {
            // Handle the empty response body
            return ['error' => 'Empty response'];
        }

        $decoded_body = json_decode($body);

        if ($decoded_body === null) {
            // Handle the error in decoding the response body
            return ['error' => 'Error decoding response body'];
        }

        return $decoded_body;
    }

    /**
     * @return Plugin
     */
    public static function getPlugin(): Plugin
    {
        return self::$plugin;
    }

    /**
     * @param Plugin $plugin
     */
    public static function setPlugin(Plugin $plugin): void
    {
        self::$plugin = $plugin;
    }

    /**
     * @return string
     */
    public static function getPluginPath(): string
    {
        return self::$pluginPath;
    }

    /**
     * @param string $pluginPath
     */
    public static function setPluginPath(string $pluginPath): void
    {
        self::$pluginPath = $pluginPath;
    }

    /**
     * @return string
     */
    public static function getPluginUrl(): string
    {
        return self::$pluginUrl;
    }

    /**
     * @param string $pluginUrl
     */
    public static function setPluginUrl(string $pluginUrl): void
    {
        self::$pluginUrl = $pluginUrl;
    }

}
