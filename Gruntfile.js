// Necessary wrapper
module.exports = function(grunt) {

    // Tasks
    grunt.initConfig({
        // Concatenate
        concat: {
            vendor_js: {
                src: [
                    'build/www/vendor/jquery/jquery.js',
                    'build/www/vendor/bootstrap/js/bootstrap-affix.js',
                    'build/www/vendor/bootstrap/js/bootstrap-dropdown.js',
                    'build/www/vendor/bootstrap/js/bootstrap-tab.js',
                    'build/www/vendor/bootstrap/js/bootstrap-tooltip.js',
                    'build/www/vendor/bootstrap/js/bootstrap-popover.js',
                    'build/www/vendor/bootstrap-switch/static/js/bootstrap-switch.js',
                    'build/www/vendor/blueimp-tmpl/js/tmpl.js',
                    'build/www/vendor/jquery-validation/jquery.validate.js',
                    'build/www/vendor/markitup/markitup/jquery.markitup.js'
                ],
                dest: 'build/www/vendor.concat.js'
            },
            custom_js: {
                src: [
                    'build/www/custom/js/jtracker.js',
                    'build/www/custom/js/jtracker-rules.js',
                    'build/www/custom/js/jtracker-tmpl.js',
                    'build/www/custom/js/markitup-set.js'
                ],
                dest: 'build/www/custom.concat.js'
            },
            custom_css: {
                src: [
                    'build/www/custom/css/template.css',
                    'build/www/custom/css/code.css'
                ],
                dest: 'build/www/custom.concat.css'
            }
        },
        // Compress
        uglify: {
            options: {
                mangle: false
            },
            vendor_js: {
                files: {
                    'build/www/vendor.concat.min.js': '<%= concat.vendor_js.dest %>'
                }
            },
            custom_js: {
                files: {
                    'build/www/custom.concat.min.js': '<%= concat.custom_js.dest %>'
                }
            }
        },
        // Compress
        cssmin: {
            custom_css: {
                files: {
                    'build/www/custom.concat.min.css': '<%= concat.custom_css.dest %>'
                }
            }
        },
        // Copy
        copy: {
            main: {
                files: [
                    {expand: true, cwd: 'build/www/', src: ['*.js'], dest: 'www/js'},
                    {expand: true, cwd: 'build/www/', src: ['*.css'], dest: 'www/css'}
                ]
            }
        }
    });

    // Load plugins
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Commands
    grunt.registerTask('default', ['concat', 'uglify', 'cssmin', 'copy']);
};
