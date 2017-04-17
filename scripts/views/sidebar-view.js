var _e2D_SidebarView = Backbone.View.extend({

    el: ".sidebar-list",

    events: {
        'click .remove-list' : 'removeReport',
        'click .edit-list' : 'editReport',
        'click #logout' : 'logout',
        'mouseover .sidebar-actions-icons' : 'highlightSibling',
        'mouseout .sidebar-actions-icons' : 'highlightSibling'
    },

    initialize: function()
    {
        var self = this;
        Backbone.on('call-sidebar-view', function(){
            self.start();
        });

        self.modalView = new _e2D_ModalView();
    },

    start: function()
    {
        this.on('onrender', function(){
            this.initializeMenymenu();

            $(".reports-list-cont").perfectScrollbar();
        });

        this.render();
    },

    redraw: function( reportsOnly )
    {
        this.render( reportsOnly || false );
    },

    render: function( reportsOnly )
    {
        var self = this
          , reportsOnly = reportsOnly || false;

        self.global.reportView.fetchReports(function(response){
            if ( response.code == 200 )
            {
                var reports = {'user_reports': response.reports || false }
                  , user = {'user_data' : self.global.accountModel.get('user_data') || false};

                  //render account 
                if ( !reportsOnly ) {
                    $("#account-details", self.$el).html( renderTemplate("account-details-template.html", user) );   
                }

                $("#reports-list", self.$el).html( renderTemplate("report-list-template.html", reports) );

                self.trigger('onrender');
            }
            else
            {
                console.error(response);
            }
        });
    },

    initializeMenymenu: function()
    {
        if ( this.meny ) return false;

        this.meny = Meny.create({
            menuElement: $(".meny-menu")[0],
            contentsElement: $(".meny-contents")[0],
            showOverlay: false,
            position:'right',
            height: 200,
            width: 320,
            threshold: 40,
            gradient: 'none',
            mouse: true,
            touch: true
        });

        $('body').css('background-color', $( '.meny-menu' ).css('background-color'));
    },

    highlightSibling: function(e)
    {
        var el = $(e.target, this.$el);
        el.toggleClass('spinSmall spinSmallDefault');
        el.siblings('a').toggleClass('a-hover');
    },

    logout: function(e)
    {
        var self = this;

        //make a request to logout
        $.ajax({
            url: 'server.php',
            method: 'POST',
            data: {action: 'logout'},
            success: function(response)
            {
                if ( response.success ) {
                    self.global.accountView.removeJFUserData(function(){
                        window.location.href = window.base;
                    });
                }
            },
            error: function(errors)
            {
                //console.log(errors);
                throw new Error("Something went wrong when user is being logout.  " + errors);
            }
        });

        return false;
    },

    editReport: function(e)
    {
        var self = this
          , el = $(e.target, this.$el)
          , rid = el.attr('data-rid').split('_')[0]
          , userID = self.global.accountModel.get('user_data').id
          , folderView = self.global.dropboxfoldersView;

        //flag to edit mode
        self.global.mainpageView.setEditMode();

        //put some nifty animation
        el.addClass('spinInfinite');

        if ( folderView.hasError() )
        {
            folderView.flagError(true);
        }
        else
        {
            //start fetching dropbox folders if not yet fetched
            if ( !folderView.isFoldersFetched() )
            {
                console.log('start folder fetching');

                //empty the container and show a loading message
                $(folderView.$el.selector).empty();
                folderView.showLoading();

                folderView.start();
            }
            
            //get the report data to edit
            $.getJSON('server.php?action=getSingleReport&rid=' + rid + '&withForm=1', function(data){

                console.log(data);
                var report = data.reports;

                Backbone.once("start:getform", function(){
                    JF.getForm(report.jotform_formid, function(formData){
                        console.log('formdata', formData);
                        //build new form_data
                        formData['last_submission_created_at'] = report.last_submission_created_at;
                        formData['last_submission_id'] = report.last_submission_id;

                        //following data is a requirement when updating
                        formData['local_id'] = report.fid;
                        formData['local_uid'] = report.uid;

                        self.global.pickerModel.set('form_data', formData);

                        //build new report_data
                        var newreport_data = {
                            "id": report.jotform_rid,
                            "title": report.jotform_title,
                            "url": report.jotform_url,
                            "filepath": report.filepath,
                            "filename": report.filename
                        };

                        //following data is a requirement when updating
                        newreport_data['local_id'] = report.id;
                        newreport_data['local_fid'] = report.fid;
                        newreport_data['local_uid'] = report.uid;

                        if ( report.isProtected ) {
                            newreport_data['isProtected'] = true;
                        }

                        //now update report_data model
                        self.global.pickerModel.set('report_data', newreport_data);

                        //make a request to edit the report
                        self.global.pickerView.showSlide('select-upload-folder');

                        //modify filename input
                        self.global.filesView.setFilenameDefault(report.filename);

                        //set the default password if protected
                        if ( report.isProtected ) {
                            self.global.reportView.setPassword(':default:');
                        }

                        var _path = report.filepath.split('/')
                          , prev_path = "";

                        //remove first entry - empty
                        _path.splice(0, 1);

                        //simulate clicks to select folders
                        var _cInc = 0
                          , _stopped = false;

                        var clickReportPath = function()
                        {
                            //don't do any actions anymore when stopped
                            if ( _stopped ) return;

                            if ( _path[_cInc] != "" || typeof _path[_cInc] !== 'undefined' )
                            {
                                var el = $(folderView.$el.selector + ' li a[rel="/'+ prev_path + _path[_cInc]+'"]');

                                //check if end of the path
                                if ( _cInc == (_path.length - 1) )
                                {
                                    el.click(function(){
                                        //stopped clicking
                                        _stopped = true;
                                        
                                        var offset = $(el).offset().top - $(folderView.$el.selector).offset().top;
                                        $('.report-path-inner').animate({'scrollTop': offset - ($('.report-path-inner').height() /2)});
                                    });

                                    setTimeout(function(){
                                        el.click();
                                    }, 500);
                                }
                                else
                                {
                                    el.dblclick(function(){
                                        //don't scroll any more if clicking stopped
                                        if ( _stopped ) return false;

                                        if ( $(this).siblings('.tree-icons').hasClass('expanded') ) {
                                            Backbone.trigger('complete:getfolders');
                                        }

                                        var offset = $(el).offset().top - $(folderView.$el.selector).offset().top;
                                        $('.report-path-inner').animate({'scrollTop': offset - ($('.report-path-inner').height() /2)});
                                    });

                                    el.dblclick();
                                }
                            } else {
                                _cInc++;
                                clickReportPath();
                            }
                        };

                        //register event
                        Backbone.on('complete:getfolders', function(){
                            if ( typeof _path[_cInc] !== 'undefined' && !folderView.hasError() )
                            {
                                prev_path += String(_path[_cInc] + '/');console.log('path', prev_path);
                                _cInc++;
                                clickReportPath();
                            }
                        });

                        //wait for the element to be visible before clicking paths
                        folderView.waitUntilExists($(folderView.$el.selector), function(el){
                            clickReportPath();
                        });

                        console.log(self.global.pickerModel.get('form_data'));
                        console.log(self.global.pickerModel.get('report_data'));
                        console.log(self.global.accountModel.get('user_data').dropbox_data);

                        el.removeClass('spinInfinite');
                    });
                });

                //when editing, if there is a password
                console.log(report);
                if ( report.isProtected )
                {
                    self.global.pickerView.updateNavigator('select-upload-folder', 'Repick Password', 'set-password');
                }
                else
                {
                    self.global.pickerView.updateNavigator('select-upload-folder', 'Repick Report', 'select-report');
                }

                Backbone.trigger("start:getform");
            });
        }
    },

    removeReport: function(e)
    {
        //ask user to confirm
        var self = this
          , confirmVal = false;

        var _closeMenymenu = function()
        {
            self.modalView.closeErrorDialog();
            self.meny.stayOpen(false);
        };

        self.once('report:removed:confirmation', function(confirmVal){
            //if the user doesnt want to, back to normal
            if( !confirmVal ) {
                _closeMenymenu();
                return false;
            }

            var el = $(e.target, self.$el)
              , rid = el.attr('data-rid').split('_')[1]
              , userID = self.global.accountModel.get('user_data').id;

            //put some nifty animation
            el.addClass('spinInfinite');

            //make a request to delete
            $.ajax({
                url: 'server.php',
                method: 'POST',
                data: {
                    action: 'removeReport',
                    userID: userID,
                    reportID: rid
                },
                success: function(response)
                {
                    //console.log(response);

                    //when successfully deleted, redraw sidebar content
                    el.removeClass('spinInfinite');
                    self.redraw(true);
                },
                error: function(errors)
                {
                    //console.log(errors);
                    throw new Error("Something went wrong when removing report with message " + errors);
                },
                complete: function()
                {
                    _closeMenymenu();
                }
            });
        });

        self.meny.stayOpen();
        self.modalView.showGenericDialog(
            {
                'content': 'Are you sure you want<br>to delete this report?',
                'subcontent': 'This can\'t be undone'
            },
            {
                'buttons': [
                    {
                        'id': '#modal-confirm-yes',
                        'class': 'pure-button pure-button-medium pure-blue pure-upper h30',
                        'text': 'Yes',
                        'handler': function(id) {
                            $(id).bind('click', function(){
                                $(this).unbind('click');
                                self.trigger('report:removed:confirmation', true);
                                return false;
                            });
                        }
                    },
                    {
                        'id': '#modal-confirm-no',
                        'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                        'text': 'No',
                        'handler': function(id) {
                            $(id).bind('click', function(){
                                $(this).unbind('click');
                                self.trigger('report:removed:confirmation', false);
                                return false;
                            });
                        }
                    }
                ]
            }
        );
    }
}); 