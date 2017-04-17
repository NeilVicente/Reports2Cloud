var _e2D_FormView = Backbone.View.extend({

    initialize: function()
    {
        this.form_reports = false;
    },

    hasReports: function(formID, query, callback)
    {
        var self = this;

        if ( typeof query === 'function' ) {
            callback = query;
            query = null;
        }

        self.global.reportView.getJFFormReports(formID, query, function(resp){
            var response = false;
            if ( resp ) {
                self.form_reports = resp;
                response = true;
            }
            callback && callback.call(self, response);
        });
    },

    getSubmissions: function(formID, query, callback)
    {
        var self = this;

        if ( typeof query === 'function' ) {
            callback = query;
            query = null;
        }

        JF.getFormSubmissions(formID, query, function success(resp){
            //console.log('Submissions', resp);
            var response = false;
            if ( resp.length > 0 ) {
                response = resp;
            }

            if (callback) callback.call(self, response);
        }, function error(){
            throw "Something went wrong when fetching submissions from form ID: " + formID;
        });
    },

    getLastSubmission: function(formID, callback)
    {
        //get the latest submission, so order it by created_at
        this.getSubmissions(formID, {
            'limit': 1,
            'orderby': 'created_at'
        }, function(submissions){
            console.log('form submissions', submissions);
            var submission = {};

            if ( submissions === false ) {
                submission = false;
                throw "No submission data from form ID: " + formID;
            }

            //loop and only get the necessary data
            _.each(submissions, function(value,key){
                submission['last_submission_id'] = (value.id == '#SampleSubmissionID') ? false : value.id;
                submission['last_submission_created_at'] = (value.id == '#SampleSubmissionID') ? false : value.created_at;
            });

            if (callback) callback.call(self, submission);
        });
    }
});