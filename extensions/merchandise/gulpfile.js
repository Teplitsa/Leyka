let basePaths = {
    src: 'src/',
    dest: 'assets/',
    npm: 'node_modules/',
};

// Require plugins
let gulp = require('gulp');

let es          = require('event-stream'),
    path        = require('relative-path'),
    del         = require('del'),
    parseArgs   = require('minimist'),
    log         = require('fancy-log'),
    through     = require('through2');
const gutil = require("gulp-util");

// Plugins - load gulp-* plugins without direct calls
let plugins = require("gulp-load-plugins")({
    pattern: ['gulp-*', 'gulp.*'],
    replaceString: /\bgulp[\-.]/ 
});

// Call "gulp -p" to go into production mode
let sassStyle = 'expanded'; // SASS syntax
let sourceMap = true; // Wheter to build source maps
let isProduction = false; // Mode flag

let opt = parseArgs(process.argv);

if(opt.p && opt.p == true){
    log.info('In production mode');
    isProduction = true;
    sassStyle = 'compressed';
    sourceMap = false;
}

//sass
gulp.task('build-css', function(){

    let appFiles = gulp.src([basePaths.src+'sass/public.scss']) // Our main file with @import-s
        .pipe( !isProduction ? plugins.sourcemaps.init() : through.obj() )  // Process the original sources for sourcemap
        .pipe(plugins.sass({
            outputStyle: sassStyle, // SASS syntax
            //includePaths: paths // Add Bourbon
        })
        .on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({
            overrideBrowserslist: ['last 4 versions'],
            cascade: false
        }))
        .pipe( !isProduction ? plugins.sourcemaps.write() : through.obj() ) //add the map to modified source
        .on('error', log.error);

    return es.concat(appFiles) // Combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('public.css')) // Combine into a file
        .pipe(isProduction ? plugins.csso() : through.obj()) // Minification on production
        .pipe(plugins.size())
        .pipe(gulp.dest(basePaths.dest+'css')) // Write into a file
        .on('error', log.error);

});

gulp.task('build-front-js', function(){

    let appFiles = gulp.src([basePaths.src+'js/public.js']);

    // console.log(basePaths.dest+'js', basePaths.src+'js/public.js')

    return es.concat(appFiles) // Join all scripts
        .pipe(plugins.concat('public.js')) // Combine them into a "public.js" bundle
        .pipe(isProduction ? plugins.uglify() : through.obj()) // Minification
        .pipe(plugins.size()) // Print total size for log
        .pipe(gulp.dest(basePaths.dest+'js')) // Write results into file
        .on('error', log.error);

});

gulp.task('build-admin-js', function(){

    let // vendorFiles = [basePaths.npm+'jquery.cookie/jquery.cookie.js'],
        appFiles = gulp.src([basePaths.src+'js/admin.js']);

    return es.concat(appFiles) // gulp.src(vendorFiles.concat(appFiles))
        .pipe(plugins.concat('admin.js'))
        .pipe(isProduction ? plugins.uglify() : gutil.noop()) // Minification
        .pipe(plugins.size())
        .on('error', console.log)
        .pipe(gulp.dest(basePaths.dest + 'js'));

});

gulp.task('full-build', gulp.series('build-css', 'build-front-js', 'build-admin-js'));

gulp.task('watch', function(){

    gulp.watch([basePaths.src+'sass/*.scss'], gulp.series('build-css',));
    gulp.watch(basePaths.src+'js/*.js', gulp.series('build-front-js', 'build-admin-js'));

});

gulp.task('default', gulp.series('full-build', 'watch'));