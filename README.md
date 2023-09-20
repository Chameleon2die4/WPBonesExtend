# WPBonesExtend
[![PHP Composer](https://github.com/Chameleon2die4/WPBonesExtend/actions/workflows/php.yml/badge.svg)](https://github.com/Chameleon2die4/WPBonesExtend/actions/workflows/php.yml)
[![Latest Version](https://img.shields.io/github/v/tag/Chameleon2die4/WPBonesExtend?sort=semver&label=version)](https://github.com/Chameleon2die4/WPBonesExtend/)
[![Packagist](https://badgen.net/packagist/v/chameleon2die4/wpbones-extend/latest)](https://packagist.org/packages/chameleon2die4/WPBonesExtend/)
[![PHP Version Require](https://img.shields.io/badge/php-%3E=7.4-green.svg)](https://www.php.net/docs.php)
[![License](https://img.shields.io/badge/license-GPL3-blue.svg)](https://github.com/Chameleon2die4/WPBonesExtend/blob/master/LICENSE.md)

[//]: # ([![Packagist]&#40;https://img.shields.io/packagist/v/chameleon2die4/WPBonesExtend/&#41;]&#40;https://packagist.org/packages/chameleon2die4/WPBonesExtend/&#41;)

This library add additional functionality for WP Bones framework.

## Installation

### Composer:

Browse into directory and run;

```shell
$ composer require chameleon2die4/wpbones-extend
```

You can configure your `composer.json` to copy files when you update your dependencies:

```
...
"scripts": {
    "post-update-cmd": [
        "Chameleon2die4\\WPBonesExtend\\WPBonesExtend::copyInitFiles"
    ]
},
```

If you rename plugin - change Chameleon2die4 to your namespace.

It's copy new bones files, with new commands. Additional copy stubs templates for commands.

After files will be copied, you can remove this part from `composer.json`.
