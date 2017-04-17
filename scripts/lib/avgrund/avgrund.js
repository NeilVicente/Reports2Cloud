/**
 *  jQuery Avgrund Popin Plugin - Modded by kenneth
 *  http://github.com/voronianski/jquery.avgrund.js/
 *
 *  (c) 2012-2013 http://pixelhunter.me/
 *  MIT licensed
 */

(function ($, window) {
	$.fn.avgrund = function (mode, options) {

		if ( typeof mode == 'object' ) {
			options = mode;
		}

		var defaults = {
			width: 380, // max = 640
			height: 280, // max = 350
			showClose: false,
			showCloseText: '',
			closeByEscape: true,
			closeByDocument: true,
			holderClass: '',
			overlayClass: '',
			enableStackAnimation: false,
			onBlurContainer: '',
			openOnEvent: true,
			setEvent: 'click',
			onLoad: false,
			onUnload: false,
			content: '',
			footer: ''
		};

		options = $.extend(defaults, options);

		return this.each(function() {
			var self = $(this),
				body = $('body'),
				maxWidth = options.width > 640 ? 640 : options.width,
				maxHeight = options.height > 350 ? 350 : options.height,
				hasFooter = options.footer.length > 0 ? true : false;

			if ( typeof options.content === 'function' ) {
				content = options.content(self);
			} else if ( typeof mode == 'string' ) {
				if ( options.content == '' ) {
					content = self.html();
				} else {
					content = options.content;
				}
			} else {
				content = options.content;
			}

			body.addClass('avgrund-ready');

			if (options.onBlurContainer !== '') {
				$(options.onBlurContainer).addClass('avgrund-blur');
			}

			function onDocumentKeyup (e) {
				if (options.closeByEscape) {
					if (e.keyCode === 27) {
						deactivate();
					}
				}
			}

			function onDocumentClick (e) {
				if (options.closeByDocument) {
					if ($(e.target).is('.avgrund-overlay, .avgrund-close')) {
						e.preventDefault();
						deactivate();
					}
				} else {
					if ($(e.target).is('.avgrund-close')) {
						e.preventDefault();
						deactivate();
					}
				}
			}

			function cleanSafariErrors() {
				if ( navigator.userAgent.match(/safari/i) )
				{
					$('.avgrund-popin, avgrund-overlay').css({
						'-webkit-transition': 'none',
						'-moz-transition': 'none',
						'-ms-transition': 'none',
						'-o-transition': 'none',
						'transition': 'none'
					});
				}
			}

			function activate () {
				if (typeof options.onLoad === 'function') {
					options.onLoad(self);
				}

				setTimeout(function() {
					body.addClass('avgrund-active');
				}, 100);

				//create main elements
				var popinElem = $('<div/>').addClass('avgrund-popin ' + options.holderClass)
				  , overlayelem  = $('<div/>').css('height', window.innerHeight+'px').addClass('avgrund-overlay ' + options.overlayClass)
				  , contentElem = $('<div/>').addClass('avgrund-content')
				  , footerElem = $('<div/>').addClass('avgrund-footer')
				  , closeElem = $('<a/>', { href: '#'}).addClass('avgrund-close');

				//append custom content to content Elem
				contentElem.append(content);

				//append to contentElem popin elem
				popinElem.append(contentElem);

				if(hasFooter) {
					//append to footerElem to popin elem
					popinElem.append(footerElem);
				}

				//append to body
				body.append(popinElem);

				//append overlay
				body.append(overlayelem);

				cleanSafariErrors();

				popinElem.css({
					'width': maxWidth + 'px',
					'height': maxHeight + 'px',
					'margin-left': '-' + (maxWidth / 2 + 10) + 'px',
					'margin-top': '-' + (maxHeight / 2 + 10) + 'px'
				});

				//modify the content elem height
				var cHeight = popinElem.height();
				var cWidth = popinElem.width();
				contentElem.css({
					'height': cHeight + 'px',
					'width' : cWidth + 'px',
				});

				//set the footer content
				if(hasFooter) {
					footerElem.append(options.footer);

					//modify whole popin if it has a footer elem
					var nHeight = (popinElem.height() + footerElem.height());
					popinElem.height(nHeight);
				}

				if (options.showClose) {
					closeElem.append(options.showCloseText);
					popinElem.append(closeElem);
				}

				if (options.enableStackAnimation) {
					popinElem.addClass('stack');
				}

				body.bind('keyup', onDocumentKeyup);
				body.bind('click', onDocumentClick);
			}

			function deactivate () {
				body.unbind('keyup', onDocumentKeyup);
				body.unbind('click', onDocumentClick);

				body.removeClass('avgrund-active');

				$('.avgrund-overlay', body).remove();

				// setTimeout(function() {
					$('.avgrund-popin').remove();
				// }, 500);

				body.removeClass('avgrund-ready');

				if (typeof options.onUnload === 'function') {
					options.onUnload(self);
				}
			}

			if (options.openOnEvent) {

				if ( mode && typeof mode == 'string' ) {
					switch(mode) {
						case 'show':
							activate();
						break;
						case 'close':
							deactivate();
						break;
					}
				} else {
					self.bind(options.setEvent, function (e) {
						e.stopPropagation();

						if ($(e.target).is('a')) {
							e.preventDefault();
						}

						activate();
					});
				}
			} else {
				activate();
			}
		});
	};
})(jQuery, window);