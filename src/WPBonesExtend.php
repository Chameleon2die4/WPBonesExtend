<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend;

final class WPBonesExtend
{

    public static function copyInitFiles() {
        $dir = dirname(__FILE__);
        $exp = explode('vendor', $dir);
        $path = $exp[0];
        copy( $dir . '/Console/bin/bones', $path . 'bones');

        if (!file_exists($path . 'plugin/Console/')) {
            mkdir($path . 'plugin/Console/');
        }

        $stubs = $path . 'plugin/Console/stubs/';
        if (!file_exists($stubs)) {
            mkdir($stubs);
        }

        foreach (glob($dir . '/Console/stubs/*.stub') as $file) {
            $name = basename($file);

            copy($file, $stubs . $name);
        }
    }

}
