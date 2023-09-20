<?php

namespace Chameleon2die4\WPBonesExtend\Traits;

trait CallStatic
{
    /**
     * __callStatic
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, $arguments)
    {
        return (new static)->$method(...$arguments);
    }

    /**
     * __call
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        return (new static)->$method(...$arguments);
    }
}
