const path = require( "path" );
const defaultConfig = require( "@wordpress/scripts/config/webpack.config" );

module.exports = () => {
    return {
        ...defaultConfig,
        entry: {
            "yoast-toggle": "./assets/js/src/yoast-toggle.js",
            "query-logger": "./assets/js/src/query-logger/index.js",
        },
        output: {
            path: path.resolve(__dirname, 'assets/js/dist'),
            filename: '[name].js',
        },
        externals: {
            ...defaultConfig.externals,
            "styled-components": "yoast.styledComponents"
        },
    }
}