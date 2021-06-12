/**
 * 2007-2021 Ingenico
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@ingenico.com we can send you a copy immediately.
 *
 *  @author    Ingenico <contact@ingenico.com>
 *  @copyright 2007-2021 Ingenico
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
'use strict';

let gulp = require('gulp'),
    rename = require('gulp-rename'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    cssmin = require('gulp-minify-css'),
    uglify = require('gulp-uglify-es').default;

gulp.task('css:build', function () {
    return gulp.src('./views/css/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./views/css'))
        .pipe(cssmin())
        .pipe(rename({
            suffix: '.min',
        }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./views/css'));
});

gulp.task('css:build:watch', function () {
    gulp.watch('./views/css/*.scss', gulp.parallel('css:build'));
});

gulp.task(
    'js:build',
    function () {
        return gulp.src( ['./views/js/*.js', '!./views/js/*.min.js'] )
            .pipe( sourcemaps.init() )
            .pipe( uglify() )
            .pipe(
                rename(
                    function (path) {
                        path.extname = '.min.js';
                    }
                )
            )
            .pipe( sourcemaps.write('.') )
            .pipe( gulp.dest( './views/js' ) );
    }
);

gulp.task('js:build:watch', function () {
    gulp.watch('./views/js/*.js', gulp.parallel('js:build'));
});
