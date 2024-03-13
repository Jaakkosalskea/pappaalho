// Set theme dir
const themeDir = './';
const proxyUrl = 'http://pappaalho.test';

module.exports = {
  cssnano: {
    "preset": [
      "cssnano-preset-advanced",
      {
        "discardComments": {
          "removeAll": true
        }
      }
    ],
  },
  size: {
    gzip: true,
    uncompressed: true,
    pretty: true,
    showFiles: true,
    showTotal: false,
  },
  rename: {
    min: {
      suffix: '.min'
    }
  },
  browsersync: {
    // Important! If src is wrong, styles will not inject to the browser
    src: [
      themeDir + '**/*.php',
      themeDir + 'assets/css/**/*',
      themeDir + 'assets/js/dev/**/*'
    ],
    opts: {
      logLevel: 'debug',
      injectChanges: true,
      proxy: proxyUrl,
      browser: 'Google Chrome',
      open: false,
      notify: true,
    },
  },
  styles: {
    src: themeDir + 'assets/sass/*.scss',
    development: themeDir + 'assets/css/dev/',
    production: themeDir + 'assets/css/prod/',
    watch: {
      development: themeDir + 'assets/sass/**/*.scss',
      production: themeDir + 'assets/css/dev/*.css',
    },
    stylelint: {
      src: themeDir + 'assets/sass/**/*.scss',
      opts: {
        fix: false,
        reporters: [{
          formatter: 'string',
          console: true,
          failAfterError: false,
          debug: false
        }]
      },
    },
    opts: {
      development: {
        verbose: true,
        bundleExec: false,
        outputStyle: 'expanded',
        debugInfo: true,
        errLogToConsole: true,
        includePaths: [themeDir + 'node_modules/'],
        quietDeps: true,
      },
      production: {
        verbose: false,
        bundleExec: false,
        outputStyle: 'compressed',
        debugInfo: false,
        errLogToConsole: false,
        includePaths: [themeDir + 'node_modules/'],
        quietDeps: true,
      }
    }
  },
  js: {
    src: themeDir + 'assets/js/src/*.js',
    watch: themeDir + 'assets/js/src/**/*',
    production: themeDir + 'assets/js/prod/',
    development: themeDir + 'assets/js/dev/',
  },
  php: {
    watch: [
      themeDir + '*.php',
      themeDir + 'inc/**/*.php',
      themeDir + 'template-parts/**/*.php'
    ]
  },
  phpcs: {
    opts: {
      bin: '/opt/homebrew/bin/phpcs',
      standard: 'PSR2',
      warningSeverity: 0
    }
  }
};
