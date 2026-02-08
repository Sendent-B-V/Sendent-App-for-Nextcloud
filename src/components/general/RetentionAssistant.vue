<template>
	<div class="retention">
		<h3>Retention Assistant</h3>
		<div v-if="retentionStore.loading" class="retention__loading">
			<span class="icon-loading" />
			Checking retention configuration...
		</div>
		<div v-else class="retention__wizard">
			<!-- Step 1: Automated Tagging App -->
			<div class="retention__step"
				:class="{ 'retention__step--ok': retentionStore.hasTaggingApp }">
				<div class="retention__step-number">
					1
				</div>
				<div class="retention__step-content">
					<div class="retention__step-header">
						<span>Files Automated Tagging app</span>
						<span class="retention__step-icon"
							:class="retentionStore.hasTaggingApp ? 'retention__step-icon--ok' : 'retention__step-icon--fail'">
							{{ retentionStore.hasTaggingApp ? '&#x2713;' : 'X' }}
						</span>
					</div>
					<p v-if="!retentionStore.hasTaggingApp" class="retention__step-info">
						Please install the "Files automated tagging" app from the Nextcloud app store.
					</p>
				</div>
			</div>

			<!-- Step 2: Upload Tag Workflow -->
			<div v-if="retentionStore.hasTaggingApp"
				class="retention__step"
				:class="{ 'retention__step--ok': retentionStore.hasUploadWorkflow }">
				<div class="retention__step-number">
					2
				</div>
				<div class="retention__step-content">
					<div class="retention__step-header">
						<span>Upload tag workflow</span>
						<span class="retention__step-icon"
							:class="retentionStore.hasUploadWorkflow ? 'retention__step-icon--ok' : 'retention__step-icon--warn'">
							{{ retentionStore.hasUploadWorkflow ? '&#x2713;' : '&#x26A0;' }}
						</span>
					</div>
					<div v-if="!retentionStore.hasUploadWorkflow" class="retention__step-action">
						<p>No workflow configured for tagging Sendent uploads.</p>
						<button @click="createUploadWorkflow">
							Create Workflow
						</button>
					</div>
				</div>
			</div>

			<!-- Step 3: Retention App -->
			<div class="retention__step"
				:class="{ 'retention__step--ok': retentionStore.hasRetentionApp }">
				<div class="retention__step-number">
					{{ retentionStore.hasTaggingApp ? '3' : '2' }}
				</div>
				<div class="retention__step-content">
					<div class="retention__step-header">
						<span>Files Retention app</span>
						<span class="retention__step-icon"
							:class="retentionStore.hasRetentionApp ? 'retention__step-icon--ok' : 'retention__step-icon--fail'">
							{{ retentionStore.hasRetentionApp ? '&#x2713;' : 'X' }}
						</span>
					</div>
					<p v-if="!retentionStore.hasRetentionApp" class="retention__step-info">
						Please install the "Files retention" app from the Nextcloud app store.
					</p>
				</div>
			</div>

			<!-- Step 4: Removed shares retention -->
			<div v-if="retentionStore.hasRetentionApp"
				class="retention__step"
				:class="{ 'retention__step--ok': retentionStore.hasRemovedRetention }">
				<div class="retention__step-number">
					{{ retentionStore.hasTaggingApp ? '4' : '3' }}
				</div>
				<div class="retention__step-content">
					<div class="retention__step-header">
						<span>Removed shares retention rule</span>
						<span class="retention__step-icon"
							:class="retentionStore.hasRemovedRetention ? 'retention__step-icon--ok' : 'retention__step-icon--warn'">
							{{ retentionStore.hasRemovedRetention ? '&#x2713;' : '&#x26A0;' }}
						</span>
					</div>
					<div v-if="!retentionStore.hasRemovedRetention" class="retention__step-action">
						<p>No retention rule for removed shares (default: 30 days after creation).</p>
						<button @click="createRemovedRetention">
							Create Rule
						</button>
					</div>
				</div>
			</div>

			<!-- Step 5: Expired shares retention -->
			<div v-if="retentionStore.hasRetentionApp"
				class="retention__step"
				:class="{ 'retention__step--ok': retentionStore.hasExpiredRetention }">
				<div class="retention__step-number">
					{{ retentionStore.hasTaggingApp ? '5' : '4' }}
				</div>
				<div class="retention__step-content">
					<div class="retention__step-header">
						<span>Expired shares retention rule</span>
						<span class="retention__step-icon"
							:class="retentionStore.hasExpiredRetention ? 'retention__step-icon--ok' : 'retention__step-icon--warn'">
							{{ retentionStore.hasExpiredRetention ? '&#x2713;' : '&#x26A0;' }}
						</span>
					</div>
					<div v-if="!retentionStore.hasExpiredRetention" class="retention__step-action">
						<p>No retention rule for expired shares (default: 90 days after creation).</p>
						<button @click="createExpiredRetention">
							Create Rule
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRetentionStore } from '../../stores/retention'

const retentionStore = useRetentionStore()

onMounted(async () => {
	retentionStore.loadInitialState()
	await retentionStore.checkConfiguration()
})

/**
 *
 */
async function createUploadWorkflow() {
	await retentionStore.createUploadWorkflow()
}

/**
 *
 */
async function createRemovedRetention() {
	// 30 days after creation (timeunit 0 = days, timeafter 0 = creation)
	await retentionStore.createRetentionRule('tag:removed', 30, 0, 0)
}

/**
 *
 */
async function createExpiredRetention() {
	// 90 days after creation
	await retentionStore.createRetentionRule('tag:expired', 90, 0, 0)
}
</script>

<style scoped>
.retention {
	margin-bottom: 24px;
}

.retention h3 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 12px;
}

.retention__loading {
	display: flex;
	align-items: center;
	gap: 8px;
	color: var(--color-text-maxcontrast);
}

.retention__wizard {
	position: relative;
	padding-left: 8px;
}

.retention__step {
	display: flex;
	gap: 12px;
	padding-bottom: 16px;
	position: relative;
}

/* Vertical connecting line */
.retention__step::before {
	content: '';
	position: absolute;
	left: 14px;
	top: 30px;
	bottom: 0;
	width: 2px;
	background: var(--color-border);
}

.retention__step:last-child::before {
	display: none;
}

.retention__step--ok::before {
	background: var(--color-success-text);
}

.retention__step-number {
	width: 28px;
	height: 28px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 600;
	font-size: 13px;
	flex-shrink: 0;
	border: 2px solid var(--color-border);
	background: var(--color-main-background);
	color: var(--color-text-maxcontrast);
	position: relative;
	z-index: 1;
}

.retention__step--ok .retention__step-number {
	border-color: var(--color-success-text);
	color: var(--color-success-text);
}

.retention__step-content {
	flex: 1;
	padding-top: 3px;
}

.retention__step-header {
	display: flex;
	align-items: center;
	gap: 8px;
	font-weight: 500;
}

.retention__step-icon {
	font-variant-emoji: text;
	font-weight: 600;
}

.retention__step-icon--ok {
	color: var(--color-success-text);
}

.retention__step-icon--fail {
	color: var(--color-error-text);
}

.retention__step-icon--warn {
	color: var(--color-warning);
}

.retention__step-info {
	margin-top: 4px;
	color: var(--color-text-maxcontrast);
	font-size: 13px;
}

.retention__step-action {
	margin-top: 6px;
}

.retention__step-action p {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 6px;
}
</style>
