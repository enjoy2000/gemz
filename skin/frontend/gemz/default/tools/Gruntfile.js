'use strict';
module.exports = function (grunt) {

    grunt.initConfig({
        jshint: {
            options: {
                jshintrc: '.jshintrc',
                expr: true
            },
            all: [
                'Gruntfile.js',
                '../js/gemz/**/*.js'
            ]
        },
        less: {
            dist: {
                files: {
                    '../css/styles.css': ['../css/styles-dev.css']
                },
                options: {
                    compress: true,
                    sourceMap: true,
                    sourceMapFileInline: true
                }
            }
        },
        cssmin: {
            compress: {
                options: {
                    compatibility: 'ie8',
                    keepSpecialComments: 0
                },
                files: {
                    '../css/styles.css': '../css/styles.css'
                }
            }
        },
        watch: {
            less: {
                files: [
                    '../css/*.less',
                    '../css/*.css',
                    '../css/plugins/*.css',
                    '../css/plugins/*.less',
                    '!../css/style.min.css',
                ],
                tasks: ['less']
            },
            js: {
                files: [
                    '<%= jshint.all %>'
                ],
                tasks: ['jshint']
            },
            livereload: {
                options: {
                    livereload: 802
                },
                files: [
                    '../css/style.min.css',
                    '../js/*.js',
                    '../js/helper/**/*.js',
                    '../js/pages/*.js',
                    '../js/plugins/**/*.js',
                    '../js/components/**/*.js',
                    '../include/*.php',
                    '../*.php'
                ]
            }
        },
        requirejs: {
            compile: {
                options: {
                    name: 'js/main',
                    baseUrl: '..',
                    out: '../js/script.min.js',
                    include:  ['vendor/requirejs/require.js'],
                    exclude: [
                    ],
                    mainConfigFile: "../js/main.js",
                    preserveLicenseComments: false,
                    generateSourceMaps: false,
                    optimize: "uglify2",
                    wrap: {
                        start: "",
                        end: ""
                    }
                }
            }
        },
        imagemin: {                       
            dist: {
                options: {
                },                   
                files: [{
                    expand: true,                  
                    cwd: '../',                   
                    src: ['images/*.{png,jpg,gif}', 'images/**/*.{png,jpg,gif}', 'media/*.{png,jpg,gif}', 'media/**/*.{png,jpg,gif}'],
                    dest: '..' 
                }]
            }
        }
    });

    // Load tasks
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // Register tasks
    grunt.registerTask('default', [
        'less',
        'cssmin',
        'imagemin'
    ]);
    grunt.registerTask('deploy', [
        'less',
        'cssmin',
        'jshint',
        'requirejs'
    ]);
    grunt.registerTask('dev', [
        'watch'
    ]);

};