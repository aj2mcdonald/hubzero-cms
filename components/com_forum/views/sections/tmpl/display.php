<?php 
defined('_JEXEC') or die( 'Restricted access' );
$juser = JFactory::getUser();
?>
<div id="content-header" class="full">
	<h2><?php echo JText::_('COM_FORUM'); ?></h2>
</div>

<?php foreach ($this->notifications as $notification) { ?>
<p class="<?php echo $notification['type']; ?>"><?php echo $this->escape($notification['message']); ?></p>
<?php } ?>

<div class="main section">
	<div class="aside">
		<div class="container">
			<h3><?php echo JText::_('Statistics'); ?><span class="starter-point"></span></h3>
			<table summary="<?php echo JText::_('Statistics'); ?>">
				<tbody>
					<tr>
						<th><?php echo JText::_('Categories'); ?></th>
						<td><?php echo $this->stats->categories; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('Discussions'); ?></th>
						<td><?php echo $this->stats->threads; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('Posts'); ?></th>
						<td><?php echo $this->stats->posts; ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="container">
			<h3><?php echo JText::_('Last Post'); ?><span class="starter-point"></span></h3>
			<p>
<?php
			if (is_object($this->lastpost)) 
			{
				$lname = JText::_('Anonymous');
				$lastposter = JUser::getInstance($this->lastpost->created_by);
				if (is_object($lastposter)) 
				{
					$lname = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $lastposter->get('id')) . '">' . $this->escape(stripslashes($lastposter->get('name'))) . '</a>';
				}
?>
				<span class="entry-date">
					@
					<span class="time"><?php echo JHTML::_('date', $this->lastpost->created, '%I:%M %p', 0); ?></span> <?php echo JText::_('COM_FORUM_ON'); ?> 
					<span class="date"><?php echo JHTML::_('date', $this->lastpost->created, '%d %b, %Y', 0); ?></span>
				</span>
				<span class="entry-author">
					<?php echo JText::_('by'); ?>
					<?php echo $lname; ?>
				</span>
<?php } else { ?>
				<?php echo JText::_('none'); ?>
<?php } ?>
			</p>
		</div>
		
<?php if ($this->config->get('access-create-section')) { ?>
		<div class="container">
			<h3><?php echo JText::_('Sections'); ?></h3>
			<p class="starter">
				<span class="starter-point"></span>
				<?php echo JText::_('Use sections to group related categories.'); ?>
			</p>
		</div>
		
		<form action="<?php echo JRoute::_('index.php?option=' . $this->option); ?>" method="post">
			<fieldset>
				<legend><?php echo JText::_('New Section'); ?></legend>
				<label for="field-title">
					<?php echo JText::_('Section Title'); ?>
					<input type="text" name="fields[title]" id="field-title" value="" />
				</label>
				<input type="submit" value="<?php echo JText::_('Create'); ?>" />
				<input type="hidden" name="task" value="savesection" />
				<input type="hidden" name="group_id" value="0" />
			</fieldset>
		</form>
<?php } ?>
	</div><!-- / .aside -->

	<div class="subject">
	<form action="<?php echo JRoute::_('index.php?option=' . $this->option); ?>" method="post">
		<div class="container data-entry">
			<input class="entry-search-submit" type="submit" value="<?php echo JText::_('Search'); ?>" />
			<fieldset class="entry-search">
				<legend><?php echo JText::_('Search categories'); ?></legend>				
				<label for="entry-search-field"><?php echo JText::_('Enter keyword or phrase'); ?></label>
				<input type="text" name="q" id="entry-search-field" value="<?php echo $this->escape($this->filters['search']); ?>" />
				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="controller" value="categories" />
				<input type="hidden" name="task" value="search" />
			</fieldset>
		</div><!-- / .container -->
