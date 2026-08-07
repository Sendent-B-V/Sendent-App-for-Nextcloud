<!--
  - @copyright Copyright (c) 2026 Sendent B.V.
  -
  - @author Sendent B.V. <info@sendent.com>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -->
<template>
	<div>
		<SettingsSection title="Email Signature"
			:definitions="definitions"
			:labels="labels" />
		<SignaturePreview v-if="signatureEnabled" :template="signatureTemplate" />
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import SettingsSection from '../settings/SettingsSection.vue'
import SignaturePreview from '../settings/SignaturePreview.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'
import { useSettingsStore } from '../../stores/settings'

const definitions = getSettingsForSection('EmailSignature')

const labels: Record<string, string> = {
	enablesignaturepush: 'Enable signature push',
	signaturehtml: 'Signature template',
}

const store = useSettingsStore()
const signatureEnabled = computed(() => store.getValue(800) === 'True') // enablesignaturepush
const signatureTemplate = computed(() => store.getValue(801)) // signaturehtml
</script>
