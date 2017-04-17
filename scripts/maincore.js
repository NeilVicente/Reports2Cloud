$(window).load(function(){

    if (
        JF && typeof JF === 'object' &&
        Backbone && typeof Backbone === 'object'
    )
    {
        // Extend Backbone with events so we can use custom events
        _.extend(Backbone, Backbone.Events);

        var _excel2Dropbox = {};
        Backbone.View.prototype.global = _excel2Dropbox;
        Backbone.Model.prototype.global = _excel2Dropbox;
        Backbone.Router.prototype.global = _excel2Dropbox;

        _excel2Dropbox.sidebarView = new _e2D_SidebarView();
        _excel2Dropbox.mainpageView = new _e2D_MainpageView();
        _excel2Dropbox.accountModel = new _e2D_AccountModel();
        _excel2Dropbox.accountView = new _e2D_AccountView();
        _excel2Dropbox.pickerModel = new _e2D_PickerModel();
        _excel2Dropbox.pickerView = new _e2D_PickerView();
        _excel2Dropbox.formView = new _e2D_FormView();
        _excel2Dropbox.reportView = new _e2D_ReportView();
        _excel2Dropbox.dropboxfoldersView = new _e2D_DropboxFoldersView();
        _excel2Dropbox.filesView = new _e2D_FilesView();

        //main executor
        var _e2D_ = function()
        {
            this.initJF = function(cb)
            {
                var self = this;

                //init JF
                JF.init({
                    enableCookieAuth : false,
                    appName: "Excel2Dropbox",
                    accesType: "readOnly" //default "readOnly" or "full"
                });

                if ( !JF.getAPIKey() )
                {
                    var a = JF.login(
                        function success(){
                            //console.log('success');
                        if(cb) cb.call(self);
                    }, function error(e){
                            //console.log(e);
                            console.error("Error occured!", e);
                        // if(cb) cb.apply(self);
                    });
                }
                else
                {
                    if(cb) cb.call(self);
                }
            };

            this.require = function(req)
            {
                if ( req.length > 0 )
                {
                    for ( x in req )
                    {
                        //console.log('call-' + req[x]);
                        Backbone.trigger('call-' + req[x]);
                    }
                }
            };

            this.init = function()
            {
                //load some scripts and css
                var self = this
                  , HTTP_URL = _excel2Dropbox.mainpageView.getHTTPURL();

                this.initJF(function(){
                    self.require(['mainpage-view']);
                });
            };
        };

        var mainExec = new _e2D_();
            mainExec.init();

        window.base = $('base').attr('href').split(location.origin)[1];
        // Backbone.history.start({pushState: true, root: base});
        // Backbone.history.start();
    }
    else
    {
        console.error("Required Libraries: Backbone OR JotForm API were missing in action");
    }
});

