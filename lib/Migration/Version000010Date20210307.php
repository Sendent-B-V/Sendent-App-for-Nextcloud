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

class Version000010Date20210307 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		//new since licensing feature - license
		if (!$schema->hasTable('sndnt_license')) {
			$table = $schema->createTable('sndnt_license');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => false,
			]);

			$table->addColumn('licensekey', 'string', [
				'notnull' => false
			]);
			$table->addColumn('email', 'string', [
				'notnull' => false
			]);
			$table->addColumn('maxusers', 'integer', [
				'notnull' => false
			]);
			$table->addColumn('maxgraceusers', 'integer', [
				'notnull' => false
			]);
			$table->addColumn('dategraceperiodend', 'string', [
				'notnull' => false
			]);
			$table->addColumn('datelicenseend', 'string', [
				'notnull' => false
			]);
			$table->addColumn('datelastchecked', 'string', [
				'notnull' => false
			]);
			$table->addColumn('level', 'string', [
				'notnull' => false
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['licensekey'], 'sendent_license_index');
		}

		return $schema;
	}
}
