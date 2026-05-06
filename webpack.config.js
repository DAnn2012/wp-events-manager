const fs = require( 'fs' );
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const { BundleAnalyzerPlugin } = require( 'webpack-bundle-analyzer' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const isProduction = process.env.NODE_ENV === 'production';
const sourceRoot = path.resolve( __dirname, 'assets/src/js' );

function getScriptEntries( directory = sourceRoot, relativeDirectory = '' ) {
	const scriptEntries = {};

	fs.readdirSync( directory, { withFileTypes: true } ).forEach( ( item ) => {
		const absolutePath = path.join( directory, item.name );
		const relativePath = path.join( relativeDirectory, item.name ).replace( /\\/g, '/' );

		if ( item.isDirectory() ) {
			Object.assign( scriptEntries, getScriptEntries( absolutePath, relativePath ) );
			return;
		}

		if ( ! item.isFile() || ! item.name.endsWith( '.js' ) || item.name.endsWith( '.min.js' ) ) {
			return;
		}

		const entryName = relativePath.replace( /\.js$/, '' );
		scriptEntries[ entryName ] = `./assets/src/js/${ relativePath }`;
	} );

	return scriptEntries;
}

module.exports = {
	...defaultConfig,
	entry: getScriptEntries(),
	module: {
		...defaultConfig.module,
		rules: [
			{
				test: /jquery\.datetimepicker\.full\.js$/,
				parser: {
					amd: false,
				},
			},
			...( defaultConfig.module?.rules || [] ),
		],
	},
	output: {
		filename: `[name]${ isProduction ? '.min' : '' }.js`,
		path: path.resolve( __dirname, 'assets/dist/js' ),
		clean: true,
	},
	plugins: [
		process.env.WP_BUNDLE_ANALYZER && new BundleAnalyzerPlugin(),

		// WP_NO_EXTERNALS global variable controls whether scripts' assets get
		// generated, and the default externals set.
		! process.env.WP_NO_EXTERNALS && new DependencyExtractionWebpackPlugin(),
	].filter( Boolean ),
};
