<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

//tag editor
JPluginHelper::importPlugin( 'hubzero' );
$dispatcher =& JDispatcher::getInstance();
$tf = $dispatcher->trigger( 'onGetMultiEntry', array(array('tags', 'tags', 'actags','', $this->tags)) );

$type = strtolower(JRequest::getWord('type', $this->entry->type));
if (!in_array($type, array('file', 'image', 'text', 'link')))
{
	$type = 'file';
}
//ximport('Hubzero_Wiki_Editor');
//$editor =& Hubzero_Wiki_Editor::getInstance();
?>
<ul id="page_options">
	<li>
		<a class="board btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&gid=' . $this->group->get('cn') . '&active=' . $this->name . '&scope=boards'); ?>">
			<?php echo JText::_('Boards'); ?>
		</a>
	</li>
</ul>

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>
<form action="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->get('cn').'&active=' . $this->name . 'scope=posts/save'); ?>" method="post" id="hubForm" class="full" enctype="multipart/form-data">
	<fieldset>
		<legend><?php echo JText::_('New post'); ?></legend>

		<ul class="post-type">
			<li class="post-image">
				<a class="tooltips<?php if ($type == 'image') { echo ' active'; } ?>" href="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->get('cn').'&active=' . $this->name . '&scope=posts/new&type=image'); ?>" rel="post-image" title="Post an image">Image</a>
			</li>
			<li class="post-file">
				<a class="tooltips<?php if ($type == 'file') { echo ' active'; } ?>" href="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->get('cn').'&active=' . $this->name . '&scope=posts/new&type=file'); ?>" rel="post-file" title="Post a file">File</a>
			</li>
			<li class="post-text">
				<a class="tooltips<?php if ($type == 'text') { echo ' active'; } ?>" href="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->get('cn').'&active=' . $this->name . '&scope=posts/new&type=text'); ?>" rel="post-text" title="Post some text">Text</a>
			</li>
			<li class="post-link">
				<a class="tooltips<?php if ($type == 'link') { echo ' active'; } ?>" href="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->get('cn').'&active=' . $this->name . '&scope=posts/new&type=link'); ?>" rel="post-link" title="Post a link">Link</a>
			</li>
		</ul>

		<div id="post-type-form">
<?php
		$view = new Hubzero_Plugin_View(
			array(
				'folder'  => 'groups',
				'element' => $this->name,
				'name'    => 'edit',
				'layout'  => '_' . $type
			)
		);
		$view->name       = $this->name;
		$view->option     = $this->option;
		$view->group      = $this->group;
		$view->params     = $this->params;
		$view->task       = $this->task;
		//$view->authorized = $this->authorized;

		$view->entry = $this->entry;
		$view->board = $this->board;

		$view->display();
?>
		</div>

		<!-- 
				<label for="field-access">
					<?php echo JText::_('Privacy'); ?>
					<select name="fields[access]" id="field-access">
						<option value="0"<?php if ($this->entry->access == 0) { echo ' selected="selected"'; } ?>><?php echo JText::_('Public (can be reposted to any board)'); ?></option>
						<option value="4"<?php if ($this->entry->access == 4) { echo ' selected="selected"'; } ?>><?php echo JText::_('Private (can only be reposted to this group\'s boards)'); ?></option>
					</select>
				</label>
		 -->

		<div class="group">
		<label for="field-board">
			<?php echo JText::_('Board'); ?>
			<select name="fields[board_id]" id="field-board">
<?php 
if ($this->boards)
{
	foreach ($this->boards as $board)
	{
?>
				<option value="<?php echo $this->escape($board->id); ?>"<?php if ($this->entry->id == $board->id) { echo ' selected="selected"'; } ?>><?php echo $this->escape(stripslashes($board->title)); ?></option>
<?php
	}
}
?>
			</select>
		</label>

		<label>
			<?php echo JText::_('PLG_GROUPS_' . strtoupper($this->name) . '_FIELD_TAGS'); ?>
			<?php if (count($tf) > 0) {
				echo $tf[0];
			} else { ?>
				<input type="text" name="tags" value="<?php echo $this->tags; ?>" />
			<?php } ?>
			<span class="hint"><?php echo JText::_('PLG_GROUPS_' . strtoupper($this->name) . '_FIELD_TAGS_HINT'); ?></span>
		</label>
		</div>
		<div class="clear"></div>
	</fieldset>

	<input type="hidden" name="fields[id]" value="<?php echo $this->entry->id; ?>" />
	<input type="hidden" name="fields[created]" value="<?php echo $this->entry->created; ?>" />
	<input type="hidden" name="fields[created_by]" value="<?php echo $this->entry->created_by; ?>" />

	<input type="hidden" name="gid" value="<?php echo $this->group->get('cn'); ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="active" value="<?php echo $this->name; ?>" />
	<input type="hidden" name="task" value="save" />
		
	<p class="submit">
		<input type="submit" value="<?php echo JText::_('PLG_GROUPS_' . strtoupper($this->name) . '_SAVE'); ?>" />
	</p>
</form>
