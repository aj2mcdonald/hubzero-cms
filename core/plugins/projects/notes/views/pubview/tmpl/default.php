<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 HUBzero Foundation, LLC.
 * @license		http://opensource.org/licenses/MIT MIT
 *
 * Copyright 2005-2009 HUBzero Foundation, LLC.
 * All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
	 ->js();

$html = $this->revision->get('pagehtml');

// Com_wiki adds /projects - strip it out
$html = str_replace('projects/projects/', 'projects/', $html);

// Fix up images
$html = str_replace($this->page->get('scope') . DS . $this->page->get('pagename') , 'wiki/' . $this->page->get('id'), $html);

?>

<div class="wiki-wrap">
	<p class="wiki-back"><?php echo Lang::txt('PLG_PROJECTS_NOTES_PUBLIC_VIEW'); ?> <span class="goback"><a href="<?php echo $this->model->link(); ?>"><?php echo Lang::txt('PLG_PROJECTS_NOTES_BACK_TO_PROJECT'); ?></a></span></p>
	<div class="wiki-content">
		<h1 class="page-title"><?php echo $this->page->get('title'); ?></h1>
		<div class="wikipage"><?php echo $html; ?></div>
	</div>
</div>
