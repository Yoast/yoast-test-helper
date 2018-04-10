// See https://github.com/Yoast/plugin-grunt-tasks
module.exports = {
	packageJSON: {
		options: {
			base: "yoast",
			target: "pluginVersion",
		},
		src: "package.json",
	},
};
