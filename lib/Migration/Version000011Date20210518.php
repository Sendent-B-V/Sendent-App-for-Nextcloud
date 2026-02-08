<?php

namespace OCA\Sendent\migration;

use Closure;
use Exception;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000011Date20210518 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stngky', 'key');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stngky', 'name');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stngky', 'templateid');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stngky', 'valuetype');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stnggrval', 'settingkeyid');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stnggrval', 'groupid');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stnggrval', 'value');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_stngtmplt', 'templatename');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_connuser', 'userid');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_connuser', 'dateconnected');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'licensekey');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'email');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'maxusers');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'maxgraceusers');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'dategraceperiodend');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'datelicenseend');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'datelastchecked');
		$result = $this->ensureColumnIsNullable($schema, 'sndnt_license', 'level');

		return $schema;
	}

	protected function ensureColumnIsNullable(ISchemaWrapper $schema, string $tableName, string $columnName): bool {
		$table = $schema->getTable($tableName);
		$column = $table->getColumn($columnName);
		// try{
		if ($column->getNotnull()) {
			$column->setNotnull(false);
			return true;
		}
		// }
		// catch(Exception $e){

		// 	return false;

		// }
		return false;
	}
}
