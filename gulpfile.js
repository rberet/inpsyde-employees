const {src, dest, series, watch, parallel} = require('gulp');
var rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
var uglify = require('gulp-uglify');
var autoprefixer = require('gulp-autoprefixer');
var sourcemaps = require('gulp-sourcemaps');
var browserify = require('browserify');
var babelify = require('babelify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var browserSync = require('browser-sync').create();

var styleSRC = 'src/scss/style.scss';
var styleDisplay = 'src/scss/display.scss';
var styleEmployer = 'src/scss/employer.scss';
var styleURL = './assets/';

var jsSRC = './src/js/';
var jsEmployer = 'employer.js';
var jsDisplay = 'display.js';
var jsFiles = [jsDisplay, jsEmployer];
var jsURL = './assets/';



function style(done) {
    "use strict";
    return src([styleSRC, styleDisplay, styleEmployer])
        .pipe(sourcemaps.init())
        .pipe(sass({
            errorLogToConsole: true,
            outputStyle: 'compressed'
        }))
        .on('error', console.error.bind(console))
        .pipe(autoprefixer({
            overrideBrowserslist: ["defaults"],
            cascade: false
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(sourcemaps.write('./'))
        .pipe(dest(styleURL))
        .pipe(browserSync.stream());
    done();
}


function js(done) {
    "use strict";
    jsFiles.map(function (entry) {
        return browserify({
            entries: [jsSRC + entry]
        })
            .transform(babelify, {presets: ['@babel/env']})
            .bundle()
            .pipe(source(entry))
            .pipe(rename({extname: '.min.js'}))
            .pipe(buffer())
            .pipe(sourcemaps.init({loadMaps: true}))
            .pipe(uglify())
            .pipe(sourcemaps.write('./'))
            .pipe(dest(jsURL))
            .pipe(browserSync.stream());
    });
    done();
}
//   browserify
//   transform babelify [env]
//   bundle
//   source
//   rename .min
//   buffer
//   sourcemap
//   uglify
//   write sourcemap
//   dist
function watcher() {
    "use strict";
    browserSync.init({
        proxy: 'http://localhost/wordpress/wp-admin/index.php',
        open: false,
        injectChanges: true
    });
    watch('./**/*.php').on('change', browserSync.reload);
    watch('src/scss/**/*.scss', {usePolling: true}, series(style)).on('change', browserSync.reload);
    watch('src/js/**/*.js', {usePolling: true}, series(js)).on('change', browserSync.reload);
}
exports.default = series(
        parallel(style, js),
        watcher
        );
exports.watcher = watcher;
exports.js = js;
exports.style = style;
//exports.default = series([js, style], watch);
//exports.watch = watch;
