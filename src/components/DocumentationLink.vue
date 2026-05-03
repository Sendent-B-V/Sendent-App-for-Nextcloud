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
	<p class="sendent-doc-link">
		{{ intro }}
		<span v-for="(link, i) in links" :key="link.href" class="sendent-doc-link__item">
			<a :href="link.href" target="_blank" rel="noopener noreferrer">{{ link.label }}</a>
			<span v-if="i < links.length - 1" class="sendent-doc-link__sep"> - </span>
		</span>
	</p>
</template>

<script setup lang="ts">
interface DocLink {
	href: string
	label: string
}

withDefaults(defineProps<{
	links: DocLink[]
	intro?: string
}>(), { intro: 'Find the latest documentation:' })
</script>

<style scoped>
.sendent-doc-link {
	margin: 0 0 16px 0;
	color: var(--color-text-maxcontrast);
	font-size: 13px;
}

.sendent-doc-link a {
	color: var(--color-primary-element);
	text-decoration: underline;
}

.sendent-doc-link__sep {
	color: var(--color-text-maxcontrast);
}
</style>
