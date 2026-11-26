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
	<!-- Without a link there is nothing for a focus trap to hold on to -->
	<NcPopover placement="bottom-start" :no-focus-trap="!definition.helpUrl">
		<template #trigger>
			<NcButton variant="tertiary-no-background"
				size="small"
				:aria-label="ariaLabel">
				<template #icon>
					<NcIconSvgWrapper :path="INFO_ICON_PATH" :size="18" />
				</template>
			</NcButton>
		</template>
		<div class="setting-help">
			<p v-if="definition.description">
				{{ definition.description }}
			</p>
			<ul v-if="describedOptions.length">
				<li v-for="opt in describedOptions" :key="opt.value">
					<strong>{{ opt.label }}:</strong> {{ opt.description }}
				</li>
			</ul>
			<p v-if="definition.format">
				{{ definition.format }}
			</p>
			<p v-if="definition.helpUrl">
				<a :href="definition.helpUrl" target="_blank" rel="noopener noreferrer">
					{{ t('sendent', 'Read more in the documentation') }}
				</a>
			</p>
		</div>
	</NcPopover>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { translate as t } from '@nextcloud/l10n'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcIconSvgWrapper from '@nextcloud/vue/components/NcIconSvgWrapper'
import NcPopover from '@nextcloud/vue/components/NcPopover'
import type { SettingDefinition } from '../../types/settings'

/** Material Design Icons "information-outline" */
const INFO_ICON_PATH = 'M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z'

const props = defineProps<{
	definition: SettingDefinition
	label: string
}>()

/** Rendered as an attribute, so placeholder escaping and sanitizing are disabled. */
const ariaLabel = computed(() =>
	t('sendent', 'Explain "{label}"', { label: props.label }, undefined, { escape: false, sanitize: false }),
)

const describedOptions = computed(() =>
	(props.definition.options ?? []).filter(opt => opt.description),
)
</script>

<style scoped>
.setting-help {
	max-width: 320px;
	padding: 12px;
	line-height: 1.5;
}

.setting-help p {
	margin: 0;
}

.setting-help ul {
	margin: 0;
	padding-inline-start: 18px;
	list-style: disc;
}

.setting-help li + li {
	margin-top: 4px;
}

.setting-help p + ul,
.setting-help p + p,
.setting-help ul + p {
	margin-top: 8px;
}

.setting-help a {
	color: var(--color-primary-element);
	text-decoration: underline;
}
</style>
