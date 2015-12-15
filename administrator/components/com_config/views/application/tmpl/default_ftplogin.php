<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-100">
	<fieldset title="<?php echo JText::_('COM_CONFIG_FTP_DETAILS'); ?>" class="adminform">
		<legend><span><?php echo JText::_('COM_CONFIG_FTP_DETAILS'); ?></span></legend>
		<?php echo JText::_('COM_CONFIG_FTP_DETAILS_TIP'); ?>

		<?php if ($this->ftp instanceof Exception): ?>
			<p><?php echo JText::_($this->ftp->message); ?></p>
		<?php endif; ?>

		<div class="input-wrap">
			<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
			<input type="text" id="username" name="username" class="input_box" size="70" value="" />
		</div>

		<div class="input-wrap">
			<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
			<input type="password" id="password" name="password" class="input_box" size="70" value="" />
		</div>
	</fieldset>
</div>