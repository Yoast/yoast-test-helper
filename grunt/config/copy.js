// See https://github.com/gruntjs/grunt-contrib-copy
module.exports = {
	artifact: {
		files: [
			{
				expand: true,
				cwd: ".",
				src: [
					"assets/**",
					"src/**",
					"vendor/**",
					"license.txt",
					"README.md",
					"readme.txt",
					"yoast-test-helper.php",
				],
				dest: "artifact",
			},
		],
	},
};
