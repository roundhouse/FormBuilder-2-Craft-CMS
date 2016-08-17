module.exports = (grunt) ->
  grunt.initConfig

    # =============================================
    # VARIABLES
    # =============================================
    ScssDirectory: 'resources/scss'
    CoffeeDirectory: 'resources/coffee'
    ResourcesDirectory: 'resources'

    # =============================================
    # WATCH FOR CHANGE
    # =============================================
    watch:
      css:
        files: ['<%= ScssDirectory %>/**/*']
        tasks: ['sass']
      scripts:
        files: ['<%= CoffeeDirectory %>/*']
        tasks: ['coffee']
      options:
        livereload: false

    # =============================================
    # SASS COMPILE
    # =============================================
    # https://github.com/gruntjs/grunt-contrib-sass
    # =============================================
    sass:
      compile:
        options:
          compress: false
          sourcemap: 'none' # none, file, inline, none
          style: 'nested' # nested, compact, compressed, expanded
        files: 
          '<%= ResourcesDirectory %>/css/formbuilder2.css': '<%= ScssDirectory %>/formbuilder2.scss',

    # =============================================
    # COFFEE COMPILING
    # =============================================
    # https://github.com/gruntjs/grunt-contrib-coffee
    # =============================================
    coffee:
      options:
        join: true
        bare: true
      compile:
        files:
          '<%= ResourcesDirectory %>/js/formbuilder2.js': ['<%= CoffeeDirectory %>/formbuilder2.coffee']
          '<%= ResourcesDirectory %>/js/entrydetails.js': ['<%= CoffeeDirectory %>/entrydetails.coffee']
          '<%= ResourcesDirectory %>/js/ajaxsubmit.js': ['<%= CoffeeDirectory %>/ajaxsubmit.coffee']
          '<%= ResourcesDirectory %>/js/layouts.js': ['<%= CoffeeDirectory %>/layouts.coffee']
          '<%= ResourcesDirectory %>/js/templates.js': ['<%= CoffeeDirectory %>/templates.coffee']
          '<%= ResourcesDirectory %>/js/emaillogoasset.js': ['<%= CoffeeDirectory %>/emaillogoasset.coffee']

    # =============================================
    # UGLIFY JAVASCRIPT
    # =============================================
    # https://github.com/gruntjs/grunt-contrib-uglify
    # =============================================
    uglify:
      options:
        sourceMap: true
        mangle: false
        beautify: false
        compress: true
      dist:
        files:
          '<%= ResourcesDirectory %>/js/formbuilder2.min.js': ['<%= ResourcesDirectory %>/js/formbuilder2.js']

    # =============================================
    # LOAD PLUGINS
    # =============================================
    grunt.loadNpmTasks 'grunt-contrib-sass'
    grunt.loadNpmTasks 'grunt-contrib-coffee'
    grunt.loadNpmTasks 'grunt-contrib-watch'
    grunt.loadNpmTasks 'grunt-contrib-uglify'

    # =============================================
    # TASKS
    # =============================================
    grunt.registerTask 'default', ['watch']
    grunt.registerTask 'minify', ['uglify']