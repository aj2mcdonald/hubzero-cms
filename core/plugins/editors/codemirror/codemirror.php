<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * CodeMirror Editor Plugin.
 */
class plgEditorCodemirror extends \Hubzero\Plugin\Plugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'media/editors/codemirror/';

	/**
	 * Initialises the Editor.
	 *
	 * @return	string	JavaScript Initialization string.
	 */
	public function onInit()
	{
		Html::behavior('framework');

		$uncompressed = Config::get('debug') ? '-uncompressed' : '';

		//Html::asset('script', $this->_basePath . 'js/codemirror'.$uncompressed.'.js', false, false, false, false);
		//Html::asset('stylesheet', $this->_basePath . 'css/codemirror.css');
		$this->js('codemirror'.$uncompressed.'.js');
		$this->css('codemirror.css');

		return '';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param	string	$id	The id of the editor field.
	 *
	 * @return string Javascript
	 */
	public function onSave($id)
	{
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getCode();\n";
	}

	/**
	 * Get the editor content.
	 *
	 * @param	string	$id	The id of the editor field.
	 *
	 * @return string Javascript
	 */
	public function onGetContent($id)
	{
		return "Joomla.editors.instances['$id'].getCode();\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param	string	$id			The id of the editor field.
	 * @param	string	$content	The content to set.
	 *
	 * @return string Javascript
	 */
	public function onSetContent($id, $content)
	{
		return "Joomla.editors.instances['$id'].setCode($content);\n";
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return boolean
	 */
	public function onGetInsertMethod()
	{
		static $done = false;

		// Do this only once.
		if (!$done)
		{
			$done = true;

			$js = "\tfunction jInsertEditorText(text, editor) {
					Joomla.editors.instances[editor].replaceSelection(text);\n
			}";
			Document::addScriptDeclaration($js);
		}

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $html     The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   int      $col      The number of columns for the textarea.
	 * @param   int      $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset
	 * @param   object   $author
	 * @param   array    $params   Associative array of editor parameters.
	 * @return  string HTML
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id))
		{
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width))
		{
			$width .= 'px';
		}

		if (is_numeric($height))
		{
			$height .= 'px';
		}

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);

		$compressed	= Config::get('debug') ? '-uncompressed' : '';

		// Default syntax
		$parserFile = 'parsexml.js';
		$styleSheet = array('xmlcolors.css');

		// Look if we need special syntax coloring.
		$syntax = User::getState('editor.source.syntax');

		if ($syntax) {
			switch ($syntax)
			{
				case 'css':
					$parserFile = 'parsecss.js';
					$styleSheet = array('csscolors.css');
					break;

				case 'js':
					$parserFile = array('tokenizejavascript.js', 'parsejavascript.js');
					$styleSheet = array('jscolors.css');
					break;

				case 'html':
					$parserFile = array('parsexml.js', 'parsecss.js', 'tokenizejavascript.js', 'parsejavascript.js', 'parsehtmlmixed.js');
					$styleSheet = array('xmlcolors.css', 'jscolors.css', 'csscolors.css');
					break;

				case 'php':
					$parserFile = array('parsexml.js', 'parsecss.js', 'tokenizejavascript.js', 'parsejavascript.js', 'tokenizephp.js', 'parsephp.js', 'parsephphtmlmixed.js');
					$styleSheet = array('xmlcolors.css', 'jscolors.css', 'csscolors.css', 'phpcolors.css');
					break;

				default:
					;
					break;
			} //switch
		}

		foreach ($styleSheet as &$style)
		{
			$style = Request::root(true).'/'.$this->_basePath.'css/'.$style;
		}

		$options = new stdClass;

		$options->basefiles  = array('basefiles'.$compressed.'.js');
		$options->path       = Request::root(true).'/'.$this->_basePath.'js/';
		$options->parserfile = $parserFile;
		$options->stylesheet = $styleSheet;
		$options->height     = $height;
		$options->width      = $width;
		$options->continuousScanning = 500;

		if ($this->params->get('linenumbers', 0))
		{
			$options->lineNumbers  = true;
			$options->textWrapping = false;
		}

		if ($this->params->get('tabmode', '') == 'shift')
		{
			$options->tabMode = 'shift';
		}

		$html = array();
		$html[]	= "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function() {';
		$html[] = 'var editor = CodeMirror.fromTextArea("'.$id.'", '.json_encode($options).');';
		$html[] = 'Joomla.editors.instances[\''.$id.'\'] = editor;';
		$html[] = '})()';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param string $name
	 * @param mixed $buttons [array with button objects | boolean true to display buttons]
	 *
	 * @return string HTML
	 */
	protected function _displayButtons($name, $buttons, $asset, $author)
	{
		// Load modal popup behavior
		Html::behavior('modal', 'a.modal-button');

		$html = array();
		$results[] = $this->onGetInsertMethod($name);

		foreach ($results as $result)
		{
			if (is_string($result) && trim($result))
			{
				$html[] = $result;
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$html[] = '<div id="editor-xtd-buttons">';

			foreach ($results as $button)
			{
				// Results should be an object
				if ($button->get('name'))
				{
					$modal   = ($button->get('modal')) ? 'class="modal-button"' : null;
					$href    = ($button->get('link')) ? 'href="'.Request::base().$button->get('link').'"' : null;
					$onclick = ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$title   = ($button->get('title')) ? $button->get('title') : $button->get('text');
					$html[] = '<div class="button2-left"><div class="'.$button->get('name').'">';
					$html[] = '<a '.$modal.' title="'.$title.'" '.$href.' '.$onclick.' rel="'.$button->get('options').'">';
					$html[] = $button->get('text').'</a></div></div>';
				}
			}

			$html[] = '</div>';
		}

		return implode("\n", $html);
	}
}