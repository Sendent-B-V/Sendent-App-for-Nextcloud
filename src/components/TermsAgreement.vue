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
	<div v-if="!termsStore.agreed" class="sendent-terms">
		<div class="sendent-terms__card">
			<h2>Terms &amp; Conditions</h2>
			<p>
				By using Sendent, you agree to the
				<a href="https://www.sendent.com/terms" target="_blank" rel="noopener">
					Terms &amp; Conditions
				</a>.
			</p>
			<div class="sendent-terms__actions">
				<label class="sendent-terms__checkbox">
					<input v-model="checked"
						type="checkbox">
					I agree to the Terms &amp; Conditions
				</label>
				<button class="primary"
					:disabled="!checked || termsStore.loading"
					@click="onAgree">
					Continue
				</button>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTermsStore } from '../stores/terms'

const termsStore = useTermsStore()
const checked = ref(false)

/**
 *
 */
async function onAgree() {
	if (checked.value) {
		await termsStore.agree()
	}
}
</script>

<style scoped>
.sendent-terms {
	display: flex;
	justify-content: center;
	padding: 40px 20px;
}

.sendent-terms__card {
	max-width: 500px;
	padding: 30px;
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
}

.sendent-terms__actions {
	margin-top: 20px;
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.sendent-terms__checkbox {
	display: flex;
	align-items: center;
	gap: 8px;
	cursor: pointer;
}
</style>
