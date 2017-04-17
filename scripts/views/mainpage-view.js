var _e2D_MainpageView = Backbone.View.extend({
    el: "#main_page_container",

    events: {
        'click #integrate_now-btn' : 'integrateNow'
    },

    initialize: function()
    {
        var self = this;

        //flag when the report is in edit mode
        this.isReportEditing = false;

        Backbone.on('call-mainpage-view', function(){
            //console.log('mainpage-view call');
        });
    },

    integrateNow: function()
    {
        var self = this;
        //console.log("Start up!!!");
        $('#application_landing', this.$el).slideUp('slow', function(){
            self.start();
        });
    },

    start: function()
    {
        var self = this;

        try
        {
            //initialize modal object
            self.modal = $("#loading-modal");

            //handle user first to identify them
            //show modal for the first time
            this.global.accountView.checkJFUserData(function(u_data){
                //console.log("u_data", u_data);
                if ( u_data ) {
                    //data from storage is present
                    //set data to model - to be accessed later
                    this.setAccountObject(u_data);

                    //login user to session
                    this.handleUserFromBackground(u_data);

                    //trigger picker views
                    Backbone.trigger('call-picker-view');

                    //trigger sidebar view
                    Backbone.trigger('call-sidebar-view');

                } else {
                    //data from storage is not present
                    //show modal, and then register and verify user
                    self.modal.avgrund('show', {
                        height: 200,
                        closeByEscape: false,
                        showClose: false,
                        closeByDocument: false,
                        content: '<div class="pop-up-loading-state"><div id="pop-up-loading-text">Verifying user<br/>please wait<span>.</span><span>.</span><span>.</span></div></div>'
                    });

                    this.handleJFUser(function(response){
                        if ( response.status == 'success' )
                        {
                            //close modal
                            self.modal.avgrund('close');

                            //trigger picker views
                            Backbone.trigger('call-picker-view');

                            //trigger sidebar view
                            Backbone.trigger('call-sidebar-view');
                        }
                        else
                        {
                            self.modal.avgrund('show', {content: 'Something went wrong when verifying account, please reload the page'});
                        }

                        //console.log("Account object", self.global.accountModel.toJSON());
                    });
                }
            });

        } catch (e) {
            console.error(e.message);
        }
    },

    getHTTPURL: function()
    {
        return String(window.location.protocol + "//" + window.location.host + window.location.pathname);
    },

    isEditMode: function()
    {
        return this.isReportEditing;
    },

    setEditMode: function()
    {
        this.isReportEditing = true;
    },

    removeEditMode: function()
    {
        this.isReportEditing = false;
    }

});