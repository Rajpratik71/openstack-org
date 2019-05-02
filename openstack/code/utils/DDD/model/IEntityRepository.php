<?php
/**
 * Copyright 2014 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
/**
 * Interface IEntityRepository
 */
interface IEntityRepository {
	/**
	 * @param QueryObject $query
	 * @return IEntity
	 */
	public function getBy(QueryObject $query);

	/**
	 * @param  mixed $entity
	 * @return int
	 */
	public function add($entity);

	/**
	 * @param  mixed $entity
	 * @return void
	 */
	public function delete($entity);

	/**
	 * @param int $id
	 * @return IEntity
	 */
	public function getById($id);

	/**
	 * @param QueryObject $query
	 * @param int         $offset
	 * @param int         $limit
	 * @return array
	 */
	public function getAll(QueryObject $query, $offset=0, $limit = 10);
} 