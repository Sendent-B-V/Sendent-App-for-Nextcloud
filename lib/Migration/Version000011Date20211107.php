<?php

namespace OCA\Sendent\migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000011Date20211107 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		//new since terms agreement feature
		if (!$schema->hasTable('sndnt_trmagr')) {
			$table = $schema->createTable('sndnt_trmagr');
			$table->addColumn('version', 'string', [
				'autoincrement' => false,
				'notnull' => false
			]);
			$table->addColumn('agreed', 'string', [
				'autoincrement' => false,
				'notnull' => false
			]);
		}

		return $schema;
	}

	protected function ensureColumnIsNullable(ISchemaWrapper $schema, string $tableName, string $columnName): bool {
		$table = $schema->getTable($tableName);
		$column = $table->getColumn($columnName);

		if ($column->getNotnull()) {
			$column->setNotnull(false);

			return true;
		}

		return false;
	}
}
