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
use OCP\IGroup;
use OCP\IUser;

class ShareControllerTest extends \Test\TestCase {

	private $controller;
	private $request;
	private $userManager;
	private $groupManager;
	private $boardService;
	private $permissionService;
	private $userId = 'user';

	public function setUp() {
		$this->l10n = $this->request = $this->getMockBuilder(
			'\OCP\IL10n')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->userManager = $this->createMock('\OCP\IUserManager');
		$this->groupManager = $this->createMock('\OCP\IGroupManager');
		$this->boardService = $this->getMockBuilder(
			'\OCA\Deck\Service\BoardService')
			->disableOriginalConstructor()
			->getMock();
		$this->groupManager->method('getUserGroupIds')
			->willReturn(['admin', 'group1', 'group2']);

		$this->controller = new ShareController(
			'deck',
			$this->request,
			$this->userManager,
			$this->groupManager,
			$this->boardService,
			$this->userId
		);
	}


	public function testSearchGroup() {
		$group = $this->createMock(IGroup::class);
		$group->expects($this->any())
			->method('getGID')
			->willReturn('foo');
		$groups = [$group];
		$this->groupManager->expects($this->once())
			->method('search')
			->willReturn($groups);
		$this->groupManager->expects($this->any())
			->method('get')
			->willReturn($group);
		$this->userManager->expects($this->once())
			->method('searchDisplayName')
			->willReturn([]);
		$actual = $this->controller->searchUser('foo');

		$acl = new Acl();
		$acl->setType('group');
		$acl->setParticipant('foo');
		$acl->setPermissionEdit(true);
		$acl->setPermissionShare(true);
		$acl->setPermissionManage(true);
		$acl->resolveRelation('participant', function($value) use ($group) {
			return new Group($group);
		});
		$this->assertEquals([$acl], $actual);
	}
	public function testSearchUser() {
		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn('foo');
		$users = [$user];
		$this->groupManager->expects($this->once())
			->method('search')
			->willReturn([]);
		$this->userManager->expects($this->any())
			->method('searchDisplayName')
			->willReturn($users);
		$this->userManager->expects($this->any())
			->method('get')
			->willReturn($user);
		$actual = $this->controller->searchUser('foo');

		$acl = new Acl();
		$acl->setType('user');
		$acl->setParticipant('foo');
		$acl->setPermissionEdit(true);
		$acl->setPermissionShare(true);
		$acl->setPermissionManage(true);
		$acl->resolveRelation('participant', function($value) use ($user) {
			return new User($user);
		});
		$this->assertEquals([0 => $acl], $actual);
	}

	public function testSearchUserExcludeOwn() {
		$user = $this->getMockBuilder(IUser::class)
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->any())
			->method('getUid')
			->willReturn('user');
		$users = [$user];
		$this->groupManager->expects($this->once())
			->method('search')
			->willReturn([]);

		$this->userManager->expects($this->once())
			->method('searchDisplayName')
			->with('user')
			->willReturn($users);
		$actual = $this->controller->searchUser('user');
		$this->assertEquals([], $actual);
	}

}
