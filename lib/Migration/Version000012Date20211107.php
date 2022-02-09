<?php

namespace OCA\Sendent\migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000012Date20211107 extends SimpleMigrationStep {
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
		if ($schema->hasTable('sndnt_trmagr')) {
			$table = $schema->getTable('sndnt_trmagr');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
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
