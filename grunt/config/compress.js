// See https://github.com/gruntjs/grunt-contrib-compress
module.exports = {
	artifact: {
		options: {
			archive: "artifact.zip",
		},
		files: [
			{
				cwd: "artifact/",
				src: [ "**" ],
				dest: "yoast-test-helper/",
			},
		],
	},
};
