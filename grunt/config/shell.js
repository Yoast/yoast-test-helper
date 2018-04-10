// See https://github.com/sindresorhus/grunt-shell
module.exports = {
	composerInstall: {
		command: "composer install"
	},
	composerInstallProduction: {
		command: "composer install --prefer-dist --optimize-autoloader --no-dev"
	},
};
