//paths for source and bundled parts 
var basePaths = {
    src: 'src/',
    dest: 'assets/',
    npm: 'node_modules/',
    bower: 'bower_components/'
};

//require plugins
var gulp = require('gulp');

var es          = require('event-stream'),
    path        = require('relative-path'),
    runSequence = require('run-sequence'),
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


//js
gulp.task('build-js', function() {
    var vendorFiles  = gulp.src([basePaths.src+'js/jquery.cookie.js']),
        appFiles  = gulp.src([basePaths.src+'js/front.js']); //our own JS files*/
        
    return es.concat(vendorFiles, appFiles) //join them
        .pipe(plugins.concat('engb.js')) //combine them into bundle.js
        .pipe(isProduction ? plugins.uglify() : through.obj()) //minification
        .pipe(plugins.size()) //print size for log
        .pipe(gulp.dest(basePaths.dest+'js')) //write results into file
        .on('error', log.error); //log
});

gulp.task('build-admin-js', function() {
    var appFiles  = gulp.src([basePaths.src+'js/admin.js']); //our own JS files*/
        
    return es.concat(appFiles) //join them
        .pipe(plugins.concat('engb-admin.js')) //combine them into bundle.js
        .pipe(isProduction ? plugins.uglify() : through.obj()) //minification
        .pipe(plugins.size()) //print size for log
        .pipe(gulp.dest(basePaths.dest+'js')) //write results into file
        .on('error', log.error); //log
});


//sass
gulp.task('build-css', function() {

    //paths for bourbon
    //var paths = path('./bower_components/bootstrap/scss');
       //mdl = path('./node_modules/material-design-lite/src');
       //paths.push(slick);

    var appFiles = gulp.src([basePaths.src+'sass/front.scss']) //our main file with @import-s
        .pipe(!isProduction ? plugins.sourcemaps.init() : through.obj())  //process the original sources for sourcemap
        .pipe(plugins.sass({
            outputStyle: sassStyle, //SASS syntas
            //includePaths: paths //add bourbon
        })
        .on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                overrideBrowserslist: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : through.obj()) //add the map to modified source
        .on('error', log.error); //log

    return es.concat(appFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('engb.css')) //combine into file
        .pipe(isProduction ? plugins.csso() : through.obj()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', log.error); //log
});


gulp.task('build-admin-css', function() {

    //paths for bourbon
    //var paths = path('./bower_components/bootstrap/scss');
       //mdl = path('./node_modules/material-design-lite/src');
       //paths.push(slick);

    var appFiles = gulp.src([basePaths.src+'sass/admin.scss']) //our main file with @import-s
        .pipe(!isProduction ? plugins.sourcemaps.init() : through.obj())  //process the original sources for sourcemap
        .pipe(plugins.sass({
            outputStyle: sassStyle, //SASS syntas
            //includePaths: paths //add bourbon
        })
        .on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                overrideBrowserslist: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : through.obj()) //add the map to modified source
        .on('error', log.error); //log

    return es.concat(appFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('engb-admin.css')) //combine into file
        .pipe(isProduction ? plugins.csso() : through.obj()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', log.error); //log
});


//builds
gulp.task('full-build', gulp.series('build-css', 'build-admin-css', 'build-js', 'build-admin-js'));


//svg - combine and clear svg assets
gulp.task('svg-opt', function() {

    var icons = gulp.src([basePaths.src+'svg/icon-*.svg'])
        .pipe(plugins.svgmin({
            plugins: [{
                removeTitle: true,
                removeDesc: { removeAny: true },
                removeComments: true
            }]
        })) //minification
        .pipe(plugins.cheerio({
            run: function ($) { //remove fill from icons
                $('[fill]').removeAttr('fill');
                $('[fill-rule]').removeAttr('fill-rule');
            },
            parserOptions: { xmlMode: true }
        })),
        pics = gulp.src([basePaths.src+'svg/pic-*.svg'])
        .pipe(plugins.svgmin({
            plugins: [{
                removeTitle: true,
                removeDesc: { removeAny: true },
                removeEditorsNSData: true,
                removeComments: true
            }]
        })); //minification

    return es.concat(icons, pics)
        .pipe(plugins.svgstore({ inlineSvg: true })) //combine 
        .pipe(gulp.dest(basePaths.dest+'svg'));
});

//watchers
gulp.task('watch', function(){

    gulp.watch([basePaths.src+'sass/*.scss', basePaths.src+'sass/**/*.scss'], gulp.series('build-css', 'build-admin-css'));
    gulp.watch(basePaths.src+'js/*.js', gulp.series('build-js', 'build-admin-js'));

});



//default
gulp.task('default', gulp.series('full-build', 'watch'));