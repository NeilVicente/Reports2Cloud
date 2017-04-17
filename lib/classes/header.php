<?php

require_once("init.php");


// define("SCRIPT_DIR", "scripts/");
// define('LIB_DIR', getcwd() . DIRECTORY_SEPARATOR . "lib");

// if ( MODE === "live" )
// {
//     include( LIB_DIR . "/php-closure.php");

//     $scriptsListTop = array(
//         SCRIPT_DIR . "lib/jstorage.js",
//         SCRIPT_DIR . "lib/jminicolors.js",
//         SCRIPT_DIR . "lib/clipboard/zclip.js",
//         SCRIPT_DIR . "lib/rawdeflate_inflate.js",
//         SCRIPT_DIR . "lib/base64.js",
//         SCRIPT_DIR . "lib/charts/dx.chartjs.js",
//         SCRIPT_DIR . "lib/charts/globalize.js"
//     );

//     $scriptsListModels = SCRIPT_DIR . "models/";
//     $scriptsListViews = SCRIPT_DIR . "views/";

//     $scriptsListBottom = array(
//         SCRIPT_DIR . "router.js",
//         SCRIPT_DIR . "maincore.js",
//         SCRIPT_DIR . "lib/flat/bootstrap-switch.js",
//         SCRIPT_DIR . "lib/flat/bootstrap-select.js",
//         SCRIPT_DIR . "lib/flat/flatui-checkbox.js",
//         SCRIPT_DIR . "lib/flat/flatui-radio.js"
//     );

//     $c = new PhpClosure();
//     $c->addFromArray($scriptsListTop)
//     ->addDir($scriptsListModels)
//     ->addDir($scriptsListViews)
//     ->addFromArray($scriptsListBottom)
//     ->hideDebugInfo()
//     ->setLanguageECMA("ECMASCRIPT5")
//     ->setCacheName('scripts-min')
//     ->cacheDir(SCRIPT_DIR)
//     ->write(false);
// }

$header = '
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta name="HandheldFriendly" content="true" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <base href="' . HTTP_URL . '" />
';

$styles = '
    <link rel="Shortcut Icon" href="favicon.ico" />
    <!-- <link rel="stylesheet" href="' . HTTP_URL . 'css/normal.css" type="text/css" media="screen, projection" /> -->
    <link rel="stylesheet" href="' . HTTP_URL . 'css/font/stylesheet.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="' . HTTP_URL . 'css/font-awesome.css">
    <!--[if IE 7]><link rel="stylesheet" href="' . HTTP_URL . 'css/font-awesome-ie7.css"><![endif]-->
';

define("PAGE_HEAD", $header);
define("PAGE_STYLES", $styles);
define("PAGE_TITLE", "Reports to Dropbox");


?>