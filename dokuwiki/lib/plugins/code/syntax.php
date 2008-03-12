<?php
if (! class_exists('syntax_plugin_code')) {
	if (! defined('DOKU_PLUGIN')) {
		if (! defined('DOKU_INC')) {
			define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
		} // if
		define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
	} // if
	// Include parent class:
	require_once(DOKU_PLUGIN . 'syntax.php');
	// Handling with Geshi here, hence include it:
	require_once(DOKU_INC . 'inc/geshi.php');

/**
 * <tt>syntax_plugin_code.php </tt>- A PHP4 class that implements the
 * <tt>DokuWiki</tt> plugin for <tt>highlighting</tt> code fragments.
 *
 * <p>
 * Usage:<br>
 * <tt>&#60;code [language startno |[fh] text |[hs]]&#62;...&#60;/code&#62;</tt>
 * </p><pre>
 *	Copyright (C) 2006, 2007  M.Watermann, D-10247 Berlin, FRG
 *			All rights reserved
 *		EMail : &lt;support@mwat.de&gt;
 * </pre>
 * <div class="disclaimer">
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either
 * <a href="http://www.gnu.org/licenses/gpl.html">version 3</a> of the
 * License, or (at your option) any later version.<br>
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * </div>
 * @author <a href="mailto:support@mwat.de">Matthias Watermann</a>
 * @version <tt>$Id$</tt>
 * @since created 24-Dec-2006
 */
class syntax_plugin_code extends DokuWiki_Syntax_Plugin {

	/**
	 * @privatesection
	 */
	//@{

	/**
	 * Additional markup used with older DokuWiki installations.
	 *
	 * @private
	 * @see _fixJS()
	 */
	var $_JSmarkup = FALSE;

	/**
	 * Indention markup used by <tt>_addLines()</tt>.
	 *
	 * <p>
	 * Note that we're using raw <em>UTF-8 NonBreakable Spaces</em> here.
	 * </p>
	 * @private
	 * @see _addLines()
	 */
	var $_lead = array('', ' ', '  ', '   ', '    ',
		'     ', '      ', '       ');

	/**
	 * The character to use for replacing invalig characters in namespace
	 * names (defaults to the <tt>$conf['sepchar']</tt> setting).
	 *
	 * <p>
	 * The one and only purpose of this private member field is to avoid the
	 * runtime overhead of calltime arguments and local references for/in
	 * our private <tt>_makeID()</tt> method.
	 * </p>
	 * @private
	 * @see render()
	 */
	var $_sepChar = FALSE;

	/**
	 * Prepare the markup to render the DIFF text.
	 *
	 * @param $aText String The DIFF text to markup.
	 * @param $aFormat String The DIFF format used ('u', 'c', 'n|r', 's').
	 * @param $aDoc String Reference to the current renderer's
	 * <tt>doc</tt> property.
	 * @return Boolean <tt>TRUE</tt>.
	 * @private
	 * @see render()
	 */
	function _addDiff(&$aText, &$aFormat, &$aDoc) {
		// Since we're inside a PRE block we need the leading LFs:
		$ADD = "\n" . '<span class="diff-addedline">';
		$DEL = "\n" . '<span class="diff-deletedline">';
		$HEAD = "\n" . '<span class="diff-blockheader">';
		$CLOSE = '</span>';
		// Common headers for all formats;
		// the RegEx needs at least ')#' appended!
		$DiffHead = '#\n((?:diff\s[^\n]*)|(?:Index:\s[^\n]*)|(?:={60,})'
			. '|(?:RCS file:\s[^\n]*)|(?:retrieving revision [0-9][^\n]*)';
		switch ($aFormat) {
			case 'u':	// unified output
				$aDoc .= preg_replace(
					array($DiffHead . '|(?:@@[^\n]*))#',
						'|\n(\+[^\n]*)|',
						'|\n(\-[^\n]*)|'),
					array($HEAD . '\1' . $CLOSE,
						$ADD . '\1' . $CLOSE,
						$DEL . '\1' . $CLOSE),
					$aText);
				return TRUE;
			case 'c':	// context output
				$sections = preg_split('|(\n\*{5,})|',
					preg_replace($DiffHead . ')#',
						$HEAD . '\1' . $CLOSE,
						$aText),
					-1, PREG_SPLIT_DELIM_CAPTURE);
				$sections[0] = preg_replace(
					array('|\n(\-{3}[^\n]*)|',
						'|\n(\*{3}[^\n]*)|'),
					array($ADD . '\1' . $CLOSE,
						$DEL . '\1' . $CLOSE),
					$sections[0]);
				$c = count($sections);
				for ($i = 1; $c > $i; ++$i) {
					$hits = array();
					if (preg_match('|^\n(\*{5,})|',
						$sections[$i], $hits)) {
						unset($hits[0]);
						$sections[$i] = $HEAD . $hits[1] . $CLOSE;
					} else if (preg_match('|^\n(\x2A{3}\s[^\n]*)(.*)|s',
						$sections[$i], $hits)) {
						unset($hits[0]);	// free mem
						$parts = preg_split('|\n(\-{3}\s[^\n]*)|',
							$hits[2], -1, PREG_SPLIT_DELIM_CAPTURE);
						// $parts[0] == OLD code
						$parts[0] = preg_replace('|\n([!\-][^\n]*)|',
							$DEL . '\1' . $CLOSE, $parts[0]);
						// $parts[1] == head of NEW code
						$parts[1] = $ADD . $parts[1] . $CLOSE;
						// $parts[2] == NEW code
						$parts[2] = preg_replace(
							array('|\n([!\x2B][^\n]*)|',
								'|\n(\x2A{3}[^\n]*)|'),
							array($ADD . '\1' . $CLOSE,
								$DEL . '\1' . $CLOSE),
							$parts[2]);
						if (isset($parts[3])) {
							// TRUE when handling multi-file patches
							$parts[3] = preg_replace('|^(\x2D{3}[^\n]*)|',
								$ADD . '\1' . $CLOSE, $parts[3]);
						} // if
						$sections[$i] = $DEL . $hits[1] . $CLOSE
							. implode('', $parts);
					} // if
					// ELSE: leave $sections[$i] as is
				} // for
				$aDoc .= implode('', $sections);
				return TRUE;
			case 'n':	// RCS output
				// Only added lines are there so we highlight just the
				// diff indicators while leaving the text alone.
				$aDoc .= preg_replace(
					array($DiffHead . ')#',
						'|\n(d[0-9]+\s+[0-9]+)|',
						'|\n(a[0-9]+\s+[0-9]+)|'),
					array($HEAD . '\1' . $CLOSE,
						$DEL . '\1' . $CLOSE,
						$ADD . '\1' . $CLOSE),
					$aText);
				return TRUE;
			case 's':	// simple output
				$aDoc .= preg_replace(
					array($DiffHead
						. '|((?:[0-9a-z]+(?:,[0-9a-z]+)*)(?:[^\n]*)))#',
						'|\n(\x26#60;[^\n]*)|',
						'|\n(\x26#62;[^\n]*)|'),
					array($HEAD . '\1' . $CLOSE,
						$DEL . '\1' . $CLOSE,
						$ADD . '\1' . $CLOSE),
					$aText);
				return TRUE;
			default:	// unknown diff format
				$aDoc .= $aText;	// just append any unrecognized text
				return TRUE;
		} // switch
	} // _addDiff()

	/**
	 * Add the lines of the given <tt>$aList</tt> to the specified
	 * <tt>$aDoc</tt> beginning with the given <tt>$aStart</tt> linenumber.
	 *
	 * @param $aList Array [IN] the list of lines as prepared by
	 * <tt>render()</tt>, [OUT] <tt>FALSE</tt>.
	 * @param $aStart Integer The first linenumber to use.
	 * @param $aDoc String Reference to the current renderer's
	 * <tt>doc</tt> property.
	 * @private
	 * @see render()
	 */
	function _addLines(&$aList, $aStart, &$aDoc) {
		// Since we're dealing with monospaced fonts here the width of each
		// character (space, NBSP, digit) is the same. Hence the length of
		// a digits string gives us its width i.e. the number of digits.
		$i = $aStart + count($aList);	// greatest line mumber
		$g = strlen("$i");		// width of greatest number
		while (list($i, $l) = each($aList)) {
			unset($aList[$i]);	// free mem
			$aDoc .= '<b class="lno">' . $this->_lead[$g - strlen("$aStart")]
				. "$aStart:</b>" . ((($l) && ('&nbsp;' != $l))
					? " $l\n"
					: "\n");
			++$aStart;	// increment line number
		} // while
		$aList = FALSE;
	} // _addLines()

	/**
	 * Internal convenience method to replace HTML special characters.
	 *
	 * @param $aString String [IN] The text to handle; [OUT] the modified
	 * text (i.e. the method's result).
	 * @return String The string with HTML special chars replaced.
	 * @private
	 * @since created 05-Feb-2007
	 * @static
	 */
	function &_entities(&$aString) {
		$aString = str_replace(array('&', '<', '>'),
			array('&#38;', '&#60;', '&#62;'), $aString);
		return $aString;
	} // _entities()

	/**
	 * Try to fix some markup error of the GeSHi SHELL highlighting.
	 *
	 * <p>
	 * The GeShi highlighting for type "sh" (i.e. "bash") is, well,
	 * seriously flawed (at least up to version 1.0.7.19).
	 * Especially handling of comments and embedded string as well as
	 * keyword is plain wrong. 
	 * </p><p>
	 * This internal helper method tries to solve some minor problems by
	 * removing highlight markup embedded in comment markup.
	 * This is, however, by no means a final resolution: GeSHi obviously
	 * keeps a kind of internal state resulting in highlighting markup spawing
	 * (i.e. repeated on) several lines. Which - if that state is wrong -
	 * causes great demage not by corrupting the data but by confusing the
	 * reader with wrong markup.
	 * The easiest way to trigger such a line spawning confusion is to use
	 * solitary doublequotes or singlequotes (apostrophe) in a comment line ...
	 * </p>
	 * @param $aMarkup String [IN] The highlight markup as returned by GeSHi;
	 * [OUT] <tt>FALSE</tt>.
	 * @param $aDoc String Reference to the current renderer's
	 * <tt>doc</tt> property.
	 * @private
	 * @since created 04-Aug-2007
	 * @see render()
	 */
	function _fixGeSHi_Bash(&$aMarkup, &$aDoc) {
		$lines = explode("\n", $aMarkup);
		$aMarkup = count($lines) - 1;	// number of last line
		while (list($i, $l) = each($lines)) {
			unset($lines[$i]);	// free mem
			$hits = array();
			// GeSHi "bash" module marks up comments with CSS class "re3":
			if (preg_match('|^((.*)<span class="re3">)(.*)$|i', $l, $hits)) {
				if ('#!/bin/' == substr($hits[3], 0, 7)) {
					$aDoc .= $hits[2] . strip_tags($hits[3]);
				} else {
					$aDoc .= $hits[1] . strip_tags($hits[3]) . '</span>';
				} // if
			} else {
				$aDoc .= $l;
			} // if
			if ($i < $aMarkup) {
				// The very last line must not get a LF since that
				// would increase the length (with an additional
				// linenumber showing up at the end of text).
				$aDoc .= "\n";
			} // if
		} // while
	} // _fixGeSHi_Bash()

	/**
	 * Add markup to load JavaScript file with older DokuWiki versions.
	 *
	 * @param $aRenderer Object The renderer used.
	 * @private
	 * @since created 19-Feb-2007
	 * @see render()
	 */
	function _fixJS(&$aRenderer) {
		//XXX This test will break if the DokuWiki file gets renamed:
		if (@file_exists(DOKU_INC . 'lib/exe/js.php')) {
			// Assuming a fairly recent DokuWiki installation
			// handling the plugin files on its own.
			return;
		} // if
		if ($this->_JSmarkup) {
			// Markup already added (or not needed)
			return;
		} // if
		$localdir = realpath(dirname(__FILE__)) . '/';
		$webdir = DOKU_BASE . 'lib/plugins/code/';
		$css = '';
		if (file_exists($localdir . 'style.css')) {
			ob_start();
			@include($localdir . 'style.css');
			// Remove whitespace from CSS and expand IMG paths:
			if ($css = preg_replace(
				array('|\s*/\x2A.*?\x2A/\s*|s', '|\s*([:;\{\},+!])\s*|',
					'|(?:url\x28\s*)([^/])|', '|^\s*|', '|\s*$|'),
				array(' ', '\1', 'url(' . $webdir . '\1'),
				ob_get_contents())) {
				$css = '<style type="text/css">' . $css . '</style>';
			} // if
			ob_end_clean();
		} // if
		$js = (file_exists($localdir . 'script.js'))
			? '</style><script type="text/javascript" src="'
				. $webdir . 'script.js"></script>'
			: '';
		if ($this->_JSmarkup = $css . $js) {
			$aRenderer->doc = $this->_JSmarkup
				. preg_replace('|\s*<p>\s*</p>\s*|', '', $aRenderer->doc);
		} else {
			// Neither CSS nor JS files found.
			// Set member field to skip tests with next call:
			$this->_JSmarkup = TRUE;
		} // if
	} // _fixJS()

	/**
	 * Convert the specified <tt>$aID</tt> to a valid XHTML
	 * fragment identifier.
	 *
	 * <p>
	 * <a href="http://www.w3.org/TR/xhtml1/#guidelines" target="extern">
	 * XHTML 1</a> (section C.8, Fragment Identifiers) gives the regex
	 * <tt>[A-Za-z][A-Za-z0-9:_.-]*</tt> for valid identifiers. Here
	 * it's slightly reduced to <tt>[A-Za-z][A-Za-z0-9_]+</tt> i.e.
	 * all non alphanumeric characters are replaced by underscores.
	 * </p>
	 * @param $aID String The raw ID string.
	 * @return String The URL fragment.
	 * @private
	 * @since created 24-Aug-2005
	 * @see render()
	 */
	function _makeID(&$aID) {
		static $CHARS;
		if (! is_array($CHARS)) {
			$CHARS = array(
				'|[^A-Za-z0-9_\.\-]|', // invalid characters
				'|\\'. $this->_sepChar . '{2,}|', // reduce multiple underscores
				'|^[^A-Za-z]+|',		// invalid leading chars
				'|\\'. $this->_sepChar . '+$|');	// trailing underscores
		} // if
		// As long as DokuWiki (in contrast to W3C) doesn't allow uppercase
		// letters in internal anchor names we've to use "strtolower()"
		// here to make the anchors work within DokuWiki.
		return strtolower(preg_replace($CHARS,
			array($this->_sepChar, $this->_sepChar),
			utf8_deaccent($aID, 0)));
	} // _makeID()

	/**
	 * Add the lines of the given <tt>$aText</tt> to the specified
	 * <tt>$aDoc</tt> beginning with the given <tt>$aStart</tt> linenumber.
	 *
	 * @param $aText String [IN] the text lines as prepared by
	 * <tt>handle()</tt>, [OUT] <tt>FALSE</tt>.
	 * @param $aStart Integer The first linenumber to use.
	 * @param $aDoc String Reference to the current renderer's
	 * <tt>doc</tt> property.
	 * @private
	 * @since created 03-Feb-2007
	 * @see render()
	 */
	function _rawMarkup(&$aText, $aStart, &$aDoc) {
		if ($aStart) {
			$aDoc .= '<pre class="code">' . "\n";
			// Split the prepared data into a list of lines:
			$aText = explode("\n", $aText);
			// Add the numbered lines to the document:
			$this->_addLines($aText, $aStart, $aDoc);
			$aDoc .= '</pre>';
		} else {
			$aDoc .= '<pre class="code">' . $aText . '</pre>';
			$aText = FALSE;
		} // if
	} // _rawMarkup()

	//@}
	/**
	 * @publicsection
	 */
	//@{

	/**
	 * Tell the parser whether the plugin accepts syntax mode
	 * <tt>$aMode</tt> within its own markup.
	 *
	 * @param $aMode String The requested syntaxmode.
	 * @return Boolean <tt>FALSE</tt> (no nested markup allowed).
	 * @public
	 * @see getAllowedTypes()
	 */
	function accepts($aMode) {
		return FALSE;
	} // accepts()

	/**
	 * Connect lookup pattern to lexer.
	 *
	 * @param $aMode String The desired rendermode.
	 * @public
	 * @see render()
	 */
	function connectTo($aMode) {
		// look-ahead to minimize the chance of false matches:
		$this->Lexer->addEntryPattern(
			'\x3Ccode(?=[^>]*\x3E\r?\n.*\n\x3C/code\x3E)',
			$aMode, 'plugin_code');
	} // connectTo()

	/**
	 * Get an array of mode types that may be nested within the
	 * plugin's own markup.
	 *
	 * @return Array Allowed nested types (none).
	 * @public
	 * @see accepts()
	 * @static
	 */
	function getAllowedTypes() {
		return array();
	} // getAllowedTypes()

	/**
	 * Get an associative array with plugin info.
	 *
	 * <p>
	 * The returned array holds the following fields:
	 * <dl>
	 * <dt>author</dt><dd>Author of the plugin</dd>
	 * <dt>email</dt><dd>Email address to contact the author</dd>
	 * <dt>date</dt><dd>Last modified date of the plugin in
	 * <tt>YYYY-MM-DD</tt> format</dd>
	 * <dt>name</dt><dd>Name of the plugin</dd>
	 * <dt>desc</dt><dd>Short description of the plugin (Text only)</dd>
	 * <dt>url</dt><dd>Website with more information on the plugin
	 * (eg. syntax description)</dd>
	 * </dl>
	 * @return Array Information about this plugin class.
	 * @public
	 * @static
	 */
	function getInfo() {
		$c = 'code';	// hack to hide "desc" field from GeShi
		return array(
			'author' =>	'Matthias Watermann',
			'email' =>	'support@mwat.de',
			'date' =>	'2007-08-31',
			'name' =>	'Code Syntax Plugin',
			'desc' =>	'Syntax highlighting with line numbering <'
				. $c . ' lang 1 |[fh] text |[hs]> ... </' . $c . '>',
			'url' =>	'http://wiki.splitbrain.org/plugin:code2');
	} // getInfo()

	/**
	 * Define how this plugin is handled regarding paragraphs.
	 *
	 * <p>
	 * This method is important for correct XHTML nesting. It returns
	 * one of the following values:
	 * </p>
	 * <dl>
	 * <dt>normal</dt><dd>The plugin can be used inside paragraphs.</dd>
	 * <dt>block</dt><dd>Open paragraphs need to be closed before
	 * plugin output.</dd>
	 * <dt>stack</dt><dd>Special case: Plugin wraps other paragraphs.</dd>
	 * </dl>
	 * @return String <tt>'block'</tt> .
	 * @public
	 * @static
	 */
	function getPType() {
		return 'block';
	} // getPType()

	/**
	 * Where to sort in?
	 *
	 * @return Integer <tt>194</tt> (below 'Doku_Parser_Mode_code').
	 * @public
	 * @static
	 */
	function getSort() {
		// class 'Doku_Parser_Mode_code' returns 200
		return 194;
	} // getSort()

	/**
	 * Get the type of syntax this plugin defines.
	 *
	 * @return String <tt>'protected'</tt>.
	 * @public
	 * @static
	 */
	function getType() {
		return 'protected';
	} // getType()

	/**
	 * Handler to prepare matched data for the rendering process.
	 *
	 * <p>
	 * The <tt>$aState</tt> parameter gives the type of pattern
	 * which triggered the call to this method:
	 * </p><dl>
	 * <dt>DOKU_LEXER_UNMATCHED</dt>
	 * <dd>ordinary text encountered within the plugin's syntax mode
	 * which doesn't match any pattern.</dd>
	 * </dl>
	 * @param $aMatch String The text matched by the patterns.
	 * @param $aState Integer The lexer state for the match.
	 * @param $aPos Integer The character position of the matched text.
	 * @param $aHandler Object Reference to the Doku_Handler object.
	 * @return Array Index <tt>[0]</tt> holds the current <tt>$aState</tt>,
	 * index <tt>[1]</tt> the embedded text to highlight,
	 * index <tt>[2]</tt> the language/dialect (or <tt>FALSE</tt>),
	 * index <tt>[3]</tt> the first line number (or <tt>0</tt>),
	 * index <tt>[4]</tt> the top title (or <tt>FALSE</tt>),
	 * index <tt>[5]</tt> the bottom title (or <tt>FALSE</tt>),
	 * index <tt>[6]</tt> hidding CSS flag (or <tt>FALSE</tt>).
	 * @public
	 * @see render()
	 * @static
	 */
	function handle($aMatch, $aState, $aPos, &$aHandler) {
		if (DOKU_LEXER_UNMATCHED == $aState) {
			$aMatch = explode('>', $aMatch, 2);
			// $aMatch[0] : lang etc.
			// $aMatch[1] : text to highlight
			// Strip leading/trailing/EoL whitespace:
			$aMatch[1] = preg_replace(
				array('/(?>\r\n)|\r/', '|^(?>[\t ]*\n+)+|',
					'|[\t ]+\n|', '|(?>\n[\t ]*)+$|'),
				array("\n", '', "\n", ''),
				$aMatch[1]);
		} else {
			return array($aState);	// nothing to do for 'render()'
		} // if
		$l = false;	// default: no language
		$n = 0;		// default: no line numbers
		$ht = $ft = false;	// default: no title
		$css = '';			// default: no CSS flag
		$hits = array();
		/*
			The free form of the RegEx to parse the arguments here is:
		/^
			# "eat" leading whitespace:
			\s*
			(?=\S)	# Look ahead: do not match empty lines. This is
					# needed since all other expressions are optional.
			# Make sure, nothing is given away once it matched:
			(?>
				# We need a separate branch for "diff" because it may be
				# followed by a _letter_ (not digit) indicating the format.
				(?>
					(diff)
					# 1
					(?>\s+([cnrsu]?))?
					#      2
				)
			|
				# Branch for standard language highlighting
				(?>
					# extract language:
					([a-z][^\x7C\s]*)
					# 3
					(?>
						# extract starting line number:
						\s+(\d\d*)
						#   4
					)?
				)
			|
				# Branch for line numbering only
				(\d\d*)
				# 5
			|
				\s*		# dummy needed to match "title only" markup (below)
			)
			# "eat" anything else up to the text delimiter:
			[^\x7C]*
			(?>
				\x7C
				# extract the position flag:
				([bfht])?\s*
				#    6
				# extract the header,footer line:
				([^\x7C]+)
				#   7
				(?>
					# see whether there is a class flag:
					\x7C\s*
					(h|s)?.*
					# 8
				)?
			)?
		# Anchored to make sure everything gets matched:
		$/xiu

			Since compiling and applying a free form RegEx slows down the
			overall matching process I've folded it all to a standard RegEx.
			Benchmarking during development gave me
			free form:	20480 loops, 552960 hits, 102400 fails, 12.994689 secs
			standard:	20480 loops, 552960 hits, 102400 fails, 8.357169 secs
		*/
		if (preg_match('/^\s*(?=\S)(?>(?>(diff)(?>\s+([cnrsu]?))?)|'
			. '(?>([a-z][^\x7C\s]*)(?>\s+(\d\d*))?)|(\d\d*)|\s*)[^\x7C]*'
			. '(?>\x7C([bfht])?\s*([^\x7C]+)(?>\x7C\s*(h|s)?.*)?)?$/iu',
		$aMatch[0], $hits)) {
			unset($hits[0]);	// free mem
			// $hits[1] = "diff"
			// $hits[2] = type	(of [1])
			// $hits[3] = LANG
			// $hits[4] = NUM	(of [3])
			// $hits[5] = NUM	(alone)
			// $hits[6] = Top/Bottom flag	(of [7])
			// $hits[7] = TITLE
			// $hits[8] = s/h CSS flag
			if (isset($hits[3]) && ($hits[3])) {
				$l = strtolower($hits[3]);
				if (isset($hits[4]) && ($hits[4])) {
					$n = (int)$hits[4];
				} // if
				$hits[3] = $hits[4] = FALSE;
			} else if (isset($hits[1]) && ($hits[1])) {
				$l = strtolower($hits[1]);
				$hits[2] = (isset($hits[2]))
					? strtolower($hits[2]) . '?'
					: '?';
				$n = $hits[2]{0};
				$hits[1] = $hits[2] = FALSE;
			} else if (isset($hits[5]) && ($hits[5])) {
				$n = (int)$hits[5];
			} // if
			if (isset($hits[7]) && ($hits[7])) {
				$hits[6] = (isset($hits[6]))
					? strtolower($hits[6]) . 'f'
					: 'f';
				switch ($hits[6]{0}) {
					case 'h':
					case 't':
						$ht = trim($hits[7]);
						break;
					default:
						$ft = trim($hits[7]);
						break;
				} // switch
				if (isset($hits[8])) {
					$hits[8] = strtolower($hits[8]) . 's';
					if ('h' == $hits[8]{0}) {
						// This class is handled by JavaScript
						// (there _must_not_ be any CSS rules for this):
						$css = ' HideOnInit';
					} // if
				} // if
				$hits[6] = $hits[7] = $hits[8] = FALSE;
			} // if
		// ELSE: no arguments given to CODE tag
		} // if
		switch ($l) {
			case 'diff':
				if ("\n" != $aMatch[1]{0}) {
					// A leading LF is needed to recognize and handle
					// the very first line with all the REs used.
					$aMatch[1] = "\n" . $aMatch[1];
				} // if
				switch ($n) {
					case 'u':	// DIFF cmdline switch for 'unified'
					case 'c':	// DIFF cmdline switch for 'context'
					case 'n':	// DIFF cmdline switch for 'RCS'
					case 's':
						// We believe the format hint ...
						// (or should we be more suspicious?)
						break;
					case 'r':	// Mnemonic for 'RCS'
						$n = 'n';
						break;
					default:	// try to figure out the format actually used
						if (preg_match(
							'|\n(?:\x2A{5,}\n\x2A{3}\s[1-9]+.*?\x2A{4}\n.+?)+|s',
							$aMatch[1])) {
							$n = 'c';
						} else if (preg_match(
							'|\n@@\s\-[0-9]+,[0-9]+[ \+,0-9]+?@@\n.+\n|s',
							$aMatch[1])) {
							$n = 'u';
						} else if (preg_match(
							'|\n[ad][0-9]+\s+[0-9]+\r?\n|', $aMatch[1])) {
							// We've to check this _before_ "simple" since the
							// REs are similar (this is slightly more specific)
							$n = 'n';
						} else if (preg_match(
							'|\n(?:[0-9a-z]+(?:,[0-9a-z]+)*)(?:[^\n]*\n.*?)+|',
							$aMatch[1])) {
							$n = 's';
						} else {
							$n = '?';
						} // if
						break;
				} // switch
				syntax_plugin_code::_entities($aMatch[1]);
				break;
			case 'html':
				$l = 'html4strict';
				break;
			case 'sh':
				$l = 'bash';
				break;
			default:
				if (! $l) {
					// no language: simple PRE markup will get generated
					syntax_plugin_code::_entities($aMatch[1]);
					$l = FALSE;
				} // if
				break;
		} // switch
		return array(DOKU_LEXER_UNMATCHED, $aMatch[1], $l, $n, $ht, $ft, $css);
	} // handle()

	/**
	 * Add exit pattern to lexer.
	 *
	 * @public
	 */
	function postConnect() {
		// look-before to minimize the chance of false matches:
		$this->Lexer->addExitPattern('(?<=\n)\x3C\x2Fcode\x3E', 'plugin_code');
	} // postConnect()

	/**
	 * Handle the actual output creation.
	 *
	 * <p>
	 * The method checks for the given <tt>$aFormat</tt> and returns
	 * <tt>FALSE</tt> when a format isn't supported. <tt>$aRenderer</tt>
	 * contains a reference to the renderer object which is currently
	 * handling the rendering. The contents of <tt>$aData</tt> is the
	 * return value of the <tt>handle()</tt> method.
	 * </p>
	 * @param $aFormat String The output format to generate.
	 * @param $aRenderer Object A reference to the renderer object.
	 * @param $aData Array The data created/returned by the
	 * <tt>handle()</tt> method.
	 * @return Boolean <tt>TRUE</tt> if rendered successfully, or
	 * <tt>FALSE</tt> otherwise.
	 * @public
	 * @see handle()
	 */
	function render($aFormat, &$aRenderer, &$aData) {
		if (DOKU_LEXER_UNMATCHED == $aData[0]) {
			if ('xhtml' != $aFormat) {
				return FALSE;
			} // if
		} else {
			return TRUE;
		} // if
		global $conf;
		if (! $this->_sepChar) {
			$this->_sepChar = (isset($conf['sepchar']) && ($conf['sepchar']))
				? $conf['sepchar']{0}
				: '_';
		} // if
		if ($tdiv = (($aData[4]) || ($aData[5]))) {
			$this->_fixJS($aRenderer);	// check for old DokuWiki versions
			$aRenderer->doc .= '<div class="code">';
			if ($aData[4]) {
				$aRenderer->doc .= '<p class="codehead' . $aData[6]
					. '"><a name="' . $this->_makeID($aData[4]) . '">'
					. $this->_entities($aData[4]) . '</a></p>';
				$aData[4] = $aData[6] = FALSE;	// free mem
			} // if
		} // if
		if ($aData[2]) {	// lang was given
			if ('diff' == $aData[2]) {
				$aRenderer->doc .= '<pre class="code diff">';
				$this->_addDiff($aData[1], $aData[3], $aRenderer->doc);
				$aRenderer->doc .= '</pre>';
			} else {
				$isSH = ('bash' == $aData[2]);
				$geshi = new GeSHi($aData[1], $aData[2], GESHI_LANG_ROOT);
				if ($geshi->error()) {
					// Language not supported by "GeSHi"
					$geshi = NULL;	// free mem
					$this->_rawMarkup($aData[1], $aData[3], $aRenderer->doc);
				} else {
					$aData[1] = FALSE;	// free mem
					$geshi->enable_classes();
					$geshi->set_encoding('utf-8');
					$geshi->set_header_type(GESHI_HEADER_PRE);
					$geshi->set_overall_class('code ' . $aData[2]);
					if ($conf['target']['extern']) {
						$geshi->set_link_target($conf['target']['extern']);
					} // if
					if ($aData[3]) {		// line numbers requested
						// Separate PRE tag from parsed data:
						$aData[1] = explode('>', $geshi->parse_code(), 2);
						// [1][0] =	leading "<pre"
						// [1][1] =	remaining markup up to trailing "/<pre"
						$geshi = NULL;	// free mem

						// Add the open tag to the document:
						$aRenderer->doc .= $aData[1][0] . '>';

						// Separate trailing PRE tag:
						$aData[1] = explode('</pre>', $aData[1][1], 2);
						// [1][0] =	GeSHi markup
						// [1][1] =	trailing "</pre"

						if ($isSH) {
							$aData[1][1] = '';
							$this->_fixGeSHi_Bash($aData[1][0], $aData[1][1]);
						} else {
							// Set reference to fixed markup to sync with
							// the "bash" execution path (above):
							$aData[1][1] =& $aData[1][0];
						} // if

						// Split the parsed data into a list of lines:
						$aData[2] = explode("\n", $aData[1][1]);
						$aData[1] = FALSE; // free mem

						// Add the numbered lines to the document:
						$this->_addLines($aData[2], $aData[3], $aRenderer->doc);

						// Close the preformatted section markup:
						$aRenderer->doc .= '</pre>';
					} else {				// w/o line numbering
						if ($isSH) {
							// Separate trailing PRE tag which
							// sometimes is "forgotten" by GeSHi:
							$aData[2] = explode('</pre>', $geshi->parse_code(), 2);
							// [1][0] =	GeSHi markup
							// [1][1] =	trailing "</pre" (if any)
							$this->_fixGeSHi_Bash($aData[2][0], $aRenderer->doc);
							$aRenderer->doc .= '</pre>';
						} else {
							$aRenderer->doc .= $geshi->parse_code();
						} // if
						$geshi = NULL;	// free mem
					} // if
				} // if
			} // if
			$aData[2] = FALSE;	// free mem
		} else {
			$this->_rawMarkup($aData[1], $aData[3], $aRenderer->doc);
		} // if
		if ($tdiv) {
			if ($aData[5]) {
				$aRenderer->doc .= '<p class="codefoot' . $aData[6]
					. '"><a name="' . $this->_makeID($aData[5]) . '">'
					. $this->_entities($aData[5]) . '</a></p>';
				$aData[5] = $aData[6] = FALSE;	// free mem
			} // if
			$aRenderer->doc .= '</div>';
		} // if
		$aData[0] = FALSE;
		return TRUE;
	} // render()

	//@}
} // class syntax_plugin_code
} // if
?>
