/**
 * Contains all the dropbox folders
 */
var _e2D_DropboxFoldersView = Backbone.View.extend({
    el: "#dropbox-folders",
    initialize: function()
    {
        var self = this;
        
        self.settings = {
            loadMessage: 'Loading...',
            root: '/',
            expandSpeed: 500,
            collapseSpeed: 500,
            expandEasing: null,
            collapseEasing: null,
            multiFolder: true
        };

        self.selectedPath = "";
        self.errored = false;
        self.foldersFetched = false;
        self.requestOnProgress = false;

        Backbone.on('call-dropboxfolders-view', function(){
            //console.log('path-view call');

            //mark folders fetch on first request
            Backbone.once("complete:getfolders", function(){
                //now we know that folders are fetched
                //this will prevent double request when editing 
                self.flagFoldersFetched();

                self.hideLoading();
            });

            self.start();
        });

        //event after report picked, we'll check if dropbox folder has no error
        Backbone.on('picked:report', function(){
            if ( self.hasError() )
            {
                self.flagError(true);
            }
        });

        //errors view
        self.modalView = new _e2D_ModalView();
    },

    bindFolderTree: function(el)
    {
        var self = this
          , o = self.settings;

        $('li a', el).bind('dblclick', function() {
            var sibling_div = $(this).siblings('.tree-icons');

            if ( sibling_div.hasClass('expanded') ) {
                return false;
            }

            if( sibling_div.hasClass('directory') )
            {
                var li_parent = $(this).parent();
                if( sibling_div.hasClass('collapsed') )
                {
                    // var sibling_ul = $(this).siblings('ul.jFileTree');

                    // Expand
                    // if( !o.multiFolder )
                    // {
                    //     if ( sibling_ul )
                    //     {
                    //         //slide up the child ul
                    //         sibling_ul.slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });

                    //         //the li parent of a tag

                    //         //find the directory icon on li parent and toggle its class
                    //         $('.directory', li_parent).removeClass('expanded').addClass('collapsed');
                    //     }
                    // }

                    // cleanup any ul first
                    $('ul', li_parent).remove();

                    var dir = $(this).attr('rel');
                    self.displayFolderTree( li_parent, dir, function(){
                        //expand now showing the results
                        self.expandFolder(li_parent);

                        self.bindFolderTree(li_parent);
                    });
                } else {
                    // Collapse
                    $('ul', li_parent).slideUp('fast');
                    self.collapseFolder(li_parent);
                }
            }

            return false;
        });

        $('li a', el).bind('click', function() {
            $('a', $(".jFileTree")).removeClass('selected');
            $(this).toggleClass('selected');
            self.selectedPath = $(this).attr('rel');
            return false;
        });

        // Prevent A from triggering the # on non-click events
        // if( o.folderEvent.toLowerCase != 'click' ) $(t).find('LI A').bind('click', function() { return false; });
    },

    getSelectedPath: function(next)
    {
        if(next) next(this.selectedPath);
    },

    expandFolder: function(el)
    {
        $(".tree-icons:eq(0)", el).removeClass('collapsed').addClass('expanded');
    },

    collapseFolder: function(el)
    {
        $(".tree-icons:eq(0)", el).removeClass('expanded').addClass('collapsed');
    },

    showError: function(el, msg)
    {
        var self = this;
        $('.errors', el).text(msg).fadeIn('slow', function(){
            var $this = this;
            self.collapseFolder(el);
            setTimeout(function(){
                $($this).fadeOut('slow').empty();
            }, 2000);
        });
    },

    parseContent: function(folders)
    {
        return renderTemplate( 'dropboxFolderView.html', {'folders': folders} )
    },

    displayFolderTree: function(el, dirValue, next)
    {
        var self = this
          , o = self.settings
          , hasIcons = false;

        if ( $(".tree-icons", el) )
        {
            hasIcons = true;
            $(".tree-icons", el).addClass('wait');
        }

        // //empty the dropbox folders container
        // if ( !self.isFoldersFetched() )
        // {
        //     $(this.$el.selector).empty();
        // }

        //if there's an existing request abort it right away
        if ( this._request )
        {
            this._request.abort();
        }

        //call the server with 30 seconds timeout
        this._request = $.ajax({
            url: 'server.php',
            method: 'POST',
            timeout: 30000,
            data: {
                action: 'getDropboxFolders',
                dir: dirValue
            },
            success: function(response)
            {
                console.log('debug berkay');
                console.log(response);

                if ( hasIcons ) {
                    $(".tree-icons", el).removeClass('wait');
                }

                if ( response.status == 'error' )
                {
                    console.log('error');
                    console.log(response.content);

                    var errMessage = [
                        'not found',
                        'not belong '
                    ];
                    if ( response.content.toLowerCase().indexOf(errMessage) > -1 )
                    {
                        self.flagError(false);
                    }
                    else
                    {
                        self.showError(el, response.content);
                    }
                }
                else
                {
                   

                    console.log(response.content);
                    el.append( self.parseContent(response.content) );
                    if( o.root == dirValue ) {
                        el.find('ul:hidden').show();
                    } else {
                        el.find('ul:hidden').slideDown('fast');
                    }
                }

                //remove any error if success
                self.removeError();

                if (next) next();
            },
            error: function(errors)
            {
                if ( errors.statusText == 'timeout' )
                {
                    console.log(errors);
                    console.log("Request timeout. Retrying...");

                    //register error
                    self.flagError(false);

                    //display and bind the folder tree
                    self.displayFolderTree(el, dirValue, next);
                }
            },
            complete: function()
            {
                Backbone.trigger("complete:getfolders");
            }
        });
    },

    waitUntilExists: function(el, next)
    {
        var checkExist = setInterval(function() {
            // console.log($(el));
            if ($(el).length) {
                clearInterval(checkExist);
                if (next) next(el);
            }
        }, 500);
    },

    start: function()
    {
        //console.log("folders view start called", this.$el);
        var self = this;

        self.waitUntilExists($(this.$el.selector), function(el){
            //console.log("el existed");
            self.displayFolderTree(el, self.settings.root, function(){
                self.bindFolderTree(el);
            });
        });
    },

    refresh: function()
    {
        var self = this;

        //check if the was an error before refreshing the folder list
        if ( this.hasError() )
        {
            this.flagError(true);
        }
        else
        {
            //mark folders fetched after refresh
            Backbone.once("complete:getfolders", function(){
                self.hideLoading();

                self.flagFoldersFetched();

                //if after refresh there was an error show it
                if ( self.hasError() ) {
                    self.flagError(true);
                }
            });

            //empty the container and show a loading message
            $(this.$el.selector).empty();
            this.showLoading();

            this.start();
        }
    },

    flagError: function(withMsg)
    {
        var self = this;
        self.errored = true;

        if ( !withMsg ) return false;

        //listen to dropbox authenticate now
        Backbone.once('picked:dropbox', function(){
            // console.log('Dropbox reauthenticate', self.global.accountModel.get('user_data'));
            self.removeError();

            //show folder path slide again
            Reveal.slide(3);
        });

        self.modalView.showGenericDialog(
            {
                'content': 'We\'re unable to process your registered Dropbox account.<br/>'
            },
            {
                'buttons': [
                    {
                        'id': '#modal-reauthenticate',
                        'class': 'pure-button pure-button-medium pure-red pure-upper h30',
                        'text': 'Re-authenticate',
                        'handler': function(id) {
                            $(id).bind('click', function(){
                                $(this).unbind('click');
                                self.modalView.closeErrorDialog();
                                self.global.pickerView.pick_el.ladda('destroy');

                                //slide number for dropbox
                                Reveal.slide( 0 );

                                return false;
                            });
                        }
                    }
                ]
            }
        );
    },

    removeError: function()
    {
        this.errored = false;
    },

    hasError: function()
    {
        return this.errored;
    },

    /**
     * Following functions will let us know
     * if folders were already fetched
     */
    flagFoldersFetched: function()
    {
        this.foldersFetched = true;
    },

    isFoldersFetched: function()
    {
        return this.foldersFetched;
    },

    /**
     * Following functions will let us know
     * about the current request to server
     */
    flagRequestOnProgress: function()
    {
        this.requestOnProgress = true;
        console.log('request on progress');
    },

    isRequestOnProgress: function()
    {
        console.log('checking request');
        return this.requestOnProgress;
    },

    doneRequest: function()
    {
        this.requestOnProgress = false;
        console.log('request stopped');
    },

    showLoading: function()
    {
        $('.loading-center').show();
    },

    hideLoading: function()
    {
        $('.loading-center').hide();
    },

    toggleLoading: function()
    {
        $('.loading-center').toggleClass('dn');
    }

});