module.exports = function(grunt) {
    require('time-grunt')(grunt);

    /**
     * Rename filename from .ext to .min.ext
     */
    var minified_version_name = function(dest, src) {
        var ext = src.split('.').pop();

        return dest + '/' + src.replace('.' + ext, '.min.' + ext);
    };

    /**
     * Configuration
     */
    grunt.initConfig({
        /**
         * Directories
         */
        dirs: {
            css: 'assets/css',
            js: 'assets/js'
        },
        /**
         * Validate Javascript files
         */
        jshint: {
            options: {
                esversion: 6
            },
            all: [
                '<%= dirs.js %>/bot.js', // we have to solve all errors returned by jshint
                '<%= dirs.js %>/scripts.js',
                '<%= dirs.js %>/scripts-admin.js',
                '<%= dirs.js %>/scripts-admin-global.js'
            ]
        },
        /**
         * Minify and concatenate Javascript files
         */
        uglify: {
            dist: {
                files: [
                    {
                        expand: true,
                        src: [
                            '<%= dirs.js %>/*.js',
                            '!<%= dirs.js %>/*.min.js',
                            '!<%= dirs.js %>/bot*.js',
                        ],
                        dest: '.',
                        rename: minified_version_name
                    },
                    {
                        // bot.js + bot.async.js
                        src: [
                            '<%= dirs.js %>/bot*.js',
                            '!<%= dirs.js %>/*.min.js',
                        ],
                        dest: '<%= dirs.js %>/bot.min.js'
                    }
                ]
            }
        },
        /**
         * Apply post-processors to CSS files
         */
        postcss: {
            options: {
                map: false,
                processors: [
                    require('autoprefixer')({overrideBrowserslist: ['> 1%', 'last 2 versions']}),
                    require('cssnano')({calc: {precision: 2}})
                ]
            },
            dist: {
                files: [
                    {
                        expand: true,
                        src: [
                            '<%= dirs.css %>/*.css',
                            '!<%= dirs.css %>/*.min.css'
                        ],
                        dest: '.',
                        rename: minified_version_name
                    }
                ]
            }
        },
        /**
         * Watch file changes
         */
        watch: {
            css: {
                files: [
                    '<%= dirs.css %>/*.css',
                    '!<%= dirs.css %>/*.min.css'
                ],
                tasks: [
                    'postcss'
                ],
                options: {
                    debounceDelay: 150
                }
            },
            js: {
                files: [
                    '<%= dirs.js %>/*.js',
                    '!<%= dirs.js %>/*.min.js'
                ],
                tasks: [
                    'jshint',
                    'uglify'
                ],
                options: {
                    debounceDelay: 150
                }
            }
        },
        version: {
            project: {
                src: [
                    'package.json'
                ]
            },
            composer: {
                src: [
                    'composer.json'
                ]
            },
            mainClass: {
                options: {
                    prefix: '\\.*\\VERSION\.*\\s=\.*\\s\''
                },
                src: [
                    'includes/classes/wp-maintenance-mode.php'
                ]
            },
            readmetxt: {
                options: {
                    prefix: 'Stable tag:\\s*'
                },
                src: [
                    'readme.txt'
                ]
            },
            metatag: {
                options: {
                    prefix: 'Version:\\s*',
                    flags: ''
                },
                src: [ 'wp-maintenance-mode.php' ]
            },
        },
        wp_readme_to_markdown: {
            plugin: {
                files: {
                    'README.md': 'readme.txt'
                },
            },
        },
    });


    /**
     * Load NPM tasks
     */
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify-es');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-version');
    grunt.loadNpmTasks('grunt-wp-readme-to-markdown');

    /**
     * Register tasks
     */
    grunt.registerTask('default', [
        'uglify',
        'postcss',
    ]);
};
