//paths for source and bundled parts 
var basePaths = {
    src: 'src/',
    dest: 'assets/',
    npm: 'node_modules/',
};

//require plugins
var gulp = require('gulp');

var es          = require('event-stream'),
    path        = require('relative-path'),
    del         = require('del'),
    parseArgs   = require('minimist'),
    log         = require('fancy-log'),
    through     = require('through2');

//plugins - load gulp-* plugins without direct calls
var plugins = require("gulp-load-plugins")({
    pattern: ['gulp-*', 'gulp.*'],
    replaceString: /\bgulp[\-.]/ 
});

//call gulp -p to go into production mode
var sassStyle = 'expanded'; // SASS syntax
var sourceMap = true; //wheter to build source maps
var isProduction = false; //mode flag

var opt = parseArgs(process.argv);

if(opt.p && opt.p == true){
    log.info('In production mode');
    isProduction = true;
    sassStyle = 'compressed';
    sourceMap = false;
}

//sass
gulp.task('build-css', function(){

    var appFiles = gulp.src([basePaths.src+'sass/public.scss']) //our main file with @import-s
        .pipe( !isProduction ? plugins.sourcemaps.init() : through.obj() )  //process the original sources for sourcemap
        .pipe(plugins.sass({
            outputStyle: sassStyle, //SASS syntas
            //includePaths: paths //add bourbon
        })
        .on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
            overrideBrowserslist: ['last 4 versions'],
            cascade: false
        }))
        .pipe( !isProduction ? plugins.sourcemaps.write() : through.obj() ) //add the map to modified source
        .on('error', log.error); //log

    return es.concat(appFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('public.css')) //combine into file
        .pipe(isProduction ? plugins.csso() : through.obj()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', log.error); //log

});

gulp.task('build-js', function(){

    var appFiles  = gulp.src([basePaths.src+'js/public.js']);

    console.log(basePaths.dest+'js', basePaths.src+'js/public.js')

    return es.concat(appFiles) // Join all scripts
        .pipe(plugins.concat('public.js')) // Combine them into a "public.js" bundle
        .pipe(isProduction ? plugins.uglify() : through.obj()) // Minification
        .pipe(plugins.size()) // Print total size for log
        .pipe(gulp.dest(basePaths.dest+'js')) // Write results into file
        .on('error', log.error);

});

//builds
gulp.task('full-build', gulp.series('build-css', 'build-js',));

gulp.task('watch', function(){

    gulp.watch([basePaths.src+'sass/*.scss', basePaths.src+'sass/**/*.scss'], gulp.series('build-css',));
    gulp.watch(basePaths.src+'js/*.js', gulp.series('build-js',));

});

gulp.task('default', gulp.series('full-build', 'watch'));