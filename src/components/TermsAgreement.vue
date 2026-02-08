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
import { ref, onMounted } from 'vue'
import { useTermsStore } from '../stores/terms'

const termsStore = useTermsStore()
const checked = ref(false)

onMounted(() => {
	termsStore.checkAgreement()
})

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
