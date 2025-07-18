<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<li class="stun-server">
		<!-- "stun:" scheme is untranslated -->
		<div class="stun-server__wrapper">
			<label :for="`stun_server_${index}`">stun:</label>

			<NcTextField ref="stun_server"
				v-model="stunServer"
				:input-id="`stun_server_${index}`"
				name="stun_server"
				class="stun-server__input"
				placeholder="stunserver:port"
				:disabled="loading"
				:aria-label="t('spreed', 'STUN server URL')"
				label-outside />
		</div>

		<IconAlertCircleOutline v-show="!isValidServer"
			class="stun-server__alert"
			:title="t('spreed', 'The server address is invalid')"
			fill-color="#E9322D" />

		<NcButton v-show="!loading"
			variant="tertiary"
			:aria-label="t('spreed', 'Delete this server')"
			@click="removeServer">
			<template #icon>
				<IconDeleteOutline :size="20" />
			</template>
		</NcButton>
	</li>
</template>

<script>
import { t } from '@nextcloud/l10n'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import IconAlertCircleOutline from 'vue-material-design-icons/AlertCircleOutline.vue'
import IconDeleteOutline from 'vue-material-design-icons/DeleteOutline.vue'

export default {
	name: 'StunServer',

	components: {
		IconAlertCircleOutline,
		IconDeleteOutline,
		NcButton,
		NcTextField,
	},

	props: {
		server: {
			type: String,
			default: '',
			required: true,
		},

		index: {
			type: Number,
			default: -1,
			required: true,
		},

		loading: {
			type: Boolean,
			default: false,
		},
	},

	emits: ['removeServer', 'update:server'],

	computed: {
		stunServer: {
			get() {
				return this.server
			},

			set(value) {
				this.$emit('update:server', value)
			},
		},

		isValidServer() {
			let server = this.server

			// Remove HTTP or HTTPS protocol, if provided
			if (server.startsWith('https://')) {
				server = server.slice(8)
			} else if (server.startsWith('http://')) {
				server = server.slice(7)
			}

			const parts = server.split(':')

			return parts.length === 2
				&& parts[1].match(/^([1-9]\d{0,4})$/) !== null
				&& parseInt(parts[1]) <= Math.pow(2, 16)
		},
	},

	methods: {
		t,
		removeServer() {
			this.$emit('removeServer', this.index)
		},
	},
}
</script>

<style lang="scss" scoped>
.stun-server {
	display: flex;
	align-items: center;
	margin-bottom: calc(var(--default-grid-baseline) * 2);
	gap: var(--default-grid-baseline);

	&__wrapper {
		display: flex;
		align-items: center;
		gap: 4px;
		width: 300px;
	}

	// Override NcInputField styles
	&__input {
		margin-block-start: 0 !important;
	}

	&__alert {
		width: var(--default-clickable-area);
		height: var(--default-clickable-area);
	}
}
</style>
