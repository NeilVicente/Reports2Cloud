<?php
    include "lib/header.php";
    $users = User::countUsers();
    $user_count = ( isset($_GET['debug']) AND count($users) > 0 ) ? (number_format($users) . ' users trust ' . APP_NAME) : false;
?>
<!doctype html>
<html lang="en">
    <head>
        <?=PAGE_HEAD?>
        <title><?=PAGE_TITLE?></title>
        <?=PAGE_STYLES?>
    </head>

    <body id="main_page_container">
        <div id="loading-modal" class="dn"></div>
        <?php if($user_count){ ?><div class="app-users-info">          
            <span><?=$user_count?></span><br>
        </div><?php } ?>
        <div id="application_landing">
            <div class="slides-background">
                <header id="header">
                    <div class="header">
                        <div class="header-content">
                            <a href="/" class="logo-link">
                                <img src="css/images/jotformDevLogo.png" alt="JotForm Developers">
                            </a>
                        </div>
                    </div>
                </header>
                <div class="content-container">
                    <div class="content">
                        <div class="banner-area">
                            <div class="banner-content">
                                <div class="title">Send JotForm Excel Reports to Your Dropbox!</div>
                                <div class="banner-text">You use Excel reports to track your form data and want to save it to a cloud storage environment such as Dropbox. Everyday, you’ll either enter new data to your Excel report or download the newest report to your Dropbox. Ever wonder if there's a way to automate this process? From now on, you'll be able to automatically enter new data into your Excel reports at a customizable time interval and let it update to your Dropbox folder. No more need for manual entries or sharing!</div>
                            </div>
                            <div class="visual">
                                <p><img src="css/images/excel2dropbox.png" alt=""></p>
                            </div>
                            <div class="integrate_btn">
                                <button data-pickertype="proceed" class="ladda-button pure-button pure-green" id="integrate_now-btn" data-style="zoom-out">Integrate Now!</button>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer" id="footer">
                    <div class="tm">
                        <span>Powered by </span>
                        <span><a href="http://www.jotform.com">JotForm</a></span>
                        <span class="app-g"><a href="http://apps.jotform.com">JotForm Apps</a></span>
                    </div>
                </footer>
            </div>
        </div>
        <div class="meny-menu dn">
            <div class="meny-navi-cont">
                <div class="meny-navi"></div>
            </div>
            <div class="sidebar-list">
                <div id="account-details"></div>
                <div id="reports-list"></div>
                <div class="app-footer-inline">
                    <a href="http://www.jotform.com/" title="JotForm - Form builder" alt="jotform" target="_blank" class="white">JotForm</a> | 
                    <a href="http://report2dropbox.jotform.io/" title="<?=APP_NAME?>" alt="<?=strtolower(APP_NAME)?>" target="_blank"><?=APP_NAME?></a> <br/>
                    <span>© 2013 <a href="http://www.google.com/recaptcha/mailhide/d?k=01zXnyZ97-oLOl4pY5AuarnA==&amp;c=-aqY40GERMz4XMdaOID1yIcIk9_F6i3S2ktFRrlTzng=" onclick="window.open('http://www.google.com/recaptcha/mailhide/d?k\07501zXnyZ97-oLOl4pY5AuarnA\75\75\46c\75-aqY40GERMz4XMdaOID1yIcIk9_F6i3S2ktFRrlTzng\075', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;" title="Reveal this e-mail address">Kenneth Palaganas</a></span>
                </div>
            </div>
        </div>
        <div id="picker-container" class="meny-contents picker-navigations reveal"></div>

        <?=PAGE_SCRIPTS?>

    <!-- Google Analytics Code -->
    <script type="text/javascript">

          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', 'UA-1170872-7']);
          _gaq.push(['_setDomainName', 'jotform.com']);
          _gaq.push(['_setAllowLinker', true]);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();

    </script>

    <script type="text/javascript">
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-51531118-1', 'jotform.io');
      ga('send', 'pageview');

    </script>

    <script src="http://cdn.jotfor.ms/static/feedback2.js?3.1.12" type="text/javascript">
        new JotformFeedback({
            formId     : "32795472589876",
            buttonText : "Feedback",
            base       : "http://jotform.co/",
            background : "#F59202",
            fontColor  : "#FFFFFF",
            buttonSide : "left",
            buttonAlign: "center",
            type       : false,
            width      : "700px"
        });
    </script>
    </body>
</html>
