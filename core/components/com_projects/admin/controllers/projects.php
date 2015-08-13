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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Components\Projects\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Projects\Tables;
use Components\Projects\Models;
use Components\Projects\Helpers;

/**
 * Manage projects
 */
class Projects extends AdminController
{
	/**
	 * Executes a task
	 *
	 * @return     void
	 */
	public function execute()
	{
		// Publishing enabled?
		$this->_publishing = Plugin::isEnabled('projects', 'publications') ? 1 : 0;

		// Include scripts
		$this->_includeScripts();

		parent::execute();
	}

	/**
	 * Include necessary scripts
	 *
	 * @return     void
	 */
	protected function _includeScripts()
	{
		// Enable publication management
		if ($this->_publishing)
		{
			require_once(PATH_CORE . DS . 'components' . DS . 'com_publications'
				. DS . 'models' . DS . 'publication.php');
		}
	}

	/**
	 * Lists projects
	 *
	 * @return     void
	 */
	public function displayTask()
	{
		$this->view->config = $this->config;

		// Get quotas
		$this->view->defaultQuota = Helpers\Html::convertSize(floatval($this->config->get('defaultQuota', 1)), 'GB', 'b');
		$this->view->premiumQuota = Helpers\Html::convertSize(floatval($this->config->get('premiumQuota', 30)), 'GB', 'b');

		// Get filters
		$this->view->filters = array(
			'limit' => Request::getState(
				$this->_option . '.projects.limit',
				'limit',
				Config::get('list_limit'),
				'int'
			),
			'start' => Request::getState(
				$this->_option . '.projects.limitstart',
				'limitstart',
				0,
				'int'
			),
			'search' => urldecode(Request::getState(
				$this->_option . '.projects.search',
				'search',
				''
			)),
			'sortby' => Request::getState(
				$this->_option . '.projects.sort',
				'filter_order',
				'id'
			),
			'sortdir' => Request::getState(
				$this->_option . '.projects.sortdir',
				'filter_order_Dir',
				'DESC'
			),
			'authorized' => true,
			'getowner'   => 1,
			'activity'   => 1,
			'quota'      => Request::getVar('quota', 'all', 'post')
		);

		$this->view->limit = $this->view->filters['limit'];
		$this->view->start = $this->view->filters['start'];

		// Retrieve all records when filtering by quota (no paging)
		if ($this->view->filters['quota'] != 'all')
		{
			$this->view->filters['limit'] = 'all';
			$this->view->filters['start'] = 0;
		}

		$obj = new Tables\Project( $this->database );

		// Get records
		$this->view->rows = $obj->getRecords( $this->view->filters, true, 0, 1 );

		// Get a record count
		$this->view->total = $obj->getCount( $this->view->filters, true, 0, 1 );

		// Filtering by quota
		if ($this->view->filters['quota'] != 'all' && $this->view->rows)
		{
			$counter = $this->view->total;
			$rows = $this->view->rows;

			for ($i=0, $n=count( $rows ); $i < $n; $i++)
			{
				$params = new \Hubzero\Config\Registry( $rows[$i]->params );
				$quota = $params->get('quota', 0);
				if (($this->view->filters['quota'] == 'premium' && $quota < $this->view->premiumQuota )
					|| ($this->view->filters['quota'] == 'regular' && $quota > $this->view->defaultQuota))
				{
					$counter--;
					unset($rows[$i]);
				}
			}

			$rows = array_values($rows);
			$this->view->total = $counter > 0 ? $counter : 0;

			// Fix up paging after filter
			if (count($rows) > $this->view->limit)
			{
				$k = 0;

				for ($i=0, $n=count( $rows ); $i < $n; $i++)
				{
					if ($k < $this->view->start || $k >= ($this->view->limit + $this->view->start))
					{
						unset($rows[$i]);
					}

					$k++;
				}
			}

			$this->view->rows = array_values($rows);
		}

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError( $this->getError() );
		}

