/**
 * @fileoverview
 * <p>
 * <tt>syntax_plugin_code</tt> is a static JavaScript class providing
 * visibility toggling for DokuWiki's CODE markup.
 * </p><p>
 * All that's to be done in the corresponding HTML file is to include
 * this file by placing <tt>
 * '&lt;script type="text/javascript" src="syntax_plugin_code.js"&gt;</tt>
 * <tt>&lt;/script&gt;</tt>
 * in the HEAD section; with DokuWiki this is done automatically.
 * The script is initialized and activated automatically at loadtime.
 * </p><pre>
 *		Copyright (C) 2007  M.Watermann, D-10247 Berlin, FRG
 *			All rights reserved
 *		EMail : &lt;support@mwat.de&gt;
 * </pre>
 * @author <a href="mailto:support@mwat.de">Matthias Watermann</a>
 * @version <tt>$Id: syntax_plugin_code.js,v 1.1 2008-03-12 22:57:51 pajoye Exp $</tt>
 * @since created 07-Feb-2007
 */

/**
 * Setup the <tt>syntax_plugin_code</tt> behaviour.
 *
 * <p>
 * To avoid polluting the global namespace even more than it already is with
 * DokuWiki we define and invoke a function to create a closure that serves
 * as our private namespace. This function will export just a (meaningless)
 * value while encapsulating all methods in its private namespace.
 * </p>
 * @public
 */
