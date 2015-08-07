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
 * @author    Kevin Wojkovich <kevinw@purdue.edu>
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

$this->js('https://maps.googleapis.com/maps/api/js?v=3.exp');
$this->js("geosearch.jquery.js");
$this->js('oms.min.js');
$this->css('geosearch.css');

?>

<div id="content-header" class="full">
	<h2><?php echo Lang::txt('COM_GEOSEARCH_TITLE'); ?></h2>
</div>

<!--- .main .section -->
<div class="main section">
	<form action="<?php echo Route::url('index.php?option=' . $this->option); ?>" method="get" id="frm_search">
	<!-- page errors -->
	<?php if ($this->getError()): ?>
		<p class="error"><?php echo implode("\n", $this->getErrors()); ?></p>
	<?php endif; ?>

<div class="aside geosearch">
	<div class="container">
		<h3><?php echo Lang::txt('COM_GEOSEARCH_FILTER'); ?></h3>
	<fieldset>
		<legend><?php echo Lang::txt('COM_GEOSEARCH_LIM_RES'); ?></legend>

		<div class="key">
			<img src="<?php echo $this->img('icn_member2.png'); ?>">
			<input type="checkbox" name="resource[]" class="resck" value="member" checked /> Members
		</div>

		<div class="key">
			<img src="<?php echo $this->img('icn_job2.png'); ?>" />
			<input type="checkbox" name="resource[]" class="resck" value="job" checked /> Jobs
		</div>

		<div class="key">
			<img src="<?php echo $this->img('icn_event2.png'); ?>" />
			<input type="checkbox" name="resource[]" class="resck" value="event" checked /> Events
		</div>

		<div class="key">
			<img src="<?php echo $this->img('icn_org2.png'); ?>" />
			<input type="checkbox" name="resource[]" class="resck" value="organization" checked/> Organizations
		</div>

		<div class="clear-right"></div>
	</fieldset>

	</div><!-- / .container -->
</div><!-- / .aside -->

<div class="subject">
	<div id="map_container">
		<div id="map_canvas"></div>
	</div> <!-- / #map_container -->
</div> <!-- / .subject -->