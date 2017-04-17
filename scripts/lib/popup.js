var Popup = function(url, title, options)
{
    this.url = url;
    this.title = title;
    this.options = options;
    this.popup = window.open(this.url, this.title, this.options);
    this.errorMsg = "Please disable pop-up blocker in your browser to complete integration.";

    this.testAndOpenPopup = function (){
        if (!this.popup || this.popup.closed || typeof this.popup == 'undefined' || typeof this.popup.closed == 'undefined') {
            this.alertUser();
        }
    };

    this.alertUser = function (){
       alert(this.errorMsg);
    };

    var _this = this;

    setTimeout(_this.testAndOpenPopup.call(_this), 1000);
};