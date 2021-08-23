module.exports = grunt = ( {
    webpack: {
        command: "./node_modules/.bin/cross-env NODE_ENV=development yarn run wp-scripts build --config webpack.config.js",
    }
} );