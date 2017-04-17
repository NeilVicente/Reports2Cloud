var _e2D_AccountView = Backbone.View.extend({

    initialize: function()
    {
        //console.log("init");
        this._acc_storage = '_r2D_userData';
    },

    removeJFUserData: function(next)
    {
        JF.logout();
        $.jStorage.deleteKey(this._acc_storage);
        if (next) next();
    },

    checkJFUserData: function(next)
    {
        var self = this;
        var data = $.jStorage.get(self._acc_storage);
        if (next) next.call(self, data);
    },

    handleUserFromBackground: function(user)
    {
        //console.log("logging user from background");
        this.handleAccount(JSON.parse(user), function(response){
            //console.log("Successfully logged in from background", response);
        });
    },

    handleAccount: function(user, next)
    {
        var self = this
          , dataParam = {
                action: 'handleAccount',
                username: user.username,
                email: user.email,
                key: JF.getAPIKey()
            };

        $.ajax({
            url: 'server.php',
            method: 'POST',
            data: dataParam,
            success: function(dataR)
            {
                //delete some unecessary data
                delete dataR.user_data['jotform_username'];
                delete dataR.user_data['jotform_email'];
                

                //merge it to model
                var _user = JSON.stringify(array_merge(user, dataR.user_data));

                //set data to model - to be accessed later
                self.setAccountObject(_user);

                if (next) next({status: "success",message: dataR.message, data: _user});
            },
            error: function(error)
            {
                console.error(error);
                throw new Error("Something went wrong handling account");
            }
        });
    },

    handleJFUser: function(next)
    {
        var self = this;
        //get user information from JotForm
        JF.getUser(function(user){

            //delete some unecessary data
            delete user['senderEmails'];

            //check if active 
            if ( String(user.status).toLowerCase() === 'active' )
            {
                //send to server
                self.handleAccount(user, function(response){
                    //console.log("Created", response);
                    if (next) next(response);
                });
            } else {
                throw new Error("User is not ACTIVE anymore");
            }
        }, function(e){
            throw "Something went wrong when fetching User data with message:" + e;
        });
    },

    userHasAccesstoken: function(next)
    {
        var user = this.global.accountModel.get('user_data')
          , dropbox = user.dropbox_data
          , userHastoken = true;
        //console.log(user);
        if ( !dropbox || dropbox == "" || dropbox.length == 0 || typeof dropbox === 'undefined' ) {
            userHastoken = false;
        }

        if (next) next.call(this, userHastoken);
    },

    setUserDropboxData: function(dropbox, next)
    {
        var user = this.global.accountModel.get('user_data');
        user.dropbox_data = dropbox;

        this.setAccountObject(JSON.stringify(user));

        if (next) next.call(this);
    },

    setAccountObject: function(object)
    {
        var obj = (IsJsonString(object)) ? JSON.parse(object) : object;

        //modify dropbox data from database
        if (
            typeof obj.dropbox_data === 'string' &&
            obj.dropbox_data !== "" &&
            IsJsonString(obj.dropbox_data)
        ){
            obj.dropbox_data = JSON.parse(obj.dropbox_data);
        }

        //apply storage data and expire in 1 day
        $.jStorage.set(this._acc_storage, JSON.stringify(obj), {TTL: (86400 * 1000)});

        this.global.accountModel.set('user_data', obj);
    },

    setSuccessCustomMessage: function(msg)
    {
        var el = $("#reportSuccessLabel");
        if ( !msg || msg == '' ) {
            el.empty();
        } else {
            el.html(msg);
        }
    },

    handleFirstIntegration: function(next)
    {
        var user = this.global.accountModel.get('user_data')
          , el = $("#reportSuccessLabel");
        
        this.setSuccessCustomMessage(false);
        
        if( user.first_integration == 1) {
            //update the text in indexpage
            this.setSuccessCustomMessage('<br/>Because this is your first integration.<br/>A sample report has been added to your Dropbox.');
            user.first_integration = 0;

            //save to model
            this.setAccountObject(JSON.stringify(user));

            //console.log("New updated user", this.global.accountModel.get('user_data'));
        }

        if (next) next();
    }
});