		// Check that master path is there
		if ($this->config->get('offroot') && !is_dir($this->config->get('webpath')))
		{
			$this->view->setError( Lang::txt('Master directory does not exist. Administrator must fix this! ') . $this->config->get('webpath') );
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Edit project info
	 *
	 * @return     void
	 */
	public function editTask()
	{
		// Incoming project ID
		$id = Request::getVar( 'id', array(0) );
		if (is_array( $id ))
		{
			$id = $id[0];
		}

		// Push some styles to the template
		\Hubzero\Document\Assets::addPluginStylesheet('projects', 'files', 'diskspace.css');
		\Hubzero\Document\Assets::addPluginScript('projects', 'files', 'diskspace.js');
		\Hubzero\Document\Assets::addPluginScript('projects', 'files');

		$this->view = $this->view;
		$this->view->config = $this->config;

		$model = new Models\Project( $id );
		$objAC = $model->table('Activity');

		if ($id)
		{
			if (!$model->exists())
			{
				App::redirect(Route::url('index.php?option=' . $this->_option, false),
					Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'),
					'error');
				return;
			}
		}
		if (!$id)
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false),
				Lang::txt('COM_PROJECTS_NOTICE_NEW_PROJECT_FRONT_END'),
				'error');
			return;
		}

		// Get project types
		$objT = $model->table('Type');
		$this->view->types = $objT->getTypes();

		// Get activity counts
		$counts = Event::trigger( 'projects.onProjectCount', array( $model, 1) );
		$counts = Helpers\Html::getCountArray($counts);
		$counts['activity'] = $objAC->getActivityCount( $model->get('id'), User:: get('id'));
		$this->view->counts = $counts;

		// Get team
		$objO = $model->table('Owner');

		// Sync with system group
		$objO->sysGroup($model->get('alias'), $this->config->get('group_prefix', 'pr-'));

		// Get members and managers
		$this->view->managers   = $objO->getOwnerNames($id, 0, '1', 1);
		$this->view->members    = $objO->getOwnerNames($id, 0, '0', 1);
		$this->view->authors    = $objO->getOwnerNames($id, 0, '2', 1);
		$this->view->reviewers  = $objO->getOwnerNames($id, 0, '5', 1);

		// Get last activity
		$afilters = array('limit' => 1);
		$last_activity = $objAC->getActivities ($id, $afilters);
		$this->view->last_activity = count($last_activity) > 0 ? $last_activity[0] : '';

		// Was project suspended?
		$this->view->suspended = false;
		$setup_complete = $this->config->get('confirm_step', 0) ? 3 : 2;
		if ($model->isInactive())
		{
			$this->view->suspended = $objAC->checkActivity( $id, Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_SUSPENDED'));
		}

		// Get project params
		$this->view->params = $model->params;

		$content = Event::trigger( 'projects.diskspace', array( $model, 'local', 'admin'));
		$this->view->diskusage = isset($content[0])  ? $content[0]: '';

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError( $this->getError() );
		}

		// Get tags on this item
		$cloud = new Models\Tags($id);
		$this->view->tags = $cloud->render('string');

		// Output the HTML
		$this->view->model = $model;
		$this->view->publishing	= $this->_publishing;
		$this->view->display();
	}

	/**
	 * Save a project and fall through to edit view
	 *
	 * @return void
	 */
	public function applyTask()
	{
		$this->saveTask(true);
	}

	/**
	 * Saves a project
	 * Redirects to main listing
	 *
	 * @return     void
	 */
	public function saveTask($redirect = false)
	{
		// Check for request forgeries
		Request::checkToken();

		// Config
		$setup_complete = $this->config->get('confirm_step', 0) ? 3 : 2;

		// Incoming
		$formdata 	= $_POST;
		$id 		= Request::getVar( 'id', 0 );
		$action 	= Request::getVar( 'admin_action', '' );
		$message 	= rtrim(\Hubzero\Utility\Sanitize::clean(Request::getVar( 'message', '' )));

		// Load model
		$model = new Models\Project( $id );
		if (!$model->exists())
		{
			App::redirect('index.php?option=' . $this->_option,
				Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'),
				'error');
			return;
		}

		$title = $formdata['title'] ? rtrim($formdata['title']) : $model->get('title');
		$type  = isset($formdata['type']) ? $formdata['type'] : 1;
		$model->set('title', $title);
		$model->set('about', rtrim(\Hubzero\Utility\Sanitize::clean($formdata['about'])));
		$model->set('type', $type);
		$model->set('modified', Date::toSql());
		$model->set('modified_by', User::get('id'));
		$model->set('private', Request::getVar( 'private', 0 ));

		$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_SAVED');

		// Was project suspended?
		$suspended = false;
		if ($model->isInactive())
		{
			$suspended = $model->table('Activity')->checkActivity( $id, Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_SUSPENDED'));
		}

		$subject  = Lang::txt('COM_PROJECTS_PROJECT') . ' "' . $model->get('alias') . '" ';
		$sendmail = 0;

		// Get project managers
		$managers = $model->table('Owner')->getIds( $id, 1, 1 );

		// Admin actions
		if ($action)
		{
			switch ($action)
			{
				case 'delete':
					$model->set('state', 2);
					$what           = Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_DELETED');
					$subject       .= Lang::txt('COM_PROJECTS_MSG_ADMIN_DELETED');
					$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_DELETED');
				break;

				case 'suspend':
					$model->set('state', 0);
					$what           = Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_SUSPENDED');
					$subject       .= Lang::txt('COM_PROJECTS_MSG_ADMIN_SUSPENDED');
					$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_SUSPENDED');
				break;

				case 'reinstate':
					$model->set('state', 1);
					$what = $suspended
						? Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_REINSTATED')
						: Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_ACTIVATED');
					$subject .= $suspended
						? Lang::txt('COM_PROJECTS_MSG_ADMIN_REINSTATED')
						: Lang::txt('COM_PROJECTS_MSG_ADMIN_ACTIVATED');

					$this->_message = $suspended
						? Lang::txt('COM_PROJECTS_SUCCESS_REINSTATED')
						: Lang::txt('COM_PROJECTS_SUCCESS_ACTIVATED');
				break;
			}

			// Add activity
			$model->recordActivity($what, 0, '', '', 'project', 0, $admin = 1 );
			$sendmail = 1;
		}
		elseif ($message)
		{
			$subject .= ' - ' . Lang::txt('COM_PROJECTS_MSG_ADMIN_NEW_MESSAGE');
			$sendmail = 1;
			$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_MESSAGE_SENT');
		}

		// Save changes
		if (!$model->store())
		{
			$this->setError( $model->getError() );
			return false;
		}

		// Incoming tags
		$tags = Request::getVar('tags', '', 'post');

		// Save the tags
		$cloud = new Models\Tags($model->get('id'));
		$cloud->setTags($tags, User::get('id'), 1);

		// Save params
		$incoming = Request::getVar( 'params', array() );
		if (!empty($incoming))
		{
			foreach ($incoming as $key=>$value)
			{
				if ($key == 'quota' || $key == 'pubQuota')
				{
					// convert GB to bytes
					$value = Helpers\Html::convertSize( floatval($value), 'GB', 'b');
				}

				$model->saveParam($key, $value);
			}
		}

		// Add members if specified
		$this->model = $model;
		$this->_saveMember();

		// Change ownership
		$this->_changeOwnership();

		// Send message
		if ($this->config->get('messaging', 0) && $sendmail && count($managers) > 0)
		{
			// Email config
			$from 			= array();
			$from['name']  	= Config::get('sitename') . ' ' . Lang::txt('COM_PROJECTS');
			$from['email'] 	= Config::get('mailfrom');

			// Html email
			$from['multipart'] = md5(date('U'));

			// Message body
			$eview = new \Hubzero\Mail\View(array(
				'name'   => 'emails',
				'layout' => 'admin_plain'
			));
			$eview->option 			= $this->_option;
			$eview->subject 		= $subject;
			$eview->action 			= $action;
			$eview->project 		= $model;
			$eview->message			= $message;

			$body = array();
			$body['plaintext'] 	= $eview->loadTemplate(false);
			$body['plaintext'] 	= str_replace("\n", "\r\n", $body['plaintext']);

			// HTML email
			$eview->setLayout('admin_html');
			$body['multipart'] = $eview->loadTemplate();
			$body['multipart'] = str_replace("\n", "\r\n", $body['multipart']);

			// Send HUB message
			Event::trigger( 'xmessage.onSendMessage',
				array( 'projects_admin_notice', $subject, $body, $from, $managers, $this->_option ));
		}

		\Notify::message($this->_message, 'success');

		// Redirect to edit view?
		if ($redirect)
		{
			App::redirect(Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false));
		}
		else
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false));
		}
	}

	/**
	 * Save member
	 *
	 * @return     void
	 */
	protected function _saveMember()
	{
		// New member added?
		$members 	= urldecode(trim(Request::getVar( 'newmember', '', 'post'  )));
		$role 		= Request::getInt( 'role', 0 );

		$mbrs = explode(',', $members);

		foreach ($mbrs as $mbr)
		{
			// Retrieve user's account info
			$profile = \Hubzero\User\Profile::getInstance( trim($mbr) );

			// Ensure we found an account
			if ($profile)
			{
				$this->model->table('Owner')->saveOwners (
					$this->model->get('id'),
					User::get('id'),
					$profile->get('uidNumber'),
					0,
					$role,
					$status = 1,
					0
				);
			}
		}
	}

	/**
	 * Change ownership
	 *
	 * @return     void
	 */
	protected function _changeOwnership()
	{
		// Incoming
		$user    = Request::getInt( 'owned_by_user', $this->model->get('owned_by_user'), 'post' );
		$group   = Request::getInt( 'owned_by_group', 0, 'post' );

		// Load project owner table class
		$objO = $this->model->table('Owner');
		$objO->loadOwner($this->model->get('id'), $user);

		if (!$objO->id)
		{
			throw new Exception(Lang::txt('Error loading user'), 404);
		}

		// Change in individual ownership
		if ($user != $this->model->get('owned_by_user'))
		{
			$this->model->set('owned_by_user', $user);
			$this->model->store();

			// Make sure user is manager
			$objO->role = 1;
			$objO->store();
		}

		// Change in group ownership
		if ($group != $this->model->get('owned_by_group'))
		{
			$this->model->set('owned_by_group', $group);
			$this->model->store();

			// Make sure project lead is affiliated with group
			$objO->groupid = $group;
			$objO->store();
		}

	}

	/**
	 * Redirects
	 *
	 * @return     void
	 */
	public function cancelTask()
	{
		App::redirect(
			Route::url('index.php?option=' . $this->_option, false)
		);
	}

	/**
	 * Erases all project information (to be used for test projects only)
	 *
	 * @return     void
	 */
	public function eraseTask()
	{
		$id = Request::getVar( 'id', 0 );
		$permanent = 1;

		// Initiate extended database class
		$obj = new Tables\Project( $this->database );
		if (!$id or !$obj->loadProject($id))
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false),
				Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'),
				'error');
			return;
		}

		// Get project group
		$group_prefix = $this->config->get('group_prefix', 'pr-');
		$prGroup = $group_prefix . $obj->alias;

		// Store project info
		$alias = $obj->alias;
		$identifier = $alias;

		// Delete project
		$obj->delete();

		// Erase all owners
		$objO = new Tables\Owner ($this->database );
		$objO->removeOwners ( $id, '', 0, $permanent, '', $all = 1 );

		// Erase owner group
		$group = new \Hubzero\User\Group();
		$group->read( $prGroup );
		if ($group)
		{
			$group->delete();
		}

		// Erase all comments
		$objC = new Tables\Comment ($this->database );
		$objC->deleteProjectComments ( $id, $permanent );

		// Erase all activities
		$objA = new Tables\Activity( $this->database );
		$objA->deleteActivities( $id, $permanent );

		// Erase all todos
		$objTD = new Tables\Todo( $this->database );
		$objTD->deleteTodos( $id, '', $permanent );

		// Erase all blog entries
		$objB = new Tables\Blog( $this->database );
		$objB->deletePosts( $id, $permanent );

		// Erase all notes
		include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'tables' . DS . 'attachment.php');
		include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'tables' . DS . 'author.php');
		include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'tables' . DS . 'comment.php');
		include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'tables' . DS . 'log.php');
		include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'tables' . DS . 'page.php');
		include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'tables' . DS . 'revision.php');
		$masterscope = 'projects' . DS . $alias . DS . 'notes';

		// Get all notes
		$this->database->setQuery( "SELECT DISTINCT p.id FROM #__wiki_page AS p
			WHERE p.group_cn=" . $this->database->quote($prGroup)
			. " AND p.scope LIKE '" . $masterscope . "%' " );
		$notes = $this->database->loadObjectList();

		if ($notes)
		{
			foreach ($notes as $note)
			{
				$page = new \Components\Wiki\Tables\Page( $this->database );

				// Delete the page's history, tags, comments, etc.
				$page->deleteBits( $note->id );

				// Finally, delete the page itself
				$page->delete( $note->id );
			}
		}

		// Erase all files, remove files repository
		if ($alias)
		{
			// Delete base dir for .git repos
			$dir 		= $alias;
			$prefix 	= $this->config->get('offroot', 0) ? '' : PATH_CORE ;
			$repodir 	= DS . trim($this->config->get('webpath'), DS);
			$path 		= $prefix . $repodir . DS . $dir;

			if (is_dir($path))
			{
				Filesystem::deleteDirectory($path);
			}

			// Delete images/preview directories
			$webdir = DS . trim($this->config->get('imagepath', '/site/projects'), DS);
			$webpath = PATH_APP . $webdir . DS . $dir;

			if (is_dir($webpath))
			{
				Filesystem::deleteDirectory($webpath);
			}
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option='.$this->_option, false),
			Lang::txt('COM_PROJECTS_PROJECT') . ' #' . $id . ' (' . $alias . ') ' . Lang::txt('COM_PROJECTS_PROJECT_ERASED')
		);
	}

	/**
	 * Add and commit untracked/changed files
	 *
	 * This is helpful in case git add/commit failed during file upload
	 *
	 * @return     void
	 */
	public function gitaddTask()
	{
		$id   = Request::getVar( 'id', 0 );
		$file = Request::getVar( 'file', '' );

		// Initiate extended database class
		$obj = new Tables\Project( $this->database );
		if (!$id or !$obj->loadProject($id))
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false),
				Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'),
				'error'
			);
			return;
		}

		$url = Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false);

		if (!$file)
		{
			App::redirect($url,
				Lang::txt('Please specify a file/directory path to add and commit into project'),
				'error'
			);
			return;
		}

		// Delete base dir for .git repos
		$prefix  = $this->config->get('offroot', 0) ? '' : PATH_APP ;
		$repodir = trim($this->config->get('webpath'), DS);
		$path    = $prefix . DS . $repodir . DS . $obj->alias . DS . 'files';

		if (!is_file($path . DS . $file))
		{
			App::redirect($url,
				Lang::txt('Error: File not found in the project, cannot add and commit'),
				'error');
			return;
		}

		// Git helper
		require_once(dirname(__DIR__) . DS . 'helpers' . DS . 'githelper.php');
		$gitHelper = new Helpers\Git($path);

		$commitMsg = '';

		// Git add & commit
		$gitHelper->gitAdd($file, $commitMsg);
		$gitHelper->gitCommit($commitMsg);

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false),
			Lang::txt('File checked into project Git repo')
		);
	}

	/**
	 * Optimize git repo
	 *
	 * @return     void
	 */
	public function gitgcTask()
	{
		$id = Request::getVar( 'id', 0 );

		// Get repo model
		require_once(PATH_CORE . DS . 'components' . DS . 'com_projects'
			. DS . 'models' . DS . 'repo.php');

		$project = new Models\Project($id);
		if (!$project->exists())
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false),
				Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'),
				'error');
			return;
		}
		$repo = new \Components\Projects\Models\Repo ($project, 'local');
		$params = array(
			'path' => $repo->get('path'),
			'adv'  => true
		);
		$repo->call('optimize', $params);

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false),
			Lang::txt('Git repo optimized')
		);
	}

	/**
	 * Unlock sync and view sync log for project
	 *
	 * @return     void
	 */
	public function fixsyncTask()
	{
		$id = Request::getVar( 'id', 0 );
		$service = 'google';

		// Initiate extended database class
		$obj = new Tables\Project( $this->database );
		if (!$id or !$obj->loadProject($id))
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false),
				Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'),
				'error');
			return;
		}

		// Unlock sync
		$obj->saveParam($id, $service . '_sync_lock', '');

		// Get log file
		$repodir = Helpers\Html::getProjectRepoPath($obj->alias, 'logs');
		$sfile 	 = $repodir . DS . 'sync.' . Date::format('Y-m') . '.log';

		if (file_exists($sfile))
		{
			// Serve up file
			$server = new \Hubzero\Content\Server();
			$server->filename($sfile);
			$server->disposition('attachment');
			$server->acceptranges(false);
			$server->saveas('sync.' . Date::format('Y-m') . '.txt');
			$result = $server->serve_attachment($sfile, 'sync.' . Date::format('Y-m') . '.txt', false);
			exit;
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false),
			Lang::txt('Sync log unavailable')
		);
	}
}