<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Dropbox Authentication</title>
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
    <script type="text/javascript" src="<?=HTTP_URL?>scripts/lib/json2.js"></script>
    <script type="text/javascript" src="<?=HTTP_URL?>scripts/lib/xds-server/xds.js"></script>
    <script>
        window.onload = function(){
            var _closeWindow = function()
            {
                window.open('', '_self', '');
                window.close();
            };

            //create a new xds instance
            var x = new XDS("<?=HTTP_URL?>")
              , key = "dropbox_data"
              , val = {
                access_token:'<?=$access_token?>',
                access_token_secret:'<?=$access_token_secret?>',
                user: JSON.parse('<?=$user?>')
            };

            //set a key to the local storage
            x.setItem( key, JSON.stringify(val), function(response){
                x.getItem( key, function(val, response){
                    // if (window.console) {
                    //     window.console.log('Item get, response: ', response);
                    // }
                    _closeWindow();
                });
            });
        };
    </script>
</head>
<body>
    <div>
        <p>
            <span style="color: green;">Authentication is successful.</span><br/>
            <input type="button" name="window_close" id="window_close" onclick="_closeWindow();" value="Close Window" style="padding: 3px;">
        </p>
    </div>
</body>
</html>