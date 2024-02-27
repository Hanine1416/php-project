/*var gulp=require('gulp');*/
let {src,dest,series, watch,task }= require('gulp');
let //sass = require('gulp-sass'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    notify = require('gulp-notify'),
    concatCss = require('gulp-concat-css'),
    minifyCSS = require('gulp-minify-css'),
    minify = require('gulp-minify'),
    rename = require('gulp-rename');
    const sass = require('gulp-sass')(require('sass'));

let mainChildSass = function() {
    return src(['../page/**/*.scss','!../page/app/**'])
        .pipe(sass())
        .pipe(minifyCSS())
        .on("error", sass.logError)
        .pipe(dest('../page/'));
}
let globalPagesSass = function() {
    return src(['../css/**/*.scss'])
        .pipe(sass())
        .pipe(minifyCSS())
        .on("error", sass.logError)
        .pipe(dest('../css/'));
}


function globalJs() {

    return src(['../page/**/*.js','!../page/app/**','!../page/**/*.min.js'])

        .pipe(rename({suffix: '.min'}))

        .pipe(uglify())

        .pipe(dest('../page/'));

}

let functions =[mainChildSass,globalPagesSass,globalJs]
/* */

/** Launch gulp sass watcher */
task('watch',function () {
    watch(['../page/**/*.scss','!../page/app/**']).on('change',function (path) {
        let date = new Date();
        console.log(`file ${path} changed at ${date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()}`);
        mainChildSass()
    });
    watch(['../css/**/*.scss']).on('change',function (path) {
        let date = new Date();
        console.log(`file ${path} changed at ${date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()}`);
        globalPagesSass()
    });
    watch(['../page/**/*.js','!../page/app/**','!../page/**/*.min.js']).on('change',function (path) {
        let date = new Date();
         console.log(`file ${path} changed at ${date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()}`);
         globalJs()
     });
 });
exports.watch = task('watch');
exports.default = series(functions);
