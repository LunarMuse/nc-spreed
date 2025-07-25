<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import { showError } from '@nextcloud/dialogs'
import { getLanguage, t } from '@nextcloud/l10n'
import { spawnDialog } from '@nextcloud/vue/functions/dialog'
import { computed } from 'vue'
import { isNavigationFailure, NavigationFailureType } from 'vue-router'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import NcButton from '@nextcloud/vue/components/NcButton'
import IconCheckUnderline from 'vue-material-design-icons/CheckUnderline.vue'
import IconDelete from 'vue-material-design-icons/Delete.vue'
import ConfirmDialog from '../../components/UIShared/ConfirmDialog.vue'
import { CONVERSATION } from '../../constants.ts'
import { getTalkConfig, hasTalkFeature } from '../../services/CapabilitiesManager.ts'

const props = defineProps<{
	token: string
	objectType: string
	isHighlighted: boolean
}>()

const supportsArchive = hasTalkFeature('local', 'archived-conversations-v2')
const retentionEventPeriod = computed(() => getTalkConfig(props.token, 'conversations', 'retention-event'))
const retentionPhonePeriod = computed(() => getTalkConfig(props.token, 'conversations', 'retention-phone'))
const retentionInstantMeetingPeriod = computed(() => getTalkConfig(props.token, 'conversations', 'retention-instant-meetings'))

const store = useStore()
const router = useRouter()
const route = useRoute()

const isModerator = computed(() => store.getters.isModerator)

const expirationDuration = computed(() => {
	if (props.objectType === CONVERSATION.OBJECT_TYPE.EVENT) {
		return retentionEventPeriod.value
	} else if (props.objectType === CONVERSATION.OBJECT_TYPE.PHONE_TEMPORARY) {
		return retentionPhonePeriod.value
	} else if (props.objectType === CONVERSATION.OBJECT_TYPE.INSTANT_MEETING) {
		return retentionInstantMeetingPeriod.value
	}
	return 0
})

const isShown = computed(() => isModerator.value || expirationDuration.value !== 0)

const descriptionLabel = computed(() => {
	if (expirationDuration.value === 0) {
		return t('spreed', 'Would you like to delete this conversation?')
	}
	const expirationDurationFormatted = new Intl.RelativeTimeFormat(getLanguage(), { numeric: 'always' }).format(expirationDuration.value!, 'days')
	return t('spreed', 'This conversation will be automatically deleted for everyone {expirationDurationFormatted} of no activity.', { expirationDurationFormatted })
})

/**
 * Delete conversation
 */
async function deleteEventConversation() {
	try {
		if (route?.params?.token === props.token) {
			await router.push({ name: 'root' })
				.catch((failure) => !isNavigationFailure(failure, NavigationFailureType.duplicated) && Promise.reject(failure))
		}
		await store.dispatch('deleteConversationFromServer', { token: props.token })
	} catch (error) {
		console.error(`Error while deleting conversation ${error}`)
		showError(t('spreed', 'Error while deleting conversation'))
	}
}

/**
 * Unbind conversation from object
 */
async function resetObjectConversation() {
	await store.dispatch('unbindConversationFromObject', { token: props.token })
}

/**
 * Show confirmation dialog
 */
async function showConfirmationDialog() {
	spawnDialog(ConfirmDialog, {
		name: t('spreed', 'Delete conversation'),
		message: t('spreed', 'Are you sure you want to delete this conversation?'),
		buttons: [
			{
				label: t('spreed', 'No'),
				variant: 'tertiary',
			},
			{
				label: t('spreed', 'Yes'),
				variant: 'error',
				callback: () => {
					deleteEventConversation()
				},
			},
		],
	})
}
</script>

<template>
	<div v-if="isShown"
		class="conversation-actions"
		:class="{ 'conversation-actions--highlighted': props.isHighlighted }">
		<p>{{ descriptionLabel }}</p>
		<div v-if="isModerator"
			class="conversation-actions__buttons">
			<NcButton variant="error"
				@click="showConfirmationDialog">
				<template #icon>
					<IconDelete />
				</template>
				{{ t('spreed', 'Delete now') }}
			</NcButton>
			<NcButton v-if="supportsArchive"
				variant="secondary"
				@click="resetObjectConversation">
				<template #icon>
					<IconCheckUnderline />
				</template>
				{{ t('spreed', 'Keep') }}
			</NcButton>
		</div>
	</div>
</template>

<style scoped lang="scss">
.conversation-actions {
	padding: calc(var(--default-grid-baseline) * 2) var(--default-grid-baseline);
	transition: background-color var(--animation-quick) ease;

	&--highlighted {
		background-color: var(--color-primary-element-light);
		p {
			color: var(--color-main-text);
		}
		border-radius: var(--border-radius);
	}

	&__buttons {
		display: flex;
		justify-content: center;
		gap: var(--default-grid-baseline);
		margin-top: var(--default-grid-baseline);
	}
}
</style>
