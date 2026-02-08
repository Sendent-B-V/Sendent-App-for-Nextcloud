<?php

namespace OCA\Sendent\Service;

use OCP\IDBConnection;

class FolderMappingService {

	private IDBConnection $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	public function getByMsId(string $msId): ?array {
		$q = $this->db->getQueryBuilder();
		$q->select('folder_id', 'ms_id', 'type', 'nextcloud_team_id')
			->from('sndnt_foldermap')
			->where($q->expr()->eq('ms_id', $q->createNamedParameter($msId)));

		$result = $q->executeQuery()->fetch();
		return $result ?: null;
	}

	public function create(string $msId, string $folderId, string $type, ?string $nextcloudTeamId = null): void {
		$q = $this->db->getQueryBuilder();
		$q->insert('sndnt_foldermap')
			->values([
				'folder_id' => $q->createNamedParameter($folderId),
				'ms_id' => $q->createNamedParameter($msId),
				'type' => $q->createNamedParameter($type),
				'nextcloud_team_id' => $q->createNamedParameter($nextcloudTeamId),
			]);
		$q->executeStatement();
	}

	public function updateByMsId(string $msId, string $folderId, string $type, ?string $nextcloudTeamId = null): int {
		$q = $this->db->getQueryBuilder();
		$q->update('sndnt_foldermap')
			->set('folder_id', $q->createNamedParameter($folderId))
			->set('type', $q->createNamedParameter($type))
			->set('nextcloud_team_id', $q->createNamedParameter($nextcloudTeamId))
			->where($q->expr()->eq('ms_id', $q->createNamedParameter($msId)));

		return $q->executeStatement(); // rows affected
	}

	public function deleteByMsId(string $msId): int {
		$q = $this->db->getQueryBuilder();
		$q->delete('sndnt_foldermap')
			->where($q->expr()->eq('ms_id', $q->createNamedParameter($msId)));
		return $q->executeStatement();
	}

	// Optional convenience if you update only the team id by ms_id
	public function setTeamIdByMsId(string $msId, ?string $nextcloudTeamId): int {
		$q = $this->db->getQueryBuilder();
		$q->update('sndnt_foldermap')
			->set('nextcloud_team_id', $q->createNamedParameter($nextcloudTeamId))
			->where($q->expr()->eq('ms_id', $q->createNamedParameter($msId)));
		return $q->executeStatement();
	}
}
