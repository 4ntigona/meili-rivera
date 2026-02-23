const wpConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

// Helper to create a configuration clone
const createConfig = (name, entry, isModule = false) => {
    const config = {
        ...wpConfig,
        name,
        entry,
        // Clone plugins and specifically re-instantiate the dependency extraction plugin
        plugins: wpConfig.plugins.map((plugin) => {
            if (plugin.constructor.name === 'DependencyExtractionWebpackPlugin') {
                return new DependencyExtractionWebpackPlugin({
                    ...plugin.options,
                });
            }
            return plugin;
        }),
    };

    if (isModule) {
        config.output = {
            ...config.output,
            module: true,
            clean: false,
            library: {
                type: 'module',
            },
        };
        config.experiments = {
            outputModule: true,
        };
    } else {
        // Ensure classic build doesn't have module settings
        config.output = {
            ...config.output,
            module: false,
            clean: false,
            // library type will default to 'window' or similar via wpConfig
        };
        config.experiments = {
            ...config.experiments,
            outputModule: false,
        };
    }

    return config;
};

module.exports = [
    createConfig('editor', {
        'blocks/filters/index': path.resolve(process.cwd(), 'src/blocks/filters/index.js'),
        'blocks/search-bar/index': path.resolve(process.cwd(), 'src/blocks/search-bar/index.js'),
    }),
    createConfig('view', {
        'blocks/filters/view': path.resolve(process.cwd(), 'src/blocks/filters/view.js'),
    }, true),
];
