const config = require('./webpack.config');
const mix = require('laravel-mix');
require('laravel-mix-eslint');

function resolve(dir) {
  return path.join(
    __dirname,
    '/resources/js',
    dir
  );
}

Mix.listen('configReady', webpackConfig => {
  // Add "svg" to image loader test
  const imageLoaderConfig = webpackConfig.module.rules.find(
    rule =>
      String(rule.test) ===
      String(/(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/)
  );
  imageLoaderConfig.exclude = resolve('icons');
});

mix.webpackConfig(config);
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

mix
  .js('resources/js/app.js', 'public/js')
  .extract(['vue', 'axios', 'vuex', 'vue-router'], 'js/vendor~utils-1.js')
  .extract(['idb-keyval', 'js-cookie', 'nprogress', 'path'], 'js/vendor~utils-2.js')
  .extract(['element-ui'], 'js/vendor~utils-3.js')
  .extract(['vue-i18n'], 'js/vendor~utils-4.js')
  .extract(['mdbvue'], 'js/vendor~utils-5.js')
  .extract(['camelcase'], 'js/vendor~utils-6.js')
  .extract(['moment'], 'js/vendor~utils-7.js')
  .extract(['laravel-echo'], 'js/vendor~utils-8.js')
  .extract(['xlsx'], 'js/vendor~utils-9.js')
  .extract(['vue-loading-overlay'], 'js/vendor~utils-10.js')
  .extract(['pusher-js'], 'js/vendor~utils-11.js')
  .extract(['dropzone'], 'js/vendor~utils-12.js')
  .extract(['sortablejs'], 'js/vendor~utils-13.js')
  .extract(['clipboard'], 'js/vendor~utils-14.js')
  .extract(['jszip'], 'js/vendor~utils-15.js')
  .extract(['highlight.js'], 'js/vendor~utils-16.js')
  .extract(['vue-mj-daterangepicker'], 'js/vendor~utils-17.js')
  .extract(['vue-tables-2'], 'js/vendor~utils-18.js')
  .extract(['tui-editor'], 'js/vendor~utils-19.js')
  .extract(['echarts'], 'js/vendor~utils-20.js')
  .extract()
  .options({
    processCssUrls: false,
  })
  .sass('resources/js/styles/index.scss', 'public/css/app.css', {
    implementation: require('node-sass'),
  });

if (mix.inProduction()) {
  mix.version();
} else {
  if (process.env.LARAVUE_USE_ESLINT === 'true') {
    mix.eslint();
  }
  // Development settings
  mix
    .sourceMaps()
    .webpackConfig({
      devtool: 'cheap-eval-source-map', // Fastest for development
    });
}
