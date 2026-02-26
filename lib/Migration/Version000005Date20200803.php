<?php

/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Sendent\migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000005Date20200803 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('sndnt_stngky')) {
			$table = $schema->createTable('sndnt_stngky');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);


			$table->addColumn('key', 'string', [
				'notnull' => false,
				'length' => 254,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => false,
				'length' => 254,
			]);
			$table->addColumn('templateid', 'integer', [
				'notnull' => false
			]);
			$table->addColumn('valuetype', 'string', [
				'notnull' => false,
				'length' => 254,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['key', 'name', 'templateid'], 'sendent_key_templateid_index');
		} else {
			$table = $schema->getTable('sndnt_stngky');
			$table->dropColumn('value');
		}








		if (!$schema->hasTable('sndnt_stnggrval')) {
			$table = $schema->createTable('sndnt_stnggrval');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('settingkeyid', 'integer', [
				'notnull' => false
			]);
			$table->addColumn('groupid', 'integer', [
				'notnull' => false
			]);
			$table->addColumn('value', 'string', [
				'notnull' => false
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['settingkeyid', 'groupid'], 'sendent_keygroup_index');
		}





		if (!$schema->hasTable('sndnt_stngtmplt')) {
			$table = $schema->createTable('sndnt_stngtmplt');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('templatename', 'string', [
				'notnull' => false
			]);
			$table->setPrimaryKey(['id']);
		} else {
			$table = $schema->getTable('sndnt_stngtmplt');
			$table->dropColumn('templateName');
			$table->addColumn('templatename', 'string', [
				'notnull' => false
			]);
		}

		//new since licensing feature - connecteduser
		if (!$schema->hasTable('sndnt_connuser')) {
			$table = $schema->createTable('sndnt_connuser');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => false,
			]);

			$table->addColumn('userid', 'string', [
				'notnull' => false
			]);
			$table->addColumn('dateconnected', 'string', [
				'notnull' => false
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['userid'], 'sendent_connuserid_index');
		}
		return $schema;
	}
}
