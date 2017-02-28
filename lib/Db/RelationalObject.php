<?php
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
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
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Created by PhpStorm.
 * User: jus
 * Date: 27.02.17
 * Time: 14:05
 */

namespace OCA\Deck\Db;


abstract class RelationalObject implements \JsonSerializable {

	/**
	 * RelationalObject constructor.
	 *
	 * @param $primaryKey string
	 * @param $object
	 */
	public function __construct($primaryKey, $object) {
		$this->primaryKey = $primaryKey;
		$this->object = $object;
	}

	public function jsonSerialize() {
		return array_merge(
			['primaryKey' => $this->primaryKey],
			$this->getObjectSerialization()
		);
	}

	public function getObjectSerialization() {
		$this->object->jsonSerialize();
	}

	public function getPrimaryKey() {
		return $this->getPrimaryKey();
	}
}