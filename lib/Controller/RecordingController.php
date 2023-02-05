<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Vitor Mattos <vitor@php.rio>
 *
 * @author Vitor Mattos <vitor@php.rio>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Talk\Controller;

use InvalidArgumentException;
use GuzzleHttp\Exception\ConnectException;
use OCA\Talk\Config;
use OCA\Talk\Exceptions\UnauthorizedException;
use OCA\Talk\Service\RecordingService;
use OCA\Talk\Service\SIPBridgeService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Http\Client\IClientService;
use OCP\IRequest;

class RecordingController extends AEnvironmentAwareController {
	public function __construct(
		string $appName,
		IRequest $request,
		private Config $talkConfig,
		private SIPBridgeService $SIPBridgeService,
		private IClientService $clientService,
		private RecordingService $recordingService
	) {
		parent::__construct($appName, $request);
	}

	public function getWelcomeMessage(int $serverId): DataResponse {
		$recordingServers = $this->talkConfig->getRecordingServers();
		if (empty($recordingServers) || !isset($recordingServers[$serverId])) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		$url = rtrim($recordingServers[$serverId]['server'], '/');

		$client = $this->clientService->newClient();
		try {
			$response = $client->get($url . '/api/v1/welcome', [
				'verify' => (bool) $recordingServers[$serverId]['verify'],
				'nextcloud' => [
					'allow_local_address' => true,
				],
			]);

			$body = $response->getBody();
			$data = json_decode($body, true);

			if (!is_array($data)) {
				return new DataResponse([
					'error' => 'JSON_INVALID',
				], Http::STATUS_INTERNAL_SERVER_ERROR);
			}

			return new DataResponse($data);
		} catch (ConnectException $e) {
			return new DataResponse(['error' => 'CAN_NOT_CONNECT'], Http::STATUS_INTERNAL_SERVER_ERROR);
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getCode()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @PublicPage
	 * @RequireModeratorParticipant
	 */
	public function start(int $status): DataResponse {
		try {
			$this->recordingService->start($this->room, $status);
		} catch (InvalidArgumentException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse();
	}

	/**
	 * @PublicPage
	 * @RequireModeratorParticipant
	 */
	public function stop(): DataResponse {
		try {
			$this->recordingService->stop($this->room);
		} catch (InvalidArgumentException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse();
	}

	/**
	 * @PublicPage
	 * @RequireRoom
	 * @BruteForceProtection(action=talkSipBridgeSecret)
	 *
	 * @return DataResponse
	 */
	public function store(string $owner): DataResponse {
		try {
			$random = $this->request->getHeader('TALK_SIPBRIDGE_RANDOM');
			$checksum = $this->request->getHeader('TALK_SIPBRIDGE_CHECKSUM');
			$secret = $this->talkConfig->getSIPSharedSecret();
			if (!$this->SIPBridgeService->validateSIPBridgeRequest($random, $checksum, $secret, $this->room->getToken())) {
				throw new UnauthorizedException();
			}
		} catch (UnauthorizedException $e) {
			$response = new DataResponse([], Http::STATUS_UNAUTHORIZED);
			$response->throttle();
			return $response;
		}

		try {
			$file = $this->request->getUploadedFile('file');
			$this->recordingService->store($this->getRoom(), $owner, $file);
		} catch (InvalidArgumentException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 * @RequireModeratorParticipant
	 */
	public function notificationDismiss(int $timestamp): DataResponse {
		try {
			$this->recordingService->notificationDismiss(
				$this->getRoom(),
				$this->participant,
				$timestamp
			);
		} catch (InvalidArgumentException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 * @RequireModeratorParticipant
	 */
	public function shareToChat(int $fileId, int $timestamp): DataResponse {
		try {
			$this->recordingService->shareToChat(
				$this->getRoom(),
				$this->participant,
				$fileId,
				$timestamp
			);
		} catch (InvalidArgumentException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse();
	}
}
