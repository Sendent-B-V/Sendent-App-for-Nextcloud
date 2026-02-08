import { getLoggerBuilder } from '@nextcloud/logger'

const logger = getLoggerBuilder()
	.setApp('sendent')
	.detectUser()
	.build()

export default logger
