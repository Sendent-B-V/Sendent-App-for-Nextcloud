<?php

// db/authormapper.php

namespace OCA\Sendent\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class ConnectedUserMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'sndnt_connuser', ConnectedUser::class);
	}
	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 *
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function find(int $id): \OCP\AppFramework\Db\Entity {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from('sndnt_connuser')
		   ->where(
			   $qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
		   );

		return $this->findEntity($qb);
	}
	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 *
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function findByUserId(string $userId): \OCP\AppFramework\Db\Entity {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from('sndnt_connuser')
		   ->where(
			   $qb->expr()->eq('userid', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
		   );

		return $this->findEntity($qb);
	}
	/**
	 * @return \OCP\AppFramework\Db\Entity[]
	 *
	 * @psalm-return array<\OCP\AppFramework\Db\Entity>
	 */
	public function findAll($limit = null, $offset = null): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from('sndnt_connuser')
		   ->setMaxResults($limit)
		   ->setFirstResult($offset);

		return $this->findEntities($qb);
	}

	public function getCount(string $licenseId) {
		$qb = $this->db->getQueryBuilder();

		$qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
			->from('sndnt_connuser')
			->where(
				$qb->expr()->eq('licenseid', $qb->createNamedParameter($licenseId, IQueryBuilder::PARAM_STR))
			);

		$cursor = $qb->execute();
		$row = $cursor->fetch();
		$cursor->closeCursor();

		return $row['count'];
	}
}
