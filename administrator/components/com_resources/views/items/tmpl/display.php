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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$canDo = ResourcesHelperPermissions::getActions('resource');

JToolBarHelper::title(JText::_('Resource Manager'), 'resources.png');
if ($canDo->get('core.admin')) 
{
	JToolBarHelper::custom('check', 'scan', '', 'Check Paths', false);
	JToolBarHelper::spacer();
	JToolBarHelper::preferences($this->option, '550');
	JToolBarHelper::spacer();
}
if ($canDo->get('core.create')) 
{
	JToolBarHelper::addNew('addchild', 'Add Child');
	JToolBarHelper::spacer();
}
if ($canDo->get('core.edit.state')) 
{
	JToolBarHelper::publishList();
	JToolBarHelper::unpublishList();
	JToolBarHelper::spacer();
}
if ($canDo->get('core.create')) 
{
	JToolBarHelper::addNew();
}
if ($canDo->get('core.edit')) 
{
	JToolBarHelper::editList();
}
if ($canDo->get('core.delete')) 
{
	JToolBarHelper::deleteList();
}

JHTML::_('behavior.tooltip');
//jimport('joomla.html.html.grid');
include_once(JPATH_ROOT . DS . 'libraries' . DS . 'joomla' . DS . 'html' . DS . 'html' . DS . 'grid.php');
?>
<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
	// do field validation
	submitform( pressbutton );
}
</script>

