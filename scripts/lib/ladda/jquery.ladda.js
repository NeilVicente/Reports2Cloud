/*!
 * Copyright (c) 2011-2013 Felix Gnass
 * Licensed under the MIT license
 */
(function(t,e){"object"==typeof exports?module.exports=e():"function"==typeof define&&define.amd?define(e):t.Spinner=e()})(this,function(){"use strict";function t(t,e){var i,n=document.createElement(t||"div");for(i in e)n[i]=e[i];return n}function e(t){for(var e=1,i=arguments.length;i>e;e++)t.appendChild(arguments[e]);return t}function i(t,e,i,n){var a=["opacity",e,~~(100*t),i,n].join("-"),r=.01+100*(i/n),o=Math.max(1-(1-t)/e*(100-r),t),s=l.substring(0,l.indexOf("Animation")).toLowerCase(),d=s&&"-"+s+"-"||"";return p[a]||(c.insertRule("@"+d+"keyframes "+a+"{"+"0%{opacity:"+o+"}"+r+"%{opacity:"+t+"}"+(r+.01)+"%{opacity:1}"+(r+e)%100+"%{opacity:"+t+"}"+"100%{opacity:"+o+"}"+"}",c.cssRules.length),p[a]=1),a}function n(t,e){var i,n,a=t.style;if(void 0!==a[e])return e;for(e=e.charAt(0).toUpperCase()+e.slice(1),n=0;u.length>n;n++)if(i=u[n]+e,void 0!==a[i])return i}function a(t,e){for(var i in e)t.style[n(t,i)||i]=e[i];return t}function r(t){for(var e=1;arguments.length>e;e++){var i=arguments[e];for(var n in i)void 0===t[n]&&(t[n]=i[n])}return t}function o(t){for(var e={x:t.offsetLeft,y:t.offsetTop};t=t.offsetParent;)e.x+=t.offsetLeft,e.y+=t.offsetTop;return e}function s(t){return this===void 0?new s(t):(this.opts=r(t||{},s.defaults,f),void 0)}function d(){function i(e,i){return t("<"+e+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',i)}c.addRule(".spin-vml","behavior:url(#default#VML)"),s.prototype.lines=function(t,n){function r(){return a(i("group",{coordsize:l+" "+l,coordorigin:-d+" "+-d}),{width:l,height:l})}function o(t,o,s){e(p,e(a(r(),{rotation:360/n.lines*t+"deg",left:~~o}),e(a(i("roundrect",{arcsize:n.corners}),{width:d,height:n.width,left:n.radius,top:-n.width>>1,filter:s}),i("fill",{color:n.color,opacity:n.opacity}),i("stroke",{opacity:0}))))}var s,d=n.length+n.width,l=2*d,u=2*-(n.width+n.length)+"px",p=a(r(),{position:"absolute",top:u,left:u});if(n.shadow)for(s=1;n.lines>=s;s++)o(s,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(s=1;n.lines>=s;s++)o(s);return e(t,p)},s.prototype.opacity=function(t,e,i,n){var a=t.firstChild;n=n.shadow&&n.lines||0,a&&a.childNodes.length>e+n&&(a=a.childNodes[e+n],a=a&&a.firstChild,a=a&&a.firstChild,a&&(a.opacity=i))}}var l,u=["webkit","Moz","ms","O"],p={},c=function(){var i=t("style",{type:"text/css"});return e(document.getElementsByTagName("head")[0],i),i.sheet||i.styleSheet}(),f={lines:12,length:7,width:5,radius:10,rotate:0,corners:1,color:"#000",direction:1,speed:1,trail:100,opacity:.25,fps:20,zIndex:2e9,className:"spinner",top:"auto",left:"auto",position:"relative"};s.defaults={},r(s.prototype,{spin:function(e){this.stop();var i,n,r=this,s=r.opts,d=r.el=a(t(0,{className:s.className}),{position:s.position,width:0,zIndex:s.zIndex}),u=s.radius+s.length+s.width;if(e&&(e.insertBefore(d,e.firstChild||null),n=o(e),i=o(d),a(d,{left:("auto"==s.left?n.x-i.x+(e.offsetWidth>>1):parseInt(s.left,10)+u)+"px",top:("auto"==s.top?n.y-i.y+(e.offsetHeight>>1):parseInt(s.top,10)+u)+"px"})),d.setAttribute("role","progressbar"),r.lines(d,r.opts),!l){var p,c=0,f=(s.lines-1)*(1-s.direction)/2,h=s.fps,m=h/s.speed,g=(1-s.opacity)/(m*s.trail/100),v=m/s.lines;(function y(){c++;for(var t=0;s.lines>t;t++)p=Math.max(1-(c+(s.lines-t)*v)%m*g,s.opacity),r.opacity(d,t*s.direction+f,p,s);r.timeout=r.el&&setTimeout(y,~~(1e3/h))})()}return r},stop:function(){var t=this.el;return t&&(clearTimeout(this.timeout),t.parentNode&&t.parentNode.removeChild(t),this.el=void 0),this},lines:function(n,r){function o(e,i){return a(t(),{position:"absolute",width:r.length+r.width+"px",height:r.width+"px",background:e,boxShadow:i,transformOrigin:"left",transform:"rotate("+~~(360/r.lines*d+r.rotate)+"deg) translate("+r.radius+"px"+",0)",borderRadius:(r.corners*r.width>>1)+"px"})}for(var s,d=0,u=(r.lines-1)*(1-r.direction)/2;r.lines>d;d++)s=a(t(),{position:"absolute",top:1+~(r.width/2)+"px",transform:r.hwaccel?"translate3d(0,0,0)":"",opacity:r.opacity,animation:l&&i(r.opacity,r.trail,u+d*r.direction,r.lines)+" "+1/r.speed+"s linear infinite"}),r.shadow&&e(s,a(o("#000","0 0 4px #000"),{top:"2px"})),e(n,e(s,o(r.color,"0 0 1px rgba(0,0,0,.1)")));return n},opacity:function(t,e,i){t.childNodes.length>e&&(t.childNodes[e].style.opacity=i)}});var h=a(t("group"),{behavior:"url(#default#VML)"});return!n(h,"transform")&&h.adj?d():l=n(h,"animation"),s});


/*!
 * Ladda jQuery 0.6.0
 * http://secondstreet.com/
 * MIT Licensed
 *
 * Ladda is based upon the MIT licensed Ladda. http://lab.hakim.se/ladda
 *
 * Ladda, Copyright (C) 2013 Hakim El Hattab, http://hakim.se
 * Ladda jQuery, Copyright (C) 2013 Second Street Media, http://secondstreet.com
 */

(function( $ ) {

	var createSpinner = function( $el ) {

		var height = $el.height();

		// If the button is tall we can afford some padding
		if( height > 32 ) {
			height *= 0.8;
		}

		// Prefer an explicit height if one is defined
		if( $el.attr( 'data-spinner-size' ) ) {
			height = parseInt( $el.attr( 'data-spinner-size' ), 10 );
		}

		var lines = 12;
		var radius = height * 0.2;
		var length = radius * 0.6;
		var width = radius < 7 ? 2 : 3;

		return new Spinner( {
			color: '#fff',
			lines: lines,
			radius: radius,
			length: length,
			width: width,
			className: ''
		} );

	};

	$.fn.ladda = function( options, progress ) {

		return $( this ).each( function() {
			// Conveniently cached jQuery element
			var $this = $( this );
			// Wrapper element for the spinner
			var $spinnerWrapper;
			// Timeout used to delay stopping of the spin animation

			if( typeof $this.attr( 'data-ladda' ) === 'undefined' ) { // If it hasn't been initialized yet
				// The text contents must be wrapped in a ladda-label
				// element, create one if it doesn't already exist
				if( $this.find( '.ladda-label' ).length < 1 ) {
					$this.wrapInner( '<span class="ladda-label" />' );
				}

				// Create the wrapper element for the spinner
				$spinnerWrapper = $( '<span class="ladda-spinner" />' );
				$this.append( $spinnerWrapper );

				// Mark it as initialized
				$this.attr( 'data-ladda', '1' );
			}
			else {
				$spinnerWrapper = $this.find( '.ladda-spinner' );
			}

			// Create the spinner
			var spinner = createSpinner( $this );

			if( typeof options === 'string' ) {
				// Controlling loading explicitely
				switch( options ) {
					case 'start':

						clearTimeout( $this.data( 'spinnerTimeout' ) );
						spinner.spin( $spinnerWrapper.get( 0 ) );

						return $this // chain
							.prop( 'disabled', true )
							.attr( 'data-loading', '' )
							.ladda( 'setProgress', 0 );

					case 'stop':
						
						clearTimeout( $this.data( 'spinnerTimeout' ) );
						$this.find( '.ladda-spinner' ).empty();
						
						return $this // chain
							.prop( 'disabled', false )
							.removeAttr( 'data-loading' );

					case 'destroy':
						clearTimeout( $this.data( 'spinnerTimeout' ) );
						var label =$this.find( '.ladda-label' );
						var oriCont = label.html();
						$this.find( '.ladda-spinner' ).empty();
						label.empty();

						$this.html(oriCont);
						return $this // chain
						    .prop( 'disabled', false )
						    .removeAttr( 'data-loading' )
						    .removeAttr( 'data-ladda' );

					case 'toggle':

						if( $this.ladda( 'isLoading' ) ) return $this.ladda( 'stop' ); // chain
						return $this.ladda( 'start' ); // chain

					case 'setProgress':

						var $progressElement = $this.find( '.ladda-progress' );

						// Remove the progress bar if we're at 0 progress
						if( progress === 0 && $progressElement.length > 0 ) {
							$progressElement.remove();
						}
						else {
							if ( $progressElement.length < 1 ) {
								$progressElement = $( '<div class="ladda-progress" />' );
								$this.append( $progressElement );
							}

							$progressElement.width( ( progress || 0 ) * $this.outerWidth() );
						}

						return $this; // chain

					case 'enable':

						return $this // chain
							.ladda( 'stop' );

					case 'disable':

						return $this // chain
							.ladda( 'stop' )
							.prop( 'disabled', true );

					case 'isLoading':
					
						return ( typeof $this.attr( 'data-loading' ) === undefined ); // boolean

					default:

						return $this; // chain
				}
			}
			else {
				// Binds the target buttons to automatically enter the
				// loading state when clicked.

				var timeout = -1;

				$this.click( function() {
					$this.ladda( 'start' );

					// Set a loading timeout if one is specified
					if( options && typeof options.timeout === 'number' ) {
						clearTimeout( timeout );
						timeout = setTimeout( function() { $this.ladda( 'stop' ); }, options.timeout );
						$this.data( 'spinnerTimeout', timeout );
					}

					// Invoke callbacks
					if( options && typeof options.callback === 'function' ) {
						options.callback.apply( null, [ $this ] );
					}
				} );
			}

		} );

	};
}( jQuery ));