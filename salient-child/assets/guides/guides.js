/**
 * FBHI Guides — frontend behaviour.
 *  - sticky offset below Salient's fixed header
 *  - sidebar collapses to a dropdown on small screens
 *  - scroll-spy for the on-page table of contents
 *  - print button
 */
( function () {
	'use strict';

	var MOBILE_MAX = 999;
	var guide = document.querySelector( '.fbhi-guide' );
	if ( ! guide ) {
		return;
	}

	/* Sticky offset: Salient's header is fixed; measure it live. */
	function setStickyTop() {
		var header = document.getElementById( 'header-outer' );
		var h = header ? header.getBoundingClientRect().height : 0;
		document.documentElement.style.setProperty( '--fbhi-guide-sticky-top', Math.round( h + 24 ) + 'px' );
	}
	setStickyTop();
	window.addEventListener( 'resize', setStickyTop );
	window.addEventListener( 'load', setStickyTop );

	/* Mobile dropdown: the whole nav is a <details>; open on desktop, closed on mobile. */
	var wrap = guide.querySelector( '.fbhi-guide-nav__wrap' );
	var lastMode = null;
	function syncNavMode() {
		if ( ! wrap ) {
			return;
		}
		var mobile = window.innerWidth <= MOBILE_MAX;
		if ( mobile !== lastMode ) {
			wrap.open = ! mobile;
			lastMode = mobile;
		}
	}
	syncNavMode();
	window.addEventListener( 'resize', syncNavMode );

	/* Chapter rows: the link navigates, the chevron toggles. Keep a link click
	   from also toggling the <details> (it would flash before navigation). */
	guide.querySelectorAll( '.fbhi-guide-nav__chapter-summary .fbhi-guide-nav__link' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
		} );
	} );

	/* Close the mobile dropdown after choosing an in-page link. */
	if ( wrap ) {
		wrap.addEventListener( 'click', function ( e ) {
			var link = e.target.closest( 'a[data-toc-target]' );
			if ( link && link.getAttribute( 'data-toc-target' ) && window.innerWidth <= MOBILE_MAX ) {
				wrap.open = false;
			}
		} );
	}

	/* Scroll-spy: highlight the TOC link of the heading currently in view. */
	var links = Array.prototype.slice.call( guide.querySelectorAll( 'a[data-toc-target]' ) ).filter( function ( a ) {
		return a.getAttribute( 'data-toc-target' );
	} );
	var headings = links.map( function ( a ) {
		return document.getElementById( a.getAttribute( 'data-toc-target' ) );
	} ).filter( Boolean );

	if ( headings.length && 'IntersectionObserver' in window ) {
		var byId = {};
		links.forEach( function ( a ) {
			byId[ a.getAttribute( 'data-toc-target' ) ] = a;
		} );
		var activeId = null;
		function activate( id ) {
			if ( id === activeId ) {
				return;
			}
			activeId = id;
			links.forEach( function ( a ) {
				var on = a.getAttribute( 'data-toc-target' ) === id;
				a.classList.toggle( 'is-active', on );
				if ( on ) {
					a.setAttribute( 'aria-current', 'location' );
				} else {
					a.removeAttribute( 'aria-current' );
				}
			} );
		}
		function currentHeading() {
			var top = parseInt( getComputedStyle( document.documentElement ).getPropertyValue( '--fbhi-guide-sticky-top' ), 10 ) || 120;
			var current = headings[ 0 ];
			for ( var i = 0; i < headings.length; i++ ) {
				if ( headings[ i ].getBoundingClientRect().top - top - 16 <= 0 ) {
					current = headings[ i ];
				} else {
					break;
				}
			}
			return current;
		}
		var ticking = false;
		function onScroll() {
			if ( ticking ) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame( function () {
				activate( currentHeading().id );
				ticking = false;
			} );
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	/* Print. */
	guide.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '[data-guide-print]' ) ) {
			window.print();
		}
	} );
} )();
