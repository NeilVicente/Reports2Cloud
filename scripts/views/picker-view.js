/**
 * Contains all kinds of pickers, form picker and reports picker
 */
var _e2D_PickerView = Backbone.View.extend({
    el: '#picker-container',
    events:
    {
        'click .picker-btn': 'pick',
        'click .back-btn': 'back',
        'click #reloadFolders': 'reloadFolders'
    },

    initialize: function()
    {
        var self = this;

        //list type
        this.list_type = 'excel';

        //render template
        self.main_template = renderTemplate("picker-container-template.html");

        Backbone.on('call-picker-view', function(){
            //console.log('call-picker-view');

            self.once('onrender-main', function(){

                //wait app image to fully load to properly render slides
                //initialize reveal
                Reveal.initialize({
                    controls: false,
                    keyboard: false,
                    history: false,
                    touch: false,
                    overview: false,
                    transition: 'default', // default/cube/page/concave/zoom/linear/fade/none
                });

                //check if user has already dropbox access_token
                self.pick(null, $("#integrate_now-btn"));

                //trigger dropbox path view
                Backbone.trigger('call-dropboxfolders-view');
            });

            self.renderMain();
        });

        //errors view
        self.modalView = new _e2D_ModalView();
    },

    renderMain: function()
    {
        this.$el.html( this.main_template );
        this.trigger('onrender-main');
        return this;
    },

    /**
     * Show a certain slide using its id name
     */
    showSlide: function(selector)
    {
        if ( typeof selector !== 'string' ) {
            throw new Error("showSlide() first` parameter must be a string");
        }

        return Reveal.slide( this.getSlideIndex(selector) );
    },

    /**
     * Check if a certain slide is exist, using its selector
     */
    isSlideExist: function(target)
    {
        return $('#' + target).length > 0;
    },

    /**
     * Add a slide to the DOM
     * target must be a html file - template for the slide
     */
    addSlide: function(type, targetSelector, template_file, callback)
    {
        var self = this
          , template_dir = "templates/";

        if ( typeof type !== 'string' ) {
            throw new Error("addSlide() first parameter must be a string");
        }

        if ( typeof targetSelector !== 'string' ) {
            throw new Error("addSlide() second parameter must be a string");
        }

        //load the slide template and re-init reveal.js
        $.post(template_dir + template_file, function(e){
            //re-init slide
            if ( type == 'before' ) {
                $("#" + targetSelector).before(e);
            } else if ( type == 'after' ) {
                $("#" + targetSelector).after(e);
            }
            
            Reveal.slide();

            callback && callback(self,e);
        });
    },

    /**
     * Add a slide before another slide selector
     */
    addSlideBefore: function(targetSelector, template_file, callback)
    {
        this.addSlide('before', targetSelector, template_file, callback)
    },

    /**
     * Add a slide after another slide selector
     */
    addSlideAfter: function(targetSelector, template_file, callback)
    {
        this.addSlide('after', targetSelector, template_file, callback)
    },

    /**
     * Easily remove a slide and then re-init Reveal 
     */
    removeSlide: function(selector, callback)
    {
        if ( typeof selector !== 'string' ) {
            throw new Error("removeSlide() first parameter must be a string");
        }

        $('#' + selector).remove();
        Reveal.slide();

        callback && callback(this);
    },

    /**
     * Gets the slide index using its selector
     */
    getSlideIndex: function(selector)
    {
        return $('#'+selector, this.$el).index();
    },

    updateNavigator: function(slide_selector, name, target)
    {
        if ( typeof name !== 'string' ) {
            throw new Error("updateNavigator() second parameter must be a string");
        }

        if ( typeof target !== 'string' ) {
            throw new Error("updateNavigator() second parameter must be a string");
        }

        var el = $("#"+slide_selector+" a.back-btn span.navigator-name");

        if ( typeof name !== 'undefined' ) {
            el.text(name);
        }

        if ( typeof target !== 'undefined' ) {
            el.data('target', target);
        }
    },

    renderPreview: function(next)
    {
        var self = this
          , renderData = this.global.pickerModel.toJSON();

        //set user data
        renderData['user_data'] = this.global.accountModel.get('user_data');

        //console.log(renderData);

        //render template
        var previewTemplate = renderTemplate("finalize-data-template.html", renderData);
        $("#finalize-data").html(previewTemplate);

        if (next) next.call(self);
    },

    proceedReadingReports: function(next)
    {
        //check if the form has reports
        var self = this
          , reports_query = {
                'orderby': 'id',
                'direction': 'DESC',
                'filter': {
                    // 'list_type': self.list_type,
                    'status': 'ENABLED'
                }
            }
          , formID = this.global.pickerModel.get('form_data').id;

        try
        {
            self.global.formView.hasReports(formID, reports_query, function(e){
                if ( e === false )
                {
                    self.modalView.showGenericDialog(
                        {
                            'content': 'No Excel Reports found'
                        },
                        {
                            'buttons': [
                                {
                                    'id': '#modal-create-excel',
                                    'class': 'pure-button pure-button-medium pure-blue pure-upper h30',
                                    'text': 'Create Excel Report',
                                    'handler': function(id) {
                                        $(id).bind('click', function(){
                                            // $(this).unbind('click');
                                            // alert('Create report not yet available');
                                            self.createReportDialog.call(self, $(this), next);
                                            return false;
                                        });
                                    }
                                },
                                {
                                    'id': '#modal-repick-form',
                                    'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                                    'text': 'Repick Form',
                                    'handler': function(id) {
                                        $(id).bind('click', function(){
                                            $(this).unbind('click');
                                            self.modalView.closeErrorDialog();
                                            self.pick_el.ladda('destroy');
                                            return false;
                                        });
                                    }
                                },
                            ]
                        }
                    );

                    throw new Error("no excel reports found");
                }
                else
                {
                    //console.log("Reading submissions");
                    //get the submissions of this form
                    self.global.formView.getLastSubmission(formID, function(submission){
                        //merge some submission data to form_data
                        if ( submission !== false )
                        {
                            //console.log("Submission modified", submission);
                            var form_data = self.global.pickerModel.get('form_data')
                              , newform_data = array_merge(form_data, submission);

                            self.global.pickerModel.set('form_data', newform_data);
                        }

                        //callback if any
                        if (next) next.call(self, e);
                    });
                }
            });
        } catch(error)
        {
            self.modalView.showGenericDialog(
                {
                    'content': error
                },
                {
                    'buttons': [
                        {
                            'id': '#modal-close-form',
                            'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                            'text': 'Try Again',
                            'handler': function(id) {
                                $(id).bind('click', function(){
                                    $(this).unbind('click');
                                    self.modalView.closeErrorDialog();
                                    self.pick_el.ladda('destroy');
                                    return false;
                                });
                            }
                        }
                    ]
                }
            );

            throw new Error(error);
        }
    },

    /**
     * Pick forms and return the selected form ID
     */
    formPicker: function(next, onclose)
    {
        var self = this;
        JF.FormPicker({
            title: 'Pick your Form where reports will come from',
            showPreviewLink: true,
            sort: 'created_at',
            sortType: 'DESC',
            multiSelect: false,
            inifinite_scroll: true,
            search: true,
            onSelect: function(r) {
                var selectedFormObj = r[0]
                  , formID = selectedFormObj.id;

                //if there is a previous data, merge it
                var _prevData = self.global.pickerModel.get('form_data');
                if ( _prevData ) {
                    selectedFormObj = array_merge(_prevData, selectedFormObj);
                }

                self.global.pickerModel.set('form_data', selectedFormObj);

                console.log(self.global.pickerModel.get('form_data'));

                self.proceedReadingReports(next);
            },
            onClose: onclose
        });
    },

    getAllFormQuestions: function(formID, next)
    {
        var ignored_types = [
            "control_head", 
            "control_button", 
            "control_pagebreak", 
            "control_collapse", 
            "control_text"
        ];

        JF.getFormQuestions(formID, function(response){
            console.log(response);
            var questionIds = [];
            _.each(response, function(val, key){
                if ( ignored_types.indexOf( val.type ) < 0 ) {
                    questionIds.push(val.qid);
                }
            });

            if (next) next.call(self, questionIds);
        });
    },

    createReportDialog: function(el, next)
    {

        var reportTitle = prompt("Please enter your Report name:\nNote: All questions in your form will be included.", "");
        var confirmVal = confirm("Are you sure you want to create [" + reportTitle + '] report?');

        //if the user doesnt want to, back to normal
        if( !confirmVal ) {
            return false;
        }

        //disable the buttons
        el.attr('disabled', true);
        el.siblings('#modal-repick-form').attr('disabled', true);

        //modify the cont text
        var self = this
          , textCont = el.parents('.avgrund-popin')
                         .find('.avgrund-content')
                         .find('.text-content');

        textCont.html('Creating report<p style="margin: 0px;padding-top: 5px;font-size: 18px;">please wait...</p>');

        var formID = this.global.pickerModel.get('form_data').id
          , fields = ['ip','dt']
          , report = {};

        self.getAllFormQuestions(formID, function(questions){
            fields = array_merge(fields, questions);
            report = {
                title: reportTitle,
                list_type: self.list_type,
                fields: fields.join(',')
            };

            JF.createReport(formID, report, function success(response){
                self.modalView.closeErrorDialog(function(){
                    self.modalView.showGenericDialog(
                        {
                            'content': response.title + '<br/>Successfully created!<br/>',
                            'subcontent': '<a id="'+response.id+'" href="'+response.url+'" target="_blank">Preview(open new tab)</a>'
                        },
                        {
                            'buttons': [
                                {
                                    'id': '#modal-close-form',
                                    'class': 'pure-button pure-button-medium pure-blue pure-upper h30',
                                    'text': 'Continue',
                                    'handler': function(id) {
                                        $(id).bind('click', function(){
                                            $(this).unbind('click');
                                            self.modalView.closeErrorDialog();

                                            //re-read reports
                                            self.proceedReadingReports(next);
                                            return false;
                                        });
                                    }
                                }
                            ]
                        }
                    );
                });
            }, function error(){
                console.error("Error occured");
            });

        });

        return false;
    },

    /**
     * Pick reports and return the selected report ID
     */
    reportPicker: function(formID, next, onclose)
    {
        var self = this;
        JF.ReportPicker(formID, {
            //used the data response from checking reports of a form
            title: 'Pick your Report that will be sent to Dropbox',
            offset: 0,
            limit: 5,
            sort: 'created_at',
            sortType: 'ASC',
            showPreviewLink: true,
            initial_data: self.global.formView.form_reports,
            multiSelect: false,
            onSelect: function(r) {
                var selectedReportObj = r[0]
                  , reportID = selectedReportObj.id;

                //modify report settings which is string object
                if (
                    typeof selectedReportObj.settings === 'string' &&
                    selectedReportObj.settings !== "" &&
                    IsJsonString(selectedReportObj.settings)
                ){
                    selectedReportObj.settings = JSON.parse(selectedReportObj.settings);
                }

                //if there is a previous data, merge it
                var _prevData = self.global.pickerModel.get('report_data');
                if ( _prevData ) {
                    selectedReportObj = array_merge(_prevData, selectedReportObj);
                }

                self.global.pickerModel.set('report_data', selectedReportObj);

                self.global.reportView.alreadyInQueue(selectedReportObj, function(e){
                    if ( e !== false )
                    {
                        self.modalView.showGenericDialog(
                            {
                                'content': 'Selected Report<br/>is already added to List'
                            },
                            {
                                'buttons': [
                                    {
                                        'id': '#modal-repick-report',
                                        'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                                        'text': 'Repick Report',
                                        'handler': function(id) {
                                            $(id).bind('click', function(){
                                                $(this).unbind('click');
                                                self.modalView.closeErrorDialog();
                                                self.pick_el.ladda('destroy');
                                                return false;
                                            });
                                        }
                                    },
                                ]
                            }
                        );

                        throw new Error("Selected report is already added to list");
                    }
                    else
                    {
                        //callback if any
                        if (next) next.call(self, reportID);
                    }
                });
            },
            onClose: onclose
        });
    },

    /**
     * Connect with Dropbox and return the access_token
     */
    dropbox_auth_finish: function(user)
    {
        //trigger event
        var val = {
            user: JSON.parse(user)
        };
        Backbone.trigger('dropbox:auth:finished', val);
    },

    dropboxAuth: function(next)
    {
        var self = this
          , HTTP_URL = self.global.mainpageView.getHTTPURL()
          , window_options = 'width=620,height=525,status=0,location=1,toolbar=0,scrollbars=1,resizable=1';

        // listen to an event
        Backbone.once('dropbox:auth:finished', function(result){
            if ( result )
            {
                //set dropbox token to account model
                self.global.accountView.setUserDropboxData(result);

                //run the folderview to update it, when dropbox token is updated
                self.global.dropboxfoldersView.refresh();

                if (next) next.call(self, result);
            } else {
                throw new Error("Error occured when connecting Dropbox. Please try again");
            }
        });

        var _popup = new Popup(HTTP_URL + "api/", 'view', window_options);

        //if closed accidentally, reset the loading button
        var windowTimer = window.setInterval(function() {
            if (_popup.popup && _popup.popup.closed !== false) {
                window.clearInterval(windowTimer);
                //reset the button
                self.pick_el.ladda('destroy');
            }
        }, 500);
    },

    reveal: function(type)
    {
        if ( typeof Reveal === 'undefined' ) {
            throw new Error("Reveal library is missing");
        }

        if ( type === 'next' ) {
            Reveal.right();
        } else if ( type === 'prev' ) {
            Reveal.left();
        } else {
            //expect a slide number
            Reveal.slide(type);
        }
    },

    delay: function(ms,cb)
    {
        var self = this;
        setTimeout(function(){
            cb.call(self);
        }, ms);
    },

    back: function(e)
    {
        var el = $(e.target)
          , target = el.data('target');

        this.showSlide(target);

        return false;
    },

    pick: function(e, customEl)
    {
        var self = this
          , el = (customEl) ? $(customEl) : $(e.target)
          , type = el.attr('data-pickertype');
          //console.log(el.attr('data-ladda'));

        //global
        self.pick_el = el;

        if ( typeof el.attr('data-ladda') === 'undefined' && el.hasClass('ladda-button') )
        {
            el.ladda( 'start' );
        }

        switch(type)
        {
            case 'proceed':
                //console.log("Start init");
                this.delay(500, function(){
                    //check if user has already dropbox access_token
                    self.global.accountView.userHasAccesstoken(function(e){
                        el.ladda('destroy');
                        if ( e === true ) {
                            Reveal.addEventListener( 'slidechanged', function( event ) {
                                if ( event.indexh == self.getSlideIndex('select-form') ) {
                                    self.delay(500, function(){
                                        $(".slides", self.$el).addClass('vs');
                                    });
                                }
                            });
                            self.showSlide('select-form');
                        } else {
                            self.showSlide('connect-dropbox');
                            $(".slides", self.$el).addClass('vs');
                        }
                    });
                });
            break;
            case 'dropbox':
                //console.log("Dropbox init");
                this.dropboxAuth(function(e){
                    if ( e !== false ) {
                        //console.log(self.global.accountModel.get('user_data'));
                        this.delay(1000, function(){
                            el.ladda('destroy');
                            this.showSlide('select-form');

                            //register global event
                            Backbone.trigger('picked:dropbox');
                        });
                    } else {
                        el.ladda('destroy');
                    }
                });
            break;
            case 'form':
                //console.log("Form picker init");
                this.formPicker(function(){
                    //console.log("Newly merge form", self.global.pickerModel.get('form_data'));
                    el.ladda('destroy');
                    this.showSlide('select-report');

                    //register global event
                    Backbone.trigger('picked:form');
                }, function(){
                    el.ladda('destroy');
                });
            break;
            case 'report':
                //console.log("Report picker init");
                //get formID
                var formID = this.global.pickerModel.get('form_data').id;console.log(formID);
                this.reportPicker(formID, function(){

                    //set the default filename using the report title
                    self.global.filesView.setFilenameDefault(self.global.pickerModel.get('report_data').title);

                    //lets do something and ask the user if the report is password protected
                    if ( self.global.pickerModel.get('report_data').isProtected === true )
                    {
                        //change the navigator name and target for the next slide
                        self.updateNavigator('select-upload-folder', 'Repick Password', 'set-password');

                        self.delay(500, function(){
                            //remove loading
                            el.ladda('destroy');

                            //show select folder slide
                            this.showSlide('set-password');
                        });
                    }
                    else
                    {
                        //change the navigator name and target for the next slide
                        self.updateNavigator('select-upload-folder', 'Repick Report', 'select-report');

                        self.delay(500, function(){
                            //remove loading
                            el.ladda('destroy');

                            //show select folder slide
                            this.showSlide('select-upload-folder');
                        });
                    }
                }, function(){
                    el.ladda('destroy');
                });
            break;
            case 'confirmpassword':
                var _password = self.global.reportView.getPasswordFromElement();console.log('password', _password);

                //if password is empty do something
                if ( _password == "" || !_password )
                {
                    self.modalView.showGenericDialog(
                        {
                            'content': 'Sorry but in order to proceed<br/>we need that password.',
                            'subcontent': false
                        },
                        {
                            'buttons': [
                                {
                                    'id': '#modal-ask-password',
                                    'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                                    'text': 'Re-type password',
                                    'handler': function(id) {
                                        $(id).bind('click', function(){
                                            $(this).unbind('click');
                                            self.modalView.closeErrorDialog();
                                            self.pick_el.ladda('destroy');
                                            return false;
                                        });
                                    }
                                },
                            ]
                        }
                    );
                }
                else
                {
                    //set password to reportView
                    self.global.reportView.setPassword(_password);

                    self.delay(500, function(){
                        //remove loading
                        el.ladda('destroy');

                        //show select folder slide
                        this.showSlide('select-upload-folder');
                    });
                }
            break;
            case 'folderpath':
                self.global.dropboxfoldersView.getSelectedPath(function(path){
                    //console.log('Got path', path);

                    if ( !path )
                    {
                        self.modalView.showGenericDialog(
                            {
                                'content': 'Folder path is missing<br/>please select a folder'
                            },
                            {
                                'buttons': [
                                    {
                                        'id': '#modal-close-form',
                                        'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                                        'text': 'Try Again',
                                        'handler': function(id) {
                                            $(id).bind('click', function(){
                                                $(this).unbind('click');
                                                self.modalView.closeErrorDialog();
                                                self.pick_el.ladda('destroy');
                                                return false;
                                            });
                                        }
                                    }
                                ]
                            }
                        );

                        return false;
                    }

                    var report_data = self.global.pickerModel.get('report_data');
                    report_data['filepath'] = path;
                    report_data['filename'] = self.global.filesView.getFilename();
                    report_data['report_password'] = self.global.reportView.getPassword();
                    self.global.pickerModel.set('report_data', report_data);
                    //console.log('Successfully merge to report object', self.global.pickerModel.get('report_data'));
                    self.renderPreview(function(){
                        this.delay(500, function(){
                            el.ladda('destroy');
                            this.showSlide('confirm-all');
                        });
                    });
                });
            break;
            case 'confirm':

                //event when a report is successfully save/update, event can be found in report-view.js
                Backbone.on('report:completed', function(){
                    console.log('completed');
                    el.ladda('destroy');
                });

                //if edit mode
                if ( self.global.mainpageView.isEditMode() )
                {
                    self.global.reportView.updateReport(function(){
                        //if edit mode - disable it now
                        console.log('removed edit mode');
                        self.global.mainpageView.removeEditMode();
                    });
                }
                else
                {
                    self.global.reportView.saveReport();
                }
            break;
            case 'back-main':
                //back to main slide
                self.showSlide('select-form');
            break;
            default:
                //silent
            break;
        }
    },

    reloadFolders: function(e)
    {
        this.global.dropboxfoldersView.refresh();
    }
});