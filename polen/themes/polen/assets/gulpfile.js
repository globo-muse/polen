"use strict";

const gulp = require("gulp");
const { dest } = require("gulp");
const uglify = require("gulp-uglify-es").default;
const log = require("fancy-log");
const iconfont = require("gulp-iconfont");
const iconfontCss = require("gulp-iconfont-css");
const sass = require("gulp-sass");
sass.compiler = require("node-sass");
const sourcemaps = require("gulp-sourcemaps");
const svgSprite = require("gulp-svg-sprite");

const fontName = "PolenIcons";
const sass_dir = "./scss/**/*.scss";

gulp.task("iconfont", function () {
	return gulp
		.src(["icons/*.svg"])
		.pipe(
			iconfontCss({
				fontName: fontName,
				path: "templates/_icons.scss",
				targetPath: "../scss/_icons.scss",
				fontPath: "fonts/",
			})
		)
		.pipe(
			iconfont({
				fontName: fontName,
				normalize: true,
				fontHeight: 1000,
			})
		)
		.pipe(gulp.dest("fonts/"));
});

function themeJsUglify() {
	log("Uglify nos arquivos do tema");
	return gulp.src("js/*.js").pipe(uglify()).pipe(dest("js/min"));
}

gulp.task("sass", function () {
	return gulp
		.src(sass_dir)
		.pipe(sass({ outputStyle: "compressed" }).on("error", sass.logError))
		.pipe(gulp.dest("./css"));
});

gulp.task("sass_map", function () {
	return gulp
		.src(sass_dir)
		.pipe(sourcemaps.init())
		.pipe(sass().on("error", sass.logError))
		.pipe(sourcemaps.write("./maps"))
		.pipe(gulp.dest("./css"));
});

gulp.task("sass:watch", function () {
	gulp.watch(sass_dir, { ignoreInitial: false }, gulp.series("sass_map"));
});

gulp.task("sprite", function () {
	const svgSpriteConfig = {
		mode: {
			css: {
				render: {
					css: true,
				},
			},
		},
	};
	return gulp
		.src("**/*.svg", { cwd: "img/cards" })
		.pipe(svgSprite(svgSpriteConfig))
		.pipe(gulp.dest("img/sprite"));
});

exports.compressjs = themeJsUglify;
