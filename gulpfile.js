//paths for source and bundled parts of app
var basePaths = {
    src: 'src/',
    dest: 'assets/',
    npm: 'node_modules/',
    bower: 'bower_components/',
    root: ''
};

//require plugins
var gulp = require('gulp');

var es          = require('event-stream'),
    gutil       = require('gulp-util'),
    bourbon     = require('node-bourbon'),
    path        = require('relative-path'),
    runSequence = require('run-sequence'),
    del         = require('del');

//plugins - load gulp-* plugins without direct calls
var plugins = require("gulp-load-plugins")({
    pattern: ['gulp-*', 'gulp.*'],
    replaceString: /\bgulp[\-.]/
});

//env - call gulp --prod to go into production mode
var sassStyle = 'expanded'; // SASS syntax
var sourceMap = true; //wheter to build source maps
var isProduction = false; //mode flag

if(gutil.env.prod === true) {
    isProduction = true;
    sassStyle = 'compressed';
    sourceMap = false;
}

//log
var changeEvent = function(evt) {
    gutil.log('File', gutil.colors.cyan(evt.path.replace(new RegExp('/.*(?=/' + basePaths.src + ')/'), '')), 'was', gutil.colors.magenta(evt.type));
};

//js
gulp.task('build-js', function() {
    var vendorFiles = [basePaths.npm+'jquery.cookie/jquery.cookie.js'],
        appFiles = [basePaths.src+'js/*', basePaths.src+'js/front/*']; //our own JS files

    return gulp.src(vendorFiles.concat(appFiles)) //join them
        .pipe(plugins.filter('*.js'))//select only .js ones
        .pipe(plugins.concat('public.js'))//combine them into bundle.js
        .pipe(isProduction ? plugins.uglify() : gutil.noop()) //minification
        .pipe(plugins.size()) //print size for log
        .on('error', console.log) //log
        .pipe(gulp.dest(basePaths.dest+'js')) //write results into file
});

gulp.task('build-admin-js', function() {
    var vendorFiles = [/*basePaths.npm+'jquery.cookie/jquery.cookie.js'*/],
        appFiles = [basePaths.src+'js/admin/*']; //our own JS files

    return gulp.src(vendorFiles.concat(appFiles)) //join them
        .pipe(plugins.filter('*.js'))//select only .js ones
        .pipe(plugins.concat('admin.js'))//combine them into bundle.js
        .pipe(isProduction ? plugins.uglify() : gutil.noop()) //minification
        .pipe(plugins.size()) //print size for log
        .on('error', console.log) //log
        .pipe(gulp.dest(basePaths.dest+'js')) //write results into file
});


//sass
gulp.task('build-css', function() {

    //paths for bourbon
    var paths = require('node-bourbon').includePaths;
    var vendorFiles = gulp.src([]), //components
        appFiles = gulp.src(basePaths.src+'sass/front-main.scss') //our main file with @import-s
        .pipe(!isProduction ? plugins.sourcemaps.init() : gutil.noop())  //process the original sources for sourcemap
        .pipe(plugins.sass({
                outputStyle: sassStyle, //SASS syntas
                includePaths: paths //add bourbon
            }).on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                browsers: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : gutil.noop()) //add the map to modified source
        .on('error', console.log); //log

    return es.concat(appFiles, vendorFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('public.css')) //combine into file
        .pipe(isProduction ? plugins.cssmin() : gutil.noop()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', console.log); //log
});

gulp.task('build-admin-css', function() {

    var paths = require('node-bourbon').includePaths,
		vendorFiles = gulp.src([]),
        appFiles = gulp.src(basePaths.src+'sass/admin/admin.scss')
        .pipe(!isProduction ? plugins.sourcemaps.init() : gutil.noop())  //process the original sources for sourcemap
        .pipe(plugins.sass({
                outputStyle: sassStyle, //SASS syntas
                includePaths: paths //add bourbon + mdl
            }).on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                browsers: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : gutil.noop()) //add the map to modified source
        .on('error', console.log); //log

	return es.concat(appFiles, vendorFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('admin.css')) //combine into file
        .pipe(isProduction ? plugins.cssmin() : gutil.noop()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', console.log); //log

});


//revision
gulp.task('revision-clean', function(){
    // clean folder https://github.com/gulpjs/gulp/blob/master/docs/recipes/delete-files-folder.md
    return del([basePaths.dest+'rev/**/*']);
});

gulp.task('revision', function(){

    return gulp.src([basePaths.dest+'css/*.css', basePaths.dest+'js/*.js'])
        .pipe(plugins.rev())
        .pipe(gulp.dest( basePaths.dest+'rev' ))
        .pipe(plugins.rev.manifest())
        .pipe(gulp.dest(basePaths.dest+'rev')) // write manifest to build dir
        .on('error', console.log); //log
});


//builds
gulp.task('full-build', function(callback) {
    runSequence('build-css',
        'build-js',
		'svg-opt',
        callback);
});

gulp.task('full-build-css', function(callback) {
    runSequence('build-css', callback);
});

gulp.task('full-build-js', function(callback) {
    runSequence('build-js', callback);
});


//svg - combine and clear svg assets
gulp.task('svg-opt', function() {

    var icons = gulp.src([basePaths.src+'svg/icon-*.svg'])
        .pipe(plugins.svgmin({
            plugins: [{
                removeTitle: true,
                removeDesc: { removeAny: true },
                removeEditorsNSData: true,
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
        pics = gulp.src([basePaths.root+'gateways/**/pic-main-*.svg', basePaths.src+'svg/pic-*.svg'])
        .pipe(plugins.svgmin({
            plugins: [{
                removeTitle: true,
                removeDesc: { removeAny: true },
                removeEditorsNSData: true,
                removeComments: true
            }]
        })); //minification

    return es.concat(icons, pics)
        .pipe(plugins.svgstore({ inlineSvg: true })) //combine for inline usage
        .pipe(gulp.dest(basePaths.dest+'svg'));
});

//watchers
gulp.task('watch', function(){
    gulp.watch([basePaths.src+'sass/*.scss'], ['full-build-css']).on('change', function(evt) {
        changeEvent(evt);
    });
    gulp.watch([basePaths.src+'js/*.js', basePaths.src+'js/front/*.js'], ['full-build-js']).on('change', function(evt) {
        changeEvent(evt);
    });
});

gulp.task('watch-admin', function(){
    gulp.watch([basePaths.src+'sass/admin/*.scss', basePaths.src+'sass/admin/**/*.scss'], ['build-admin-css']).on('change', function(evt) {
        changeEvent(evt);
    });
    // gulp.watch([basePaths.src+'js/*.js', basePaths.src+'js/front/*.js'], ['full-build-js']).on('change', function(evt) {
    //     changeEvent(evt);
    // });
});


//default
gulp.task('default', ['full-build', 'watch']);