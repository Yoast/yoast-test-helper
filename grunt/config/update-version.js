// See https://github.com/Yoast/plugin-grunt-tasks
module.exports = {
	options: {
		version: "<%= pluginVersion %>",
	},
	readme: {
		options: {
			regEx: /(Stable tag:\s+)(\d+(\.\d+){0,3})([^\n^\.\d]?.*?)(\n)/,
			preVersionMatch: "$1",
			postVersionMatch: "$5",
		},
		src: "readme.txt",
	},
	packageJSON: {
		options: {
			regEx: /("version":\s+")(\d+(\.\d+){0,3})([^\n^\.\d]?.*?)(",)/,
			preVersionMatch: "$1",
			postVersionMatch: "$5",
		},
		src: "package.json",
	},
	// When changing or adding entries, make sure to update `aliases.yml` for "update-version-trunk".
	pluginFile: {
		options: {
			regEx: /(\* Version:\s+)(\d+(\.\d+){0,3})([^\n^\.\d]?.*?)(\n)/,
			preVersionMatch: "$1",
			postVersionMatch: "$5",
		},
		src: "<%= pluginMainFile %>",
	},
	initializer: {
		options: {
			regEx: /(define\( \'YOAST_TEST_HELPER_VERSION\'\, \')(\d+(\.\d+){0,3})([^\.^\'\d]?.*?)(\' \);\n)/,
			preVersionMatch: "$1",
			postVersionMatch: "$5",
		},
		src: "<%= pluginMainFile %>",
	},
};
