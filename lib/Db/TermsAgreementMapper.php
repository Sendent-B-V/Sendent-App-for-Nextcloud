<?php

// db/authormapper.php

namespace OCA\Sendent\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class TermsAgreementMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'sndnt_trmagr', TermsAgreement::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 *
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function findByVersion(string $version): \OCP\AppFramework\Db\Entity {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from('sndnt_trmagr')
		   ->where(
			$qb->expr()->eq('version', $qb->createNamedParameter($version, IQueryBuilder::PARAM_STR))
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
		   ->from('sndnt_trmagr')
		   ->setMaxResults($limit)
		   ->setFirstResult($offset);

		return $this->findEntities($qb);
	}
}
