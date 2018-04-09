// See https://github.com/sindresorhus/grunt-shell
module.exports = {
	composerInstall: {
		command: 'composer install'
	},
	composerInstallNoDev: {
		command: 'composer install --no-dev'
	},
};