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
	<div class="outlook-settings-tab">
		<GeneralSettings />
		<TalkSettings />
		<ArchivingSettings />
		<DomainExceptions />
		<AttachmentSettings />
		<FileHandlingSettings />
		<SecureMailSettings />
		<GuestAccountSettings />
		<AdvancedTheming />
	</div>
</template>

<script setup lang="ts">
import GeneralSettings from './GeneralSettings.vue'
import TalkSettings from './TalkSettings.vue'
import ArchivingSettings from './ArchivingSettings.vue'
import DomainExceptions from './DomainExceptions.vue'
import AttachmentSettings from './AttachmentSettings.vue'
import FileHandlingSettings from './FileHandlingSettings.vue'
import SecureMailSettings from './SecureMailSettings.vue'
import GuestAccountSettings from './GuestAccountSettings.vue'
import AdvancedTheming from './AdvancedTheming.vue'
</script>
