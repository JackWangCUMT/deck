<?php
/**
 * @copyright Copyright (c) 2016 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Deck\Controller;

use OCA\Deck\Db\Acl;

use OCA\Deck\Db\Group;
use OCA\Deck\Db\User;
use OCA\Deck\Service\BoardService;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\IUserManager;

class ShareController extends Controller {

	private $userManager;
	private $groupManager;
	private $boardService;
	private $userId;

	public function __construct($appName, IRequest $request, IUserManager $userManager, IGroupManager $groupManager, BoardService $boardService, $userId) {
		parent::__construct($appName, $request);
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userId = $userId;
		$this->boardService = $boardService;

	}

	/**
	 * @NoAdminRequired
	 * @param $search
	 * @return array
	 */
	public function searchUser($search) {
		if($search==='%') {
			$search = '';
		}
		$limit = 5;
		$offset = null;
		$result = [];
		$userManager = $this->userManager;
		$groupManager = $this->groupManager;
		foreach ($this->groupManager->search($search, $limit, $offset) as $idx => $group) {
			$acl = new Acl();
			$acl->setType('group');
			$acl->setParticipant($group->getGID());
			$acl->setPermissionEdit(true);
			$acl->setPermissionShare(true);
			$acl->setPermissionManage(true);
			$acl->resolveRelation('participant', function($value) use (&$acl, &$userManager, &$groupManager) {
				return new Group($groupManager->get($value));
			});
			$result[] = $acl;
		}
		foreach ($this->userManager->searchDisplayName($search, $limit, $offset) as $idx => $user) {
			if ($user->getUID() === $this->userId) {
							continue;
			}
			$acl = new Acl();
			$acl->setType('user');
			$acl->setParticipant($user->getUID());
			$acl->setPermissionEdit(true);
			$acl->setPermissionShare(true);
			$acl->setPermissionManage(true);
			$acl->resolveRelation('participant', function($value) use (&$acl, &$userManager, &$groupManager) {
				return new User($userManager->get($value));
			});
			$result[] = $acl;
		}
		return $result;
	}


}
