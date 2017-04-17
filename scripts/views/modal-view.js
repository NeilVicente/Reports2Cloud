var _e2D_ModalView = Backbone.View.extend({

	initialize: function()
	{

	},

    savingReportError: function()
    {
        var self = this;
        self.showGenericDialog(
            {
                'content': 'Something went wrong when<br/>saving the report.'
            },
            {
                'buttons': [
                    {
                        'id': '#modal-resave-report',
                        'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                        'text': 'Resave Report',
                        'handler' : function(id) {
                            $(id).bind('click', function(){
                                $(this).unbind('click');
                                self.closeErrorDialog();
                                self.global.pickerView.pick_el.ladda('destroy');
                                return false;
                            });
                        }
                    },
                ]
            }
        );
    },

    showGenericDialog: function(content_data, footer_data)
    {
        content_data['subcontent'] = (typeof content_data['subcontent'] === 'undefined') ? "Please try again" : 
                                             ((content_data['subcontent']) ? content_data['subcontent'] : '');

        this.global.mainpageView.modal.avgrund('show', {
            height: 200,
            closeByEscape: false,
            showClose: false,
            closeByDocument: false,
            content: renderTemplate("popin-error-content-template.html", content_data),
            footer: renderTemplate("popin-error-footer-template.html", footer_data)
        });

        if ( typeof footer_data !== 'undefined' )
        {
            //console.log('start footer handler');
            _.each(footer_data, function(val, key){
                if ( key === 'buttons' ) {
                    //console.log('button handler');
                    _.each(val, function(v, k){
                        //console.log('calling handlers');
                        v.handler(v.id);
                    });
                }
            });
        }

        //register global event
        Backbone.trigger('error:dialog');
    },

    closeErrorDialog: function(cb)
    {
        this.global.mainpageView.modal.avgrund('close',{onUnload:cb});
    }
});