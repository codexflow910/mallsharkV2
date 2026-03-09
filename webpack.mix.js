const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .react()
    .sass('resources/sass/app.scss', 'public/css')
    .options({ legacyNodePolyfills: true })
    .webpackConfig({ resolve: {  
        fallback: 
        { "path": require.resolve("path-browserify"),
        "stream": require.resolve("stream-browserify"), 
        "os": require.resolve("os-browserify/browser"),"https": require.resolve("https-browserify"),"http": require.resolve("stream-http"),"crypto": require.resolve("crypto-browserify"),"fs": false,
    }, }, })
    .setResourceRoot("/");