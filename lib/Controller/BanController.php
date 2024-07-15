<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Talk\Controller;

use OCA\Talk\Middleware\Attribute\RequireModeratorParticipant;
use OCA\Talk\Model\Attendee;
use OCA\Talk\Model\Ban;
use OCA\Talk\ResponseDefinitions;
use OCA\Talk\Service\BanService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;

/**
 * @psalm-import-type TalkBan from ResponseDefinitions
 */
class BanController extends AEnvironmentAwareController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected BanService $banService,
		protected ITimeFactory $timeFactory,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Ban an actor or IP address
	 *
	 * Required capability: `ban-v1`
	 *
	 * @param 'users'|'groups'|'guests'|'circles'|'emails'|'federated_users'|'phones'|'ip' $actorType Type of actor to ban, or `ip` when banning a clients remote address
	 * @psalm-param Attendee::ACTOR_*|'ip' $actorType Type of actor to ban, or `ip` when banning a clients remote address
	 * @param string $actorId Actor ID or the IP address or range in case of type `ip`
	 * @param string $internalNote Optional internal note (max. 4000 characters)
	 * @return DataResponse<Http::STATUS_OK, TalkBan, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: 'bannedActor'|'internalNote'|'moderator'|'self'}, array{}>
	 *
	 * 200: Ban successfully
	 * 400: Actor information is invalid
	 */
	#[PublicPage]
	#[RequireModeratorParticipant]
	public function banActor(string $actorType, string $actorId, string $internalNote = ''): DataResponse {
		try {
			$moderator = $this->participant->getAttendee();
			$moderatorActorType = $moderator->getActorType();
			$moderatorActorId = $moderator->getActorId();

			$ban = $this->banService->createBan(
				$this->room,
				$moderatorActorId,
				$moderatorActorType,
				$actorId,
				$actorType,
				$this->timeFactory->getDateTime(),
				$internalNote
			);

			return new DataResponse($ban->jsonSerialize(), Http::STATUS_OK);
		} catch (\InvalidArgumentException $e) {
			/** @var 'bannedActor'|'internalNote'|'moderator'|'self' $message */
			$message = $e->getMessage();
			return new DataResponse([
				'error' => $message,
			], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * List the bans of a conversation
	 *
	 * Required capability: `ban-v1`
	 *
	 * @return DataResponse<Http::STATUS_OK, TalkBan[], array{}>
	 *
	 * 200: List all bans
	 */
	#[PublicPage]
	#[RequireModeratorParticipant]
	public function listBans(): DataResponse {
		$bans = $this->banService->getBansForRoom($this->room->getId());
		$result = array_map(static fn (Ban $ban): array => $ban->jsonSerialize(), $bans);
		return new DataResponse($result, Http::STATUS_OK);
	}

	/**
	 * Unban an actor or IP address
	 *
	 * Required capability: `ban-v1`
	 *
	 * @param int $banId ID of the ban to be removed
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>
	 *
	 * 200: Unban successfully or not found
	 */
	#[PublicPage]
	#[RequireModeratorParticipant]
	public function unbanActor(int $banId): DataResponse {
		$this->banService->findAndDeleteBanByIdForRoom($banId, $this->room->getId());
		return new DataResponse([], Http::STATUS_OK);
	}
}