<form action="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<label for="filter_search"><?php echo JText::_('Search'); ?>: </label>
		<input type="text" name="search" id="filter_search" value="<?php echo $this->filters['search']; ?>" />

		<label for="status"><?php echo JText::_('Status'); ?>:</label>
		<select name="status" id="status">
			<option value="all"<?php echo ($this->filters['status'] == 'all') ? ' selected="selected"' : ''; ?>><?php echo JText::_('[ all ]'); ?></option>
			<option value="2"<?php echo ($this->filters['status'] == 2) ? ' selected="selected"' : ''; ?>><?php echo JText::_('Draft (user created)'); ?></option>
			<option value="5"<?php echo ($this->filters['status'] == 5) ? ' selected="selected"' : ''; ?>><?php echo JText::_('Draft (internal)'); ?></option>
			<option value="3"<?php echo ($this->filters['status'] == 3) ? ' selected="selected"' : ''; ?>><?php echo JText::_('Pending'); ?></option>
			<option value="0"<?php echo ($this->filters['status'] == 0 && $this->filters['status'] != 'all') ? ' selected="selected"' : ''; ?>><?php echo JText::_('Unpublished'); ?></option>
			<option value="1"<?php echo ($this->filters['status'] == 1) ? ' selected="selected"' : ''; ?>><?php echo JText::_('Published'); ?></option>
			<option value="4"<?php echo ($this->filters['status'] == 4) ? ' selected="selected"' : ''; ?>><?php echo JText::_('Deleted'); ?></option>
		</select>

		<label for="type"><?php echo JText::_('Type'); ?>:</label>
		<?php echo ResourcesHtml::selectType($this->types, 'type', $this->filters['type'], '[ all types ]', '', '', ''); ?>

		<input type="submit" name="filter_submit" id="filter_submit" value="<?php echo JText::_('Go'); ?>" />
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo ($this->rows) ? count( $this->rows ) : 0;?>);" /></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('ID'), 'id', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('Title'), 'title', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('Status'), 'published', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('Access'), 'access', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('Modified'), 'modified', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JText::_('License'); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('Type'), 'type', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('Children'), 'children', @$this->filters['sort_Dir'], @$this->filters['sort'] ); ?></th>
				<th><?php echo JText::_('Tags'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
			<tr>
				<td colspan="9">
					<p><?php echo JText::_('Published status: (click icon above to toggle state)'); ?></p>
					<ul class="key">
						<li class="draftinternal"><span>draft (internal)</span> = <?php echo JText::_('Draft (internal production)'); ?></li>
						<li class="draftexternal"><span>draft (external)</span> = <?php echo JText::_('Draft (user created)'); ?></li>
						<li class="submitted"><span>new</span> = <?php echo JText::_('New, awaiting approval'); ?></li>
						<li class="pending"><span>pending</span> = <?php echo JText::_('Published, but is Coming'); ?></li>
						<li class="published"><span>current</span> = <?php echo JText::_('Published and is Current'); ?></li>
						<li class="expired"><span>finished</span> = <?php echo JText::_('Published, but has Finished'); ?></li>
						<li class="unpublished"><span>unpublished</span> = <?php echo JText::_('Unpublished'); ?></li>
						<li class="deleted"><span>deleted</span> = <?php echo JText::_('Delete/Removed'); ?></li>
					</ul>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
$k = 0;
$filterstring  = ''; //($this->filters['sort'])   ? '&amp;sort='.$this->filters['sort']     : '';
//$filterstring .= '&amp;status='.$this->filters['status'];
//$filterstring .= ($this->filters['type'])   ? '&amp;type='.$this->filters['type']     : '';

$database = JFactory::getDBO();
$rt = new ResourcesTags($database);

$paramsClass = 'JParameter';
if (version_compare(JVERSION, '1.6', 'ge'))
{
	$paramsClass = 'JRegistry';
}

for ($i=0, $n=count($this->rows); $i < $n; $i++)
{
	$row =& $this->rows[$i];

	$rparams = new $paramsClass($row->params);
	$license = $rparams->get('license');

	// Build some publishing info
	$info  = JText::_('Created') . ': ' . JHTML::_('date', $row->created, JText::_('DATE_FORMAT_HZ1')) . '<br />';
	$info .= JText::_('Created by') . ': ' . $this->escape($row->created_by) . '<br />';
	
	// Get the published status
	$now = JFactory::getDate()->toSql();
	switch ($row->published)
	{
		case 0:
			$alt   = 'Unpublish';
			$class = 'unpublished';
			$task  = 'publish';
			break;
		case 1:
			if ($now <= $row->publish_up) {
				$alt   = 'Pending';
				$class = 'pending';
				$task  = 'unpublish';
			} else if ($now <= $row->publish_down || $row->publish_down == "0000-00-00 00:00:00") {
				$alt   = 'Published';
				$class = 'published';
				$task  = 'unpublish';
			} else if ($now > $row->publish_down) {
				$alt   = 'Expired';
				$class = 'expired';
				$task  = 'unpublish';
			}
			
			$info .= JText::_('Published').': '.JHTML::_('date', $row->publish_up, JText::_('DATE_FORMAT_HZ1')).'<br />';
			break;
		case 2:
			$alt   = 'Draft (user created)';
			$class = 'draftexternal';
			$task  = 'publish';
			break;
		case 3:
			$alt   = 'New';
			$class = 'submitted';
			$task  = 'publish';
			break;
		case 4:
			$alt   = 'Deleted';
			$class = 'deleted';
			$task  = 'publish';
			break;
		case 5:
			$alt   = 'Draft (internal production)';
			$class = 'draftinternal';
			$task  = 'publish';
			break;
		default:
			$alt   = '-';
			$class = '';
			$task  = '';
			break;
	}

	switch ($row->access)
	{
		case 0:
			$color_access = 'public';
			$task_access  = 'accessregistered';
			$row->groupname = 'Public';
			break;
		case 1:
			$color_access = 'registered';
			$task_access  = 'accessspecial';
			$row->groupname = 'Registered';
			break;
		case 2:
			$color_access = 'special';
			$task_access  = 'accessprotected';
			$row->groupname = 'Special';
			break;
		case 3:
			$color_access = 'protected';
			$task_access  = 'accessprivate';
			$row->groupname = 'Protected';
			break;
		case 4:
			$color_access = 'private';
			$task_access  = 'accesspublic';
			$row->groupname = 'Private';
			break;
	}

	// Get the tags on this item
	$tags = count($rt->getTags($row->id, 0, 0, 1));

	// See if it's checked out or not
	if ($row->checked_out || $row->checked_out_time != '0000-00-00 00:00:00')
	{
		//$checked = JHTMLGrid::_checkedOut($row);
		$date = JHtml::_('date', $row->checked_out_time, JText::_('DATE_FORMAT_LC1'));
		$time = JHtml::_('date', $row->checked_out_time, 'H:i');

		$checked  = '<span class="editlinktip hasTip" title="' . JText::_('JLIB_HTML_CHECKED_OUT') . '::' . $this->escape($row->editor) . '<br />' . $date . '<br />' . $time . '">';
		$checked .= JHtml::_('image', 'admin/checked_out.png', null, null, true) . '</span>';

		$info .= ($row->checked_out_time != '0000-00-00 00:00:00')
				 ? JText::_('Checked out').': '.JHTML::_('date', $row->checked_out_time, JText::_('DATE_FORMAT_HZ1')).'<br />'
				 : '';
		if ($row->editor)
		{
			$info .= JText::_('Checked out by') . ': ' . $this->escape($row->editor);
		}
	}
	else
	{
		$checked = JHTML::_('grid.id', $i, $row->id, false, 'id');
	}
?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php echo $row->id; ?>
				</td>
				<td>
				<?php if ($row->type == 7) { ?>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'Tool pages must be managed through the Tools Pipeline' );?>">
						<span><?php echo $this->escape(stripslashes($row->title)); ?></span>
					</span>
				<?php } else { ?>
					<a class="editlinktip hasTip" href="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=edit&amp;id[]=<?php echo $row->id;  echo $filterstring; ?>" title="<?php echo JText::_( 'Publish Information' );?>::<?php echo $info; ?>">
						<span><?php echo $this->escape(stripslashes($row->title)); ?></span>
					</a>
				<?php } ?>
				</td>
				<td>
					<a class="<?php echo $class;?> hasTip" href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=<?php echo $task; ?>&amp;id[]=<?php echo $row->id; echo $filterstring; ?>&amp;<?php echo JUtility::getToken(); ?>=1" title="<?php echo $alt; ?> :: Set this to <?php echo $task;?>">
						<span><?php echo $alt; ?></span>
					</a>
				</td>
				<td>
					<a class="access <?php echo $color_access; ?>" href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=<?php echo $task_access; ?>&amp;id=<?php echo $row->id; echo $filterstring; ?>&amp;<?php echo JUtility::getToken(); ?>=1" title="Change Access">
						<span><?php echo $this->escape($row->groupname); ?></span>
					</a>
				</td>
				<td style="white-space: nowrap">
					<?php if ($row->modified == '0000-00-00 00:00:00') { echo JText::_('Not modified'); } else { ?>
					<time datetime="<?php echo ($row->modified != '0000-00-00 00:00:00' ? $row->modified : $row->created); ?>">
						<?php echo JHTML::_('date', ($row->modified != '0000-00-00 00:00:00' ? $row->modified : $row->created), JText::_('DATE_FORMAT_HZ1')); //$this->escape($license); ?>
					</time>
					<?php } ?>
				</td>
				<td>
					<?php echo $this->escape(stripslashes($license)); ?>
				</td>
				<td style="white-space: nowrap">
					<?php echo $this->escape(stripslashes($row->typetitle)); ?>
				</td>
				<td style="white-space: nowrap">
					<?php if ($row->children > 0) { ?> 
						<a class="glyph menulist" href="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=children&amp;pid=<?php echo $row->id; ?>" title="View this item's children">
							<span><?php echo $row->children; ?></span>
						</a>
					<?php } else { ?> 
						<a class="state add" href="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=addchild&amp;pid=<?php echo $row->id; ?>" title="Add a child">
							<span>+ Add</span>
						</a>
					<?php } ?>
				</td>
				<td style="white-space: nowrap">
					<?php if ($tags > 0) { ?> 
						<a class="glyph tag" href="index.php?option=<?php echo $this->option; ?>&amp;controller=tags&amp;id=<?php echo $row->id; ?>" title="View this item's tags">
							<span><?php echo $tags; ?></span>
						</a>
					<?php } else { ?> 
						<a class="state add" href="index.php?option=<?php echo $this->option; ?>&amp;controller=tags&amp;id=<?php echo $row->id; ?>" title="Add a tag">
							<span>+ Add</span>
						</a>
					<?php } ?>
				</td>
			</tr>
<?php
	$k = 1 - $k;
}
?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->filters['sort']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filters['sort_Dir']; ?>" />

	<?php echo JHTML::_('form.token'); ?>
</form>