var syntax_plugin_code = function() {
	// Private member fields (preallocated strings to avoid repeated
	// memory allocations inside loops and event handlers):
	var _cH = ' codeHidden', _cS = ' codeShown',	// CSS classes
		_reH = /\s*\bcodeHidden\b/gi, _reS = /\s*\bcodeShown\b/gi,
		_B = '', _ini = 'syntax_plugin_code.js()', _DW,

// The '_Divs()' and '_Ps()' methods are one-time-only - meaning that
// they're needed only during setup. Hence they are declared as function
// members and will be reset to a meaningless value once they've done
// their respective job.

	/**
	 * Get a list of <tt>DIV</tt> elements with CSS class <tt>'code'</tt>.
	 *
	 * @returns Array A (possibly empty) list of <tt>div</tt> elements with
	 * CSS class <tt>'code'</tt> assigned.
	 * @type Array
	 * @private
	 * @member syntax_plugin_code
	 */
	_Divs = function() {
		var d, l, r = [];
		try {
			if ((d = window.document.getElementsByTagName('div')) &&
			(l = d.length)) {
				var e, re = /\bcode\b/i;
				do {
					if ((e = d[--l]) && (e.className) && re.test(e.className)) {
						// 'Array.push()' is not implemented by older M$IE
						r[r.length] = e;
					} // if
				} while (0 < l);
			} // if
		} catch(X) {} // return the empty list
		return r;
	}, // _Divs()

	/**
	 * Get a list of <tt>P</tt> elements representing a footer/header
	 * for CODE markup.
	 *
	 * @returns Array A (possibly empty) list of <tt>P</tt> elements
	 * referencing the footer/header elements of the respective PRE elements.
	 * @type Array
	 * @private
	 * @member syntax_plugin_code
	 */
	_Ps = function() {
		var d = _Divs(), l, r = [];
		_Divs = null;	// release memory
		if ((l = d.length)) {
			var e, fc, fcn, lc, lcn, p = 'p', pf = 'pre';
			try {
				do {
					if ((e = d[--l]) &&
					(fc = e.firstChild) && (fcn = fc.tagName.toLowerCase()) &&
					(lc = e.lastChild) && (lcn = lc.tagName.toLowerCase())) {
						if ((pf == fcn) && (p == lcn)) {
							// footer line
							lc._PRE = fc;	// link PRE to P
							r[r.length] = lc;
						} else if ((pf == lcn) && (p == fcn)) {
							// header line
							fc._PRE = lc;	// link PRE to P
							r[r.length] = fc;
						} // if
						// ELSE: unknown markup scheme (i.e. ignore)
					} // if
					d.length = l;	// free mem
				} while (0 < l);
			} catch(X) {} // return the empty list
		} // if
		return r;
	}; // _Ps()

	/**
	 * Swap two class names of the given <tt>anObj</tt>.
	 *
	 * <p>
	 * This method gets called internally by the <tt>_toggle()</tt> method.
	 * </p>
	 * @param anObj Object The document's element whose class is to change.
	 * @param aRE2Del Object A RegEx object matching the CSS class to remove.
	 * @param aCss2Add String The CSS class name to add.
	 * @private
	 * @member syntax_plugin_code
	 */
	function _swap(anObj, aRE2Del, aCss2Add) {
		aRE2Del.lastIndex = 0;
		if (aRE2Del.test(anObj.className)) {
			aRE2Del.lastIndex = 0;
			anObj.className = (aCss2Add) ?
				anObj.className.replace(aRE2Del, aCss2Add) :
				anObj.className.replace(aRE2Del, _B);
		} else if (aCss2Add) {
			// Old class not set currently
			anObj.className += aCss2Add;
		} // if
		aRE2Del.lastIndex = 0;
	} // _swap()

	/**
	 * Toggle the visibility of the associated PRE element.
	 *
	 * <p>
	 * Event handler used by the header/footer P elements.
	 * </p>
	 * @param anEvent Object The current event object.
	 * @private
	 * @member syntax_plugin_code
	 */
	function _toggle(anEvent) {
		if ((anEvent = anEvent || window.event)) {
			anEvent.cancelBubble = true;
			anEvent.returnValue = false;
		} // if
		if (this.className) {
			if (_reH.test(this.className)) {
				_swap(this._PRE, _reH, _cS);
				_swap(this, _reH, _cS);
			} else {
				_swap(this, _reS, _cH);
				_swap(this._PRE, _reS, _cH);
			} // if
		} else {
			this.className = this._PRE.className = _cH;
		} // if
		return false;	// no further action required
	} // _toggle()

	/**
	 * Setup the behaviour.
	 *
	 * <p>
	 * This method is sort of constructor.
	 * It sets up all code blocks marked up by the (PHP)
	 * <tt>syntax_plugin_code</tt> with header/footer lines so that they
	 * toggle the respective PRE's visibility (by means of exchanging CSS
	 * classes).
	 * </p>
	 * @member syntax_plugin_code
	 */
	function ini() {
		if (_Ps) {
			_ini = null;	// release memory
		} else {
			return;	// something's broken ...
		} // if
		var d = _Ps(), l;
		_Ps = null;	// release memory
		if ((l = d.length)) {
			var p, re = /\s*\bHideOnInit\b/ig;
			do {
				if ((p = d[--l]) && (p._PRE)) {
					if (re.test(p.className)) {
						re.lastIndex = 0;
						p._PRE.className += _cH;		// add CSS class
						p.className = p.className.replace(re, _cH);	// dito
					} else {
						p._PRE.className += _cS;	// add CSS class
						p.className += _cS;		// dito
					} // if
					p.onclick = _toggle;	// setup event
					re.lastIndex = 0;
				} // if
			}  while (0 < l);
		} // if
		if (_DW) {
			// Remove this method from the event handlers list to save memory
			try {
				window.removeEvent(window, 'load', ini);
			} catch(X) { } // nothing we could about it
		} // if
	} // _js()

	// Delay the setup until after the document is loaded.
	if ((_DW = ('undefined' != typeof(window.addEvent)))) {
		try {
			window.addEvent(window, 'load', ini);
		} catch(X) {
			// DokuWiki's event library not loaded or broken.
			// Let a background thread do the job:
			window.setTimeout(_ini, 512);
		} // try
	} else {
		// Older DokuWiki release: using background thread:
		window.setTimeout(_ini, 512);
	} // if
	return {js:ini};
}(); // syntax_plugin_code

/* _EoF_ */
