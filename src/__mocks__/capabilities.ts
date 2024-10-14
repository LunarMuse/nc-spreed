/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import type { Capabilities } from '../types/index.ts'

export const mockedCapabilities: Capabilities = {
	spreed: {
		features: [
			'audio',
			'video',
			'chat-v2',
			'conversation-v4',
			'guest-signaling',
			'empty-group-room',
			'guest-display-names',
			'multi-room-users',
			'favorites',
			'last-room-activity',
			'no-ping',
			'system-messages',
			'delete-messages',
			'mention-flag',
			'in-call-flags',
			'conversation-call-flags',
			'notification-levels',
			'invite-groups-and-mails',
			'locked-one-to-one-rooms',
			'read-only-rooms',
			'listable-rooms',
			'chat-read-marker',
			'chat-unread',
			'webinary-lobby',
			'start-call-flag',
			'chat-replies',
			'circles-support',
			'force-mute',
			'sip-support',
			'sip-support-nopin',
			'chat-read-status',
			'phonebook-search',
			'raise-hand',
			'room-description',
			'rich-object-sharing',
			'temp-user-avatar-api',
			'geo-location-sharing',
			'voice-message-sharing',
			'signaling-v3',
			'publishing-permissions',
			'clear-history',
			'direct-mention-flag',
			'notification-calls',
			'conversation-permissions',
			'rich-object-list-media',
			'rich-object-delete',
			'unified-search',
			'chat-permission',
			'silent-send',
			'silent-call',
			'send-call-notification',
			'talk-polls',
			'breakout-rooms-v1',
			'recording-v1',
			'avatar',
			'chat-get-context',
			'single-conversation-status',
			'chat-keep-notifications',
			'typing-privacy',
			'remind-me-later',
			'bots-v1',
			'markdown-messages',
			'media-caption',
			'session-state',
			'note-to-self',
			'recording-consent',
			'sip-support-dialout',
			'delete-messages-unlimited',
			'edit-messages',
			'silent-send-state',
			'chat-read-last',
			'federation-v1',
			'federation-v2',
			'ban-v1',
			'chat-reference-id',
			'mention-permissions',
			'edit-messages-note-to-self',
			'archived-conversations',
			'talk-polls-drafts',
		],
		'features-local': [
			'favorites',
			'chat-read-status',
			'listable-rooms',
			'phonebook-search',
			'temp-user-avatar-api',
			'unified-search',
			'avatar',
			'remind-me-later',
			'note-to-self',
			'archived-conversations',
		],
		config: {
			attachments: {
				allowed: true,
				folder: '/Talk',
			},
			call: {
				enabled: true,
				'breakout-rooms': true,
				recording: true,
				'recording-consent': 0,
				'supported-reactions': ['❤️', '🎉', '👏', '👍', '👎', '😂', '🤩', '🤔', '😲', '😥'],
				'predefined-backgrounds': ['1_office', '2_home', '3_abstract'],
				'can-upload-background': true,
				'sip-enabled': true,
				'sip-dialout-enabled': true,
				'can-enable-sip': true,
				'start-without-media': false,
			},
			chat: {
				'max-length': 32000,
				'read-privacy': 0,
				'has-translation-providers': true,
				'typing-privacy': 0,
			},
			conversations: {
				'can-create': true,
			},
			federation: {
				enabled: false,
				'incoming-enabled': false,
				'outgoing-enabled': false,
				'only-trusted-servers': true,
			},
			previews: {
				'max-gif-size': 3145728,
			},
			signaling: {
				'session-ping-limit': 200,
			},
		},
		'config-local': {
			attachments: [
				'allowed',
				'folder',
			],
			call: [
				'predefined-backgrounds',
				'can-upload-background',
				'start-without-media',
			],
			chat: [
				'read-privacy',
				'has-translation-providers',
				'typing-privacy',
			],
			conversations: [
				'can-create',
			],
			federation: [],
			previews: [
				'max-gif-size',
			],
			signaling: [],
		},
		version: '20.0.0-dev.0',
	}
}

export const mockedRemotes = {
	'https://nextcloud1.local': { ...mockedCapabilities, hash: 'abc123', tokens: ['TOKEN3FED1'] },
	'https://nextcloud2.local': { ...mockedCapabilities, hash: 'def123', tokens: ['TOKEN5FED2'] },
}
