'use strict';
module.exports = function (grunt) {
    grunt.initConfig({
        less: {
            production: {
                options: {
                    compress: true
                },
                files: {
                    "css/main.css": "css/main.less"
                }
            }
        },
        cssmin : {
            combine : {
                options: {
                    keepSpecialComments: 0
                },
                files : {
                    'css/reports2cloud.min.css' : [
                        'css/pure.css',
                        'css/font/font.css',
                        'css/font-awesome.css',
                        'scripts/lib/avgrund/avgrund.css',
                        'scripts/lib/reveal/reveal.css',
                        'scripts/lib/reveal/theme/default.css',
                        'scripts/lib/ladda/ladda-themeless.css',
                        'scripts/lib/scrollbar/scrollbar.css',
                        'css/main.css'
                    ]
                }
            }
        },
        uglify: {
            options: {
                compress: {
                    drop_console: true
                }
            },
            all: {
                files : {
                    'scripts/reports2cloud.min.js' : [
                        'scripts/lib/enhance.js',
                        'scripts/lib/jstorage.js',
                        'scripts/lib/tools.js',
                        'scripts/lib/avgrund/avgrund.js',
                        'scripts/lib/scrollbar/scrollbar.js',
                        'scripts/lib/classList.js',
                        'scripts/lib/html5shiv.js',
                        'scripts/lib/reveal/reveal.js',
                        'scripts/lib/ladda/jquery.ladda.js',
                        'scripts/lib/meny/meny.js',
                        'scripts/lib/xds-server/xds.js',
                        'scripts/lib/popup.js',

                        'scripts/models/account-model.js',
                        'scripts/views/modal-view.js',
                        'scripts/models/picker-model.js',
                        'scripts/views/account-view.js',
                        'scripts/views/picker-view.js',
                        'scripts/views/form-view.js',
                        'scripts/views/report-view.js',
                        'scripts/views/dropboxfolders-view.js',
                        'scripts/views/file-view.js',
                        'scripts/views/sidebar-view.js',
                        'scripts/views/mainpage-view.js',
                        'scripts/maincore.js'
                    ]
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.registerTask('default', ['less', 'cssmin', 'uglify:all']);

};