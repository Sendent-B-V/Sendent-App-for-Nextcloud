<?php

namespace OCA\Sendent\Service;

use OCP\DB\ISearchQuery;
use OCP\DB\IQueryBuilder;
use OCP\IDBConnection;

class FolderMappingService {

	private IDBConnection $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	public function getByMsId(string $msId): ?array {
		$query = $this->db->getQueryBuilder();
		$query->select('folder_id', 'ms_id', 'type')
			->from('sndnt_foldermap')
			->where($query->expr()->eq('ms_id', $query->createNamedParameter($msId)));

		$result = $query->execute()->fetch();
		return $result ?: null;
	}

	public function create(string $msId, string $folderId, string $type): void {
		$query = $this->db->getQueryBuilder();
		$query->insert('sndnt_foldermap')
			->values([
				'folder_id' => $query->createNamedParameter($folderId),
				'ms_id' => $query->createNamedParameter($msId),
				'type' => $query->createNamedParameter($type),
			]);
		$query->executeStatement();
	}

	public function update(int $id, string $msId, string $folderId, string $type): void {
		$query = $this->db->getQueryBuilder();
		$query->update('sndnt_foldermap')
			->set('ms_id', $query->createNamedParameter($msId))
			->set('folder_id', $query->createNamedParameter($folderId))
			->set('type', $query->createNamedParameter($type))
			->where($query->expr()->eq('id', $query->createNamedParameter($id)));
		$query->executeStatement();
	}

	public function deleteByMsId(string $msId): int {
		$query = $this->db->getQueryBuilder();
		$query->delete('sndnt_foldermap')
			->where($query->expr()->eq('ms_id', $query->createNamedParameter($msId)));
		return $query->executeStatement();
	}
}
