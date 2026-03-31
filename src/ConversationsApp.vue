<template>
	<NcContent app-name="sendent">
		<NcAppContent>
			<div class="sendent-conversations">
				<NcEmptyContent
					name="Sendent Conversations"
					description="This is a placeholder for the conversations module.">
					<template #icon>
						<span class="icon-comment" />
					</template>
				</NcEmptyContent>
			</div>
		</NcAppContent>
	</NcContent>
</template>

<script setup lang="ts">
import NcContent from '@nextcloud/vue/components/NcContent'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
</script>

<style scoped>
.sendent-conversations {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
}
</style>