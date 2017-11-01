// generated on 2017-06-27 using generator-webapp 3.0.1
const gulp = require('gulp');
const gulpLoadPlugins = require('gulp-load-plugins');
const browserSync = require('browser-sync').create();
const del = require('del');
const wiredep = require('wiredep').stream;
const runSequence = require('run-sequence');
const cssPrefix = require('gulp-css-prefix');
const pagebuilder = require('gulp-pagebuilder');
const util = require('util');
const fs = require('fs');
const inject = require('gulp-inject');
const $ = gulpLoadPlugins();
const reload = browserSync.reload;
const gutil = require('gulp-util');

const distCssDir = '../web/assets/css'


let dev = true;


gulp.task('serve', () => {
    runSequence(['clean','wiredep'], ['createStyles', 'scripts', 'fonts'],  () => {
        browserSync.init({
            notify: true,
            port: 12000,
            proxy: 'http://widgetbeheer-api.dev/demo/'
            /*
            server: {
                baseDir: [distCssDir, 'app'],
                routes: {
                    '/bower_components': 'bower_components'
                }
            }
            */
        });
        gulp.watch([
            'app/templates/**/*.html',
            'app/images/**/*',
            'app/tmp/fonts/**/*'
        ]
        );

        gulp.watch('app/styles/**/*.scss', ['createStyles']);
        gulp.watch('app/scripts/**/*.js', ['scripts']);
        gulp.watch('app/fonts/**/*', ['fonts']);
        gulp.watch('bower.json', ['wiredep', 'fonts']);
    });
});



gulp.task('createStyles', function(callback) {
    runSequence('styles',  'injectNonCompiledCss',  callback);
});



gulp.task('injectNonCompiledCss', function () {
   return  gulp.src('app/**/*.html')
            .pipe(inject(gulp.src('app/css/nonCompiled.css', {read: false}), {starttag: '<!-- inject:noncompiled:{{ext}} -->'}))
            .pipe(gulp.dest('./app'));
});


gulp.task('callback-example', function(callback) {
    // Use the callback in the async function
    fs.readFile('.tmp/styles/main.css', function(err, file) {
        console.log(file);
        callback();
    });
});



gulp.task('styles', () => {
  return gulp.src('app/styles/*.scss')
    .pipe($.plumber())
    .pipe($.if(dev, $.sourcemaps.init()))
    .pipe($.sass.sync({
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['.']
    }).on('error', $.sass.logError))
    .pipe($.autoprefixer({browsers: ['> 1%', 'last 2 versions', 'Firefox ESR']}))
    .pipe($.if(dev, $.sourcemaps.write()))
    .pipe(gulp.dest(distCssDir))
    .pipe(browserSync.stream());
});


gulp.task('prefixcss',  function() {
    return gulp.src('.tmp/styles-noprefix/main.css')
        .on('start', function(){ gutil.log('prefixing start...'); })
        .pipe(cssPrefix('cnw_'))
        .pipe(gulp.dest('.tmp/styles/'));
});




gulp.task('scripts', () => {
  return gulp.src('app/scripts/**/*.js')
    .pipe($.plumber())
    .pipe($.if(dev, $.sourcemaps.init()))
    .pipe($.babel())
    .pipe($.if(dev, $.sourcemaps.write('.')))
    .pipe(gulp.dest('.app/tmp/scripts'))
    .pipe(reload({stream: true}));
});

function lint(files) {
  return gulp.src(files)
    .pipe($.eslint({ fix: true }))
    .pipe(reload({stream: true, once: true}))
    .pipe($.eslint.format())
    .pipe($.if(!browserSync.active, $.eslint.failAfterError()));
}

gulp.task('lint', () => {
  return lint('app/scripts/**/*.js')
    .pipe(gulp.dest('app/scripts'));
});
gulp.task('lint:test', () => {
  return lint('test/spec/**/*.js')
    .pipe(gulp.dest('test/spec'));
});

gulp.task('html', ['createStyles', 'scripts'], () => {
  return gulp.src('app/**/*.html')
    .pipe($.useref({searchPath: [distCssDir, 'app', '.']}))
    .pipe($.if(/\.js$/, $.uglify({compress: {drop_console: true}})))
    .pipe($.if(/\.css$/, $.cssnano({safe: true, autoprefixer: false})))
    .pipe($.if(/\.html$/, $.htmlmin({
      collapseWhitespace: false,
      minifyCSS: false,
      minifyJS: {compress: {drop_console: true}},
      processConditionalComments: true,
      removeComments: false,
      removeEmptyAttributes: false,
      removeScriptTypeAttributes: false,
      removeStyleLinkTypeAttributes: false
    })))

    .pipe(gulp.dest('dist'));
});

gulp.task('images', () => {
  return gulp.src('app/images/**/*')
    .pipe($.cache($.imagemin()))
    .pipe(gulp.dest('dist/images'));
});

gulp.task('fulltest', () => {
  return gulp.src('app/fulltest/**/*')
    .pipe(gulp.dest('dist/fulltest'));
});

gulp.task('fonts', () => {
  return gulp.src(require('main-bower-files')('**/*.{eot,svg,ttf,woff,woff2}', function (err) {})
    .concat('app/fonts/**/*'))
    .pipe($.if(dev, gulp.dest('.tmp/fonts'), gulp.dest('dist/fonts')));
});

gulp.task('extras', () => {
  return gulp.src([
    'app/*',
    '!app/templates/**/*.html'
  ], {
    dot: true
  }).pipe(gulp.dest('dist'));
});

gulp.task('clean', del.bind(null, ['app/tmp/', 'dist']));

gulp.task('serve:dist', ['default'], () => {
  browserSync.init({
    notify: false,
    port: 12000,
    server: {
      baseDir: ['dist']
    }
  });
});

gulp.task('serve:test', ['scripts'], () => {
  browserSync.init({
    notify: false,
    port: 12000,
    ui: false,
    server: {
      baseDir: 'test',
      routes: {
        '/scripts': 'app/tmp/scripts',
        '/bower_components': 'bower_components'
      }
    }
  });

  gulp.watch('app/scripts/**/*.js', ['scripts']);
  gulp.watch(['test/spec/**/*.js', 'test/index.html']).on('change', reload);
  gulp.watch('test/spec/**/*.js', ['lint:test']);
});

// inject bower components
gulp.task('wiredep', () => {


  gulp.src('app/*.html')
    .pipe(wiredep({
      exclude: ['bootstrap'],
      ignorePath: /^(\.\.\/)*\.\./
    }))
    .pipe(gulp.dest('./app'));
});

gulp.task('build', [ 'html', 'images', 'fonts', 'extras', 'fulltest'],  () => {
  return gulp.src('dist/**/*')
      .pipe($.size({title: 'build', gzip: true}));
});


gulp.task('componentloader', function () {
    gulp.src('components/*.html')
        .pipe(pagebuilder('components'))
        .pipe(gulp.dest('app/'));
});

gulp.task('default', () => {
  return new Promise(resolve => {
    dev = false;
    runSequence(['clean', 'wiredep'], 'build', resolve);
  });
});
