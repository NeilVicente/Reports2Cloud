<?php
    
    require_once(__DIR__ . "/init.php");

    $header = '
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <meta name="HandheldFriendly" content="true" />
        <meta name="keywords" content="report, jotform reports, excel, cloud storage, automatic upload, upload to cloud, dropbox, box, google drive" />
        <meta name="description" content="Automatically send your jotform reports to your cloud storage such as Dropbox, Box and Google Drive in an easy and fastest way" />
        <meta name="google-site-verification" content="B1zJkBnd0nD-P4tryzAA-N66WQIuHlJEwRG42voBAL4" />
        <meta property="og:title" content="Jotform reports to Dropbox, Box, Google Drive" />
        <meta property="og:description" content="Automatically send your jotform reports to your cloud storage such as Dropbox, Box and Google Drive in an easy and fastest way" />
        <meta property="og:image" content="http://cms.interlogy.com/uploads/image_upload/image_upload/global/9260_150X150.jpg" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://report2dropbox.jotform.io/" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:url" content="http://report2dropbox.jotform.io/" />
        <meta name="twitter:title" content="Jotform reports to Dropbox, Box, Google Drive" />
        <meta name="twitter:description" content="Automatically send your jotform reports to your cloud storage such as Dropbox, Box and Google Drive in an easy and fastest way" />
        <meta name="twitter:image" content="http://cms.interlogy.com/uploads/image_upload/image_upload/global/9260_150X150.jpg" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <base href="' . HTTP_URL . '" />
    ';

    if ( MODE == 'dev' )
    {
        $styles = 
        '
            <link rel="Shortcut Icon" href="' . HTTP_URL . 'css/images/favicon.ico" />
            <link rel="stylesheet" href="' . HTTP_URL . 'css/import-fonts.css" type="text/css" media="screen, projection" />
            <link rel="stylesheet" href="' . HTTP_URL . 'css/pure.css" type="text/css" media="screen, projection" />
            <link rel="stylesheet" href="' . HTTP_URL . 'css/main.css" type="text/css" media="screen, projection" />
            <link rel="stylesheet" href="' . HTTP_URL . 'css/font/font.css" type="text/css" media="screen, projection" />
            <link rel="stylesheet" href="' . HTTP_URL . 'css/font-awesome.css" type="text/css" media="screen, projection" >
            <!--[if IE 7]><link rel="stylesheet" href="' . HTTP_URL . 'css/font-awesome-ie7.css"><![endif]-->
            <link rel="stylesheet" href="' . HTTP_URL . 'scripts/lib/avgrund/avgrund.css" type="text/css" media="screen, projection" >
            <link rel="stylesheet" href="' . HTTP_URL . 'scripts/lib/reveal/reveal.css" type="text/css" media="screen, projection" >
            <link rel="stylesheet" href="' . HTTP_URL . 'scripts/lib/reveal/theme/default.css" type="text/css" media="screen, projection" >
            <link rel="stylesheet" href="' . HTTP_URL . 'scripts/lib/ladda/ladda-themeless.css" type="text/css" media="screen, projection" >
            <link rel="stylesheet" href="' . HTTP_URL . 'scripts/lib/scrollbar/scrollbar.css" type="text/css" media="screen, projection" >
        ';
    }
    else
    {
        $styles = 
        '
        <link rel="stylesheet" href="' . HTTP_URL . 'css/import-fonts.css" type="text/css" media="screen, projection" />
        <link rel="stylesheet" href="' . HTTP_URL . 'css/reports2cloud.min.css" type="text/css" media="screen, projection" />
        ';
    }


    $jotformFiles = 
    '
        <script type="text/javascript" src="'. ((MODE == 'dev') ?  HTTP_URL . "scripts/lib/JotForm.js" : "//js.jotform.com/JotForm.js?3.1.{REV}") . '"></script>
        <script type="text/javascript" src="'. ((MODE == 'dev') ?  HTTP_URL . "scripts/lib/FormPicker.js" : "//js.jotform.com/FormPicker.js?3.1.{REV}") . '"></script>
        <script type="text/javascript" src="'. ((MODE == 'dev') ?  HTTP_URL . "scripts/lib/ReportPicker.js" : "//js.jotform.com/ReportPicker.js?3.1.{REV}") . '"></script>
    ';

    $sripts = 
    '
        <!--[if lt IE 7]><script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE7.js"></script><![endif]-->
        <!--[if lt IE 8]><script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js"></script><![endif]-->
        <!--[if lt IE 9]><script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
            
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20121008/json2.js"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.0/backbone-min.js"></script>
    '.$jotformFiles;

    if ( MODE == 'dev' )
    {
        $sripts .=
        '
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/enhance.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/jstorage.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/tools.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/avgrund/avgrund.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/scrollbar/scrollbar.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/classList.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/html5shiv.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/reveal/reveal.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/ladda/jquery.ladda.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/meny/meny.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/xds-server/xds.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/lib/popup.js"></script>

        <script type="text/javascript" src="' . HTTP_URL . 'scripts/models/account-model.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/models/picker-model.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/modal-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/account-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/picker-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/form-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/report-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/dropboxfolders-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/file-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/sidebar-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/views/mainpage-view.js"></script>
        <script type="text/javascript" src="' . HTTP_URL . 'scripts/maincore.js"></script>
        ';
    }
    else
    {
        $sripts .= '<script type="text/javascript" src="' . HTTP_URL . 'scripts/reports2cloud.min.js"></script>';
    }


    define("PAGE_HEAD", $header);
    define("PAGE_STYLES", $styles);
    define("PAGE_SCRIPTS", $sripts);
    define("PAGE_TITLE", "JotForm Reports To Cloud Storage");
    define("APP_NAME", "Reports2Cloud");

?>