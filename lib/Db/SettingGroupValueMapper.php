<?php

// db/authormapper.php

namespace OCA\Sendent\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class SettingGroupValueMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'sndnt_stnggrval', SettingGroupValue::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 *
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function find(int $id, string $gid = ''): \OCP\AppFramework\Db\Entity {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('sndnt_stnggrval')
			->where(
				$qb->expr()->eq('settingkeyid', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)),
				$qb->expr()->eq('ncgroup', $qb->createNamedParameter($gid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 *
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function findBySettingKeyId(int $settingkeyid, string $gid = ''): \OCP\AppFramework\Db\Entity {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('sndnt_stnggrval')
			->where(
				$qb->expr()->eq('settingkeyid', $qb->createNamedParameter($settingkeyid, IQueryBuilder::PARAM_INT)),
				$qb->expr()->eq('ncgroup', $qb->createNamedParameter($gid, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 *
	 * @return \OCP\AppFramework\Db\Entity[]
	 *
	 * @psalm-return array<\OCP\AppFramework\Db\Entity>
	 */
	public function findByGroupId(int $groupId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('sndnt_stnggrval')
			->where(
				$qb->expr()->eq('groupid', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_INT))
			);

		return $this->findEntities($qb);
	}

	/**
	 * @return \OCP\AppFramework\Db\Entity[]
	 *
	 * @psalm-return array<\OCP\AppFramework\Db\Entity>
	 */
	public function findAll($limit = null, $offset = null): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('sndnt_stnggrval')
			->setMaxResults($limit)
			->setFirstResult($offset);

		return $this->findEntities($qb);
	}

	public function deleteSettingsForGroup($gid = '') {
		$settings = $this->findSettingsForNCGroup($gid);
		foreach ($settings as $setting) {
			$this->delete($setting);
		}
	}

	/**
	 * @return \OCP\AppFramework\Db\Entity[]
	 *
	 * @psalm-return array<\OCP\AppFramework\Db\Entity>
	 */
	public function findSettingsForNCGroup($gid = '', $limit = null, $offset = null): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('sndnt_stnggrval')
			->where(
				$qb->expr()->eq('ncgroup', $qb->createNamedParameter($gid, IQueryBuilder::PARAM_STR))
			)->setMaxResults($limit)
			->setFirstResult($offset);

		return $this->findEntities($qb);
	}

	public function settingKeyCount($groupid) {
		$qb = $this->db->getQueryBuilder();

		$qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
			->from('sndnt_stnggrval')
			->where(
				$qb->expr()->eq('groupid', $qb->createNamedParameter($groupid, IQueryBuilder::PARAM_STR))
			);

		$cursor = $qb->executeQuery();
		$row = $cursor->fetch();
		$cursor->closeCursor();

		return $row['count'];
	}
}
