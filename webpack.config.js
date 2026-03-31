const webpackConfig = require('@nextcloud/webpack-vue-config')
const path = require('path')

// Override entry points
webpackConfig.entry = {
	settings: path.join(__dirname, 'src', 'settings-main.ts'),
	filelist: path.join(__dirname, 'src', 'filelist.ts'),
	conversations: path.join(__dirname, 'src', 'conversations-main.ts'),
}

// Override the built-in TS rule to enable transpileOnly + Vue SFC support
const tsRule = webpackConfig.module.rules.find(r => r.test?.toString().includes('tsx'))
if (tsRule) {
	tsRule.use = [
		'babel-loader',
		{
			loader: 'ts-loader',
			options: {
				transpileOnly: true,
				appendTsSuffixTo: [/\.vue$/],
			},
		},
	]
}

module.exports = webpackConfig
