var _e2D_ReportView = Backbone.View.extend({

    initialize: function()
    {
        this.modalView = new _e2D_ModalView();

        this.report_password = false;

        this.valid_list_types = ['excel', 'csv'];
    },

    alreadyInQueue: function(report, callback)
    {
        var self = this;

        $.ajax({
            url: 'server.php',
            method: 'POST',
            data: {
                action: 'checkReport',
                report: JSON.stringify(report)
            },
            success: function(response)
            {
                //console.log(response);
                if(callback) callback.call(self, response.isExisted);
            },
            error: function(errors)
            {
                //console.log(errors);
                throw new Error("Something went wrong when verifying report with an ID of " + report.id);
            }
        });
    },

    getJFFormReports: function(formID, query, callback)
    {
        var self = this;
        JF.getFormReports(formID, query, function success(resp) {
            console.log(resp);
            var response = false;
            if ( resp.length > 0 ) {
                response = self.filterLists(resp);
            }

            if (callback) callback.call(self, response);
        }, function error(){
            throw "Something went wrong when fetching Reports";
        });
    },

    filterLists: function(lists)
    {
        var self = this;
        _.each(lists, function(val, key){
            if ( !_.contains(self.valid_list_types, val['list_type']) ) {
                lists.splice(key, 1);
            }
        });
        console.log('new lists', lists);
        return lists;
    },

    fetchReports: function(next)
    {
        var self = this;

        $.ajax({
            url: 'server.php',
            method: 'POST',
            data: {
                action: 'getReports'
            },
            success: function(response)
            {
                //console.log("Got reports", response);
                if(next) next.call(self, response);
            },
            error: function(errors)
            {
                //console.log(errors);
                throw new Error("Something went wrong when fetching all user reports");
            }
        });
    },

    /**
     * Responsible on saving an updating of report
     */
    ajaxReport: function(dataParam, next)
    {
        var self = this;

        //send to server
        $.ajax({
            url: 'server.php',
            method: 'POST',
            data: dataParam,
            success: function(response)
            {
                console.log("Reports Saved", response);
                self.global.pickerView.delay(1000, function(){
                    //remove sensitive data
                    this.global.pickerModel.clear();

                    //update the sidebar view
                    this.global.sidebarView.redraw();

                    //update the account
                    this.global.accountView.handleFirstIntegration(function(){
                        self.global.pickerView.reveal('next');
                    });
                });
            },
            error: function(error)
            {
                console.log(error);
                self.modalView.savingReportError();
                var msg = JSON.parse(error.responseText).message;
                throw new Error("Something went wrong saving the report with message: " + msg);
            },
            complete: function()
            {
                //trigger and callback
                Backbone.trigger('report:completed');
                next && next();
            }
        });
    },

    /**
     * Save a new report to db
     */
    saveReport: function(next)
    {
        var self = this;

        var form_data = this.global.pickerModel.get('form_data')
          , report_data = this.global.pickerModel.get('report_data');

        if ( !form_data || !report_data )
        {
            self.modalView.showGenericDialog(
                {
                    'content': 'We are unable to save the report'
                },
                {
                    'buttons': [
                        {
                            'id': '#modal-retry',
                            'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                            'text': 'Try Again',
                            'handler': function(id) {
                                $(id).bind('click', function(){
                                    $(this).unbind('click');
                                    self.modalView.closeErrorDialog();
                                    self.global.pickerView.pick_el.ladda('destroy');
                                    return false;
                                });
                            }
                        }
                    ]
                }
            );
        }

        var dataParam = {
            action: 'saveReport',
            form: form_data ? JSON.stringify(form_data) : '',
            report: report_data ? JSON.stringify(report_data) : ''
        };
        console.log("Finalizing report", dataParam);

        //send to server
        this.ajaxReport(dataParam, next);
    },

    /**
     * Update a report to db
     */
    updateReport: function(next)
    {
        var self = this;
        var dataParam = {
            action: 'updateReport',
            form: JSON.stringify(this.global.pickerModel.get('form_data')),
            report: JSON.stringify(this.global.pickerModel.get('report_data'))
        };
        console.log("Finalizing report", dataParam);

        //send to server
        this.ajaxReport(dataParam, next);
    },

    /** 
     * Returns the reports password
     */
    getPassword: function()
    {
        var report = this.global.pickerModel.get('report_data');
        console.log('get password', report);
        return (report.isProtected) ? this.report_password : false;
    },

    /**
     * Set the report password if any
     */
    setPassword: function(password)
    {
        var report = this.global.pickerModel.get('report_data');
        console.log('set password', report);
        this.report_password = (report.isProtected) ? password : false;
    },

    getPasswordFromElement: function()
    {
        return $("#report_password", $('#set-password')).val();
    },

    emptyPasswordElemet: function()
    {
        $("#report_password", $('#set-password')).val('');
    }

});