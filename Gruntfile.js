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
		},
		pkg,
	};

	// Load Grunt configurations and tasks.
	loadGruntConfig( grunt, {
		configPath: path.join(process.cwd(), project.paths.config),
		data: project,
	} );
};