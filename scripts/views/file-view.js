/**
 * Contains all the dropbox files
 */
var _e2D_FilesView = Backbone.View.extend({
    el: ".report-file-modification",
    initialize: function()
    {
        var self = this;
        
        self.modifiedFilename = "";
    },

    getFilename: function()
    {
        return $("#filename", $(this.$el.selector)).val();
    },

    setFilenameDefault: function(value)
    {
        $("#filename", $(this.$el.selector)).val(value);
    }
});