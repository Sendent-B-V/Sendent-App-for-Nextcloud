<?php

namespace OCA\Sendent\Controller;

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\Sendent\Service\NotFoundException;

trait errors {
	protected function handleNotFound(Closure $callback) {
		try {
			return new DataResponse($callback());
		} catch (NotFoundException $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}
}
