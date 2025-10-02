<?php

declare(strict_types=1);

namespace OCA\Sendent\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000022Date20251002120000 extends SimpleMigrationStep {

	private IDBConnection $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('sndnt_foldermap')) {
			$table = $schema->getTable('sndnt_foldermap');

			if (!$table->hasColumn('nextcloud_team_id')) {
				// Adjust length if you expect longer IDs; 64–255 are typical
				$table->addColumn('nextcloud_team_id', 'string', [
					'length'  => 255,
					'notnull' => false,
				]);
			}
		}

		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
