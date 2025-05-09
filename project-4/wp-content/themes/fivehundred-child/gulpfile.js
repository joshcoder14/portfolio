const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const autoprefixer = require("gulp-autoprefixer");
const paths = {
  styles: {
    src: "./src/styles/**/*.scss",
    dest: "./assets/css",
  },
};

gulp.task("css", function () {
  return gulp
    .src(paths.styles.src, { sourcemaps: true })
    .pipe(
      sass({
        errLogToConsole: true,
        outputStyle: "compressed",
      })
    )
    .on("error", sass.logError)
    .pipe(autoprefixer())
    .pipe(gulp.dest(paths.styles.dest, { sourcemaps: "." }));
});

gulp.task("watch", function () {
  gulp.watch(paths.styles.src, gulp.series("css"));
});

gulp.task("default", gulp.series("css"));
gulp.task("build", gulp.series("css"));