<?php

namespace Chameleon2die4\WPBonesExtend\Contracts;

interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson(int $options = 0);
}
