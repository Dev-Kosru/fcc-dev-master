var gulp        = require('gulp');
var browserSync = require('browser-sync').create();
var sass        = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var babel = require('gulp-babel');

// Static Server + watching scss/html files
gulp.task('serve', ['sass', 'js'], function() {

    browserSync.init({
        proxy: "fcc.local",
        minify: false,
        logFileChanges: true
    });

    gulp.watch("scss/*.scss", ['sass']);
    gulp.watch("js/*.js", ['js', 'babel']);
});

// Compile sass into CSS & auto-inject into browsers
gulp.task('sass', function() {
    return gulp.src("scss/*.scss")
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest("./"))
        .pipe(browserSync.stream());
});
gulp.task('js', function() {
    return gulp.src("js/script.js")
        .pipe(browserSync.stream());
});
gulp.task('babel', function() {
    return gulp.src("js/backend.js")
        .pipe(babel({
			presets: ['babel-preset-react']
        }))
        .pipe(gulp.dest('js/dist'));
});

gulp.task('default', ['serve']);
