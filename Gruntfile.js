var path = require( "path" );
var loadGruntConfig = require( "load-grunt-config" );
var timeGrunt = require( "time-grunt" );

module.exports = function ( grunt ) {
	timeGrunt( grunt );

	const pkg = grunt.file.readJSON( "package.json" );
	const pluginVersion = pkg.yoast.pluginVersion;

	/* Used to switch between development and release builds.
	Switches based on the grunt command (which is the third 'argv', after node and grunt,  so index 2).*/
	const developmentBuild = ! [ "create-rc", "release", "release:js", "artifact", "deploy:trunk", "deploy:master" ].includes( process.argv[ 2 ] );

	// Define project configuration.
	var project = {
		developmentBuild,
		pluginVersion: pluginVersion,
		pluginSlug: "yoast-test-helper",
		pluginMainFile: "yoast-test-helper.php",
		pluginVersionConstant: "YOAST_TEST_HELPER_VERSION",
		paths: {
			get config() {
				return this.grunt + "config/";
			},
			js: "assets/js/src/",
			jsDist: "assets/js/dist/",
			grunt: "grunt/",
			assets: "svn-assets/",
			svnCheckoutDir:  ".wordpress-svn",
			languages: "languages/",
		},
		files: {
			php: [
				"*.php",
				"src/**/*.php",
			],
			artifact: "artifact",
			js: [
				"assets/js/*.js",
			],
		},
		pkg,
	};

	// Load Grunt configurations and tasks.
	loadGruntConfig( grunt, {
		configPath: path.join( process.cwd(), "node_modules/@yoast/grunt-plugin-tasks/config/" ),
		overridePath: path.join( process.cwd(), project.paths.config ),
		data: project,
		jitGrunt: {
			staticMappings: {
				addtextdomain: "grunt-wp-i18n",
				makepot: "grunt-wp-i18n",
				glotpress_download: "grunt-glotpress",
				"update-version": "@yoast/grunt-plugin-tasks",
				"set-version": "@yoast/grunt-plugin-tasks",
			},
		},
	} );
};
