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

namespace OCA\Deck\Service;



use OCA\Deck\Db\Card;
use OCA\Deck\Db\CardMapper;
use OCA\Deck\Db\Label;
use OCA\Deck\Db\LabelMapper;
use OCA\Deck\Db\Stack;
use OCA\Deck\Db\StackMapper;

class StackServiceTest extends \PHPUnit_Framework_TestCase {

    /** @var StackService */
	private $stackService;
    /** @var \PHPUnit_Framework_MockObject_MockObject|StackMapper */
	private $stackMapper;
    /** @var \PHPUnit_Framework_MockObject_MockObject|CardMapper */
	private $cardMapper;
    /** @var \PHPUnit_Framework_MockObject_MockObject|LabelMapper */
	private $labelMapper;
    /** @var \PHPUnit_Framework_MockObject_MockObject|PermissionService */
	private $permissionService;

	public function setUp() {
		$this->stackMapper = $this->getMockBuilder(StackMapper::class)
			->disableOriginalConstructor()->getMock();
		$this->cardMapper = $this->getMockBuilder(CardMapper::class)
			->disableOriginalConstructor()->getMock();
		$this->labelMapper = $this->getMockBuilder(LabelMapper::class)
			->disableOriginalConstructor()->getMock();
		$this->permissionService = $this->getMockBuilder(PermissionService::class)
			->disableOriginalConstructor()->getMock();

		$this->stackService = new StackService(
			$this->stackMapper,
            $this->cardMapper,
            $this->labelMapper,
			$this->permissionService
		);
	}

	public function testFindAll() {
	    // FIXME: FIX ME !!!
        $this->permissionService->expects($this->once())->method('checkPermission');
        $this->stackMapper->expects($this->once())->method('findAll')->willReturn($this->getStacks());
        $this->labelMapper->expects($this->once())->method('getAssignedLabelsForBoard')->willReturn($this->getLabels());
        $this->cardMapper->expects($this->any())->method('findAll')->willReturn($this->getCards(222));

        $actual = $this->stackService->findAll(123);
        for($stackId=0; $stackId<3; $stackId++) {
            for ($cardId=0;$cardId<10;$cardId++) {
                $this->assertEquals($actual[0]->getCards()[$cardId]->getId(), $cardId);
                $this->assertEquals($actual[0]->getCards()[$cardId]->getStackId(), 222);
                $this->assertEquals($actual[0]->getCards()[$cardId]->getLabels(), $this->getLabels()[$cardId]);
            }
        }
    }

    public function testFindAllArchived() {
    $this->permissionService->expects($this->once())->method('checkPermission');
    $this->stackMapper->expects($this->once())->method('findAll')->willReturn($this->getStacks());
    $this->labelMapper->expects($this->once())->method('getAssignedLabelsForBoard')->willReturn($this->getLabels());
    $this->cardMapper->expects($this->any())->method('findAllArchived')->willReturn($this->getCards(222));

    $actual = $this->stackService->findAllArchived(123);
    for($stackId=0; $stackId<3; $stackId++) {
        for ($cardId=0;$cardId<10;$cardId++) {
            $this->assertEquals($actual[0]->getCards()[$cardId]->getId(), $cardId);
            $this->assertEquals($actual[0]->getCards()[$cardId]->getStackId(), 222);
            $this->assertEquals($actual[0]->getCards()[$cardId]->getLabels(), $this->getLabels()[$cardId]);
        }
    }
}

    private function getLabels() {
        for ($i=0;$i<10;$i++) {
            $label1 = new Label();
            $label1->setTitle('Important');
            $label1->setCardId(1);
            $label2 = new Label();
            $label2->setTitle('Maybe');
            $label2->setCardId(2);
            $labels[$i] = [
                $label1,
                $label2
            ];
        }
        return $labels;
    }
    private function getStacks() {
	    $s1 = new Stack();
	    $s1->setId(222);
	    $s2 = new Stack();
	    $s2->setId(223);
	    return [$s1, $s2];
    }
    private function getCards($stackId=0) {
	    $cards = [];
	    for ($i=0;$i<10;$i++) {
	        $cards[$i] = new Card();
	        $cards[$i]->setId($i);
            $cards[$i]->setStackId($stackId);
        }
        return $cards;
    }

    public function testCreate() {
        $this->permissionService->expects($this->once())->method('checkPermission');
        $stack = new Stack();
        $stack->setId(123);
        $stack->setTitle('Foo');
        $stack->setBoardId(2);
        $stack->setOrder(1);
        $this->stackMapper->expects($this->once())->method('insert')->willReturn($stack);
        $result = $this->stackService->create('Foo', 2, 1);
        $this->assertEquals($stack, $result);
    }

	public function testDelete() {
	    $this->permissionService->expects($this->once())->method('checkPermission');
        $this->stackMapper->expects($this->once())->method('find')->willReturn(new Stack());
	    $this->stackMapper->expects($this->once())->method('delete');
	    $this->stackService->delete(123);
    }

    public function testUpdate() {
        $this->permissionService->expects($this->once())->method('checkPermission');
        $stack = new Stack();
        $this->stackMapper->expects($this->once())->method('find')->willReturn($stack);
        $this->stackMapper->expects($this->once())->method('update')->willReturn($stack);
        $stack->setId(123);
        $stack->setTitle('Foo');
        $stack->setBoardId(2);
        $stack->setOrder(1);
        $result = $this->stackService->update(123, 'Foo', 2, 1);
        $this->assertEquals($stack, $result);

    }

}