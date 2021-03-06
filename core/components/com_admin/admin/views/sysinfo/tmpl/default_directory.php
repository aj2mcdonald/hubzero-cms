<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_HZEXEC_') or die();
?>
<fieldset class="adminform">
	<legend><?php echo Lang::txt('COM_ADMIN_DIRECTORY_PERMISSIONS'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="650">
						<?php echo Lang::txt('COM_ADMIN_DIRECTORY'); ?>
					</th>
					<th>
						<?php echo Lang::txt('COM_ADMIN_STATUS'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&#160;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->directory as $dir => $info):?>
					<tr>
						<td>
							<?php echo Html::directory('message', $dir, $info['message']); ?>
						</td>
						<td>
							<?php echo Html::directory('writable', $info['writable']); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
</fieldset>
