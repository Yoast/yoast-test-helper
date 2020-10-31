const path = require( "path" );
const loadGruntConfig = require( "load-grunt-config" );

module.exports = function ( grunt ) {
	require( "jit-grunt" )( grunt );
	require( "time-grunt" )( grunt );

	const pkg = grunt.file.readJSON( "package.json" );
	const pluginVersion = pkg.yoast.pluginVersion;

	// Define project configuration.
	var project = {
		pluginVersion: pluginVersion,
		pluginSlug: "yoast-test-helper",
		pluginMainFile: "yoast-test-helper.php",
		paths: {
			get config() {
				return this.grunt + "config/";
			},
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
