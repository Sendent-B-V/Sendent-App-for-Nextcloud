<?php

namespace OCA\Sendent\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Sendent\Db\TermsAgreement;
use OCA\Sendent\Db\TermsAgreementMapper;

class TermsAgreementService {
	private $mapper;

	public function __construct(TermsAgreementMapper $mapper) {
		$this->mapper = $mapper;
	}

	public function findAll() {
		return $this->mapper->findAll();
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(string $version) {
		try {
			return $this->mapper->findByVersion($version);
		} catch (Exception $e) {
			// in order to be able to plug in different storage backends like files
			// for instance it is a good idea to turn storage related exceptions
			// into service related exceptions so controllers and service users
			// have to deal with only one type of exception

			$this->handleException($e);
		}
	}

	public function isAgreed(string $version) {
		try {
			$termsAgreed = $this->mapper->findByVersion($version);
			if ($termsAgreed->getAgreed() == "yes") {
				error_log(print_r("TERMSAGREEMENTSERVICE-IsAgreed-TRUE", true));
				return $termsAgreed;
			}

			error_log(print_r("TERMSAGREEMENTSERVICE-IsAgreed-FALSE", true));
			return $termsAgreed;
		} catch (Exception $e) {
			error_log(print_r("TERMSAGREEMENTSERVICE-IsAgreed-EXCEPTION=" . $e, true));
			$termsAgreed = $this->create($version, "no");
			return $termsAgreed;
		}
	}

	public function setToAgreed(string $version) {
		try {
			$termsAgreed = $this->mapper->findByVersion($version);
			if (isset($termsAgreed) && $termsAgreed->getAgreed() == "yes") {
				error_log(print_r("TERMSAGREEMENTSERVICE-SetToAgreed-TRUE", true));
				return $termsAgreed;
			}

			error_log(print_r("TERMSAGREEMENTSERVICE-SetToAgreed-FALSE", true));
			return $termsAgreed;
		} catch (Exception $e) {
			error_log(print_r("TERMSAGREEMENTSERVICE-SetToAgreed-EXCEPTION=" . $e, true));
			$termsAgreed = $this->create($version, "yes");
			return $termsAgreed;
		}
	}

	public function create(string $version, string $agreed): \OCP\AppFramework\Db\Entity {
		$termsAgreed = new TermsAgreement();
		$termsAgreed->setVersion($version);
		$termsAgreed->setAgreed($agreed);

		return $this->mapper->insert($termsAgreed);
	}

	public function update(string $version,string $agreed): \OCP\AppFramework\Db\Entity {
		try {
			$termsAgreed = $this->mapper->findByVersion($version);
			$termsAgreed->setAgreed($agreed);

			return $this->mapper->update($termsAgreed);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}


	public function destroy(string $version): \OCP\AppFramework\Db\Entity {
		try {
			$termsAgreed = $this->mapper->findByVersion($version);
			$this->mapper->delete($termsAgreed);

			return $termsAgreed;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