<?php
foreach ($this->sections as $section)
{
	if ($section->id == 0 && !$section->categories) 
	{
		continue;
	}
?>
		<div class="container" id="section-<?php echo $section->id; ?>">
			<table class="entries categories">
				<caption>
<?php if ($this->config->get('access-edit-section') && $this->edit == $section->alias && $section->id) { ?>
					<a name="s<?php echo $section->id; ?>"></a>
					<input type="text" name="fields[title]" value="<?php echo $this->escape(stripslashes($section->title)); ?>" />
					<input type="submit" value="<?php echo JText::_('Save'); ?>" />
					<input type="hidden" name="fields[id]" value="<?php echo $section->id; ?>" />
					<input type="hidden" name="fields[group_id]" value="0" />
					<input type="hidden" name="controller" value="sections" />
					<input type="hidden" name="task" value="save" />
<?php } else { ?>
					<?php echo $this->escape(stripslashes($section->title)); ?>
<?php } ?>
			<?php if (($this->config->get('access-edit-section') || $this->config->get('access-delete-section')) && $section->id) { ?>
				<?php if ($this->config->get('access-delete-section')) { ?>
					<a class="delete" href="<?php echo JRoute::_('index.php?option='.$this->option . '&section=' . $section->alias . '&task=delete'); ?>" title="<?php echo JText::_('Delete'); ?>">
						<span><?php echo JText::_('Delete'); ?></span>
					</a>
				<?php } ?>
				<?php if ($this->config->get('access-edit-section') && $this->edit != $section->alias && $section->id) { ?>
					<a class="edit" href="<?php echo JRoute::_('index.php?option='.$this->option . '&section=' . $section->alias . '&task=edit#s' . $section->id); ?>" title="<?php echo JText::_('Edit'); ?>">
						<span><?php echo JText::_('Edit'); ?></span>
					</a>
				<?php } ?>
			<?php } ?>
				</caption>
<?php if ($this->config->get('access-create-category')) { ?>
				<tfoot>
					<tr>
						<td<?php if ($section->categories) { echo ' colspan="5"'; } ?>>
							<a class="add" id="addto-<?php echo $section->id; ?>" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $section->alias . '&task=new'); ?>">
								<span><?php echo JText::_('Add Category'); ?></span>
							</a>
						</td>
					</tr>
				</tfoot>
<?php } ?>
				<tbody>
<?php 
if ($section->categories) { 
		foreach ($section->categories as $row) 
		{ 
?>
					<tr<?php if ($row->closed) { echo ' class="closed"'; } ?>>
						<th scope="row">
							<span class="entry-id"><?php echo $this->escape($row->id); ?></span>
						</th>
						<td>
							<a class="entry-title" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $section->alias . '&category=' . $row->alias); ?>">
								<span><?php echo $this->escape(stripslashes($row->title)); ?></span>
							</a>
							<span class="entry-details">
								<span class="entry-description">
									<?php echo $this->escape(stripslashes($row->description)); ?>
								</span>
							</span>
						</td>
						<td>
							<span><?php echo $row->threads; ?></span>
							<span class="entry-details">
								<?php echo JText::_('Discussions'); ?>
							</span>
						</td>
						<td>
							<span><?php echo $row->posts; ?></span>
							<span class="entry-details">
								<?php echo JText::_('Posts'); ?>
							</span>
						</td>
<?php 			if ($this->config->get('access-edit-category') || $this->config->get('access-delete-categort')) { ?>
						<td class="entry-options">
							<?php if (($row->created_by == $juser->get('id') || $this->config->get('access-edit-category')) && $section->id) { ?>
								<a class="edit" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $section->alias . '&category=' . $row->alias . '&task=edit'); ?>" title="<?php echo JText::_('Edit'); ?>">
									<span><?php echo JText::_('Edit'); ?></span>
								</a>
							<?php } ?>
							<?php if ($this->config->get('access-delete-category') && $section->id) { ?>
								<a class="delete tooltips" title="<?php echo JText::_('COM_FORUM_DELETE_CATEGORY'); ?>" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $section->alias . '&category=' . $row->alias . '&task=delete'); ?>" title="<?php echo JText::_('Delete'); ?>">
									<span><?php echo JText::_('Delete'); ?></span>
								</a>
							<?php } ?>
						</td>
<?php 			} ?>
					</tr>
<?php 
		}
	} else { 
?>
					<tr>
						<td><?php echo JText::_('There are no categories.'); ?></td>
					</tr>
<?php } ?>
				</tbody>
			</table>
		</div>
<?php
}
?>
	</form>
	</div><!-- /.subject -->
</div><!-- /.main -->