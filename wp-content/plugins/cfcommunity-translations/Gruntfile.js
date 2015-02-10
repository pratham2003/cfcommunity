'use strict';
module.exports = function(grunt) {

  grunt.initConfig({
  
    po2mo: {
        files: {
            src: 'languages/*.po',
            expand: true,
        },
    },

    pot: {
          options:{
              text_domain: 'cfctranslation', //Your text domain. Produces my-text-domain.pot
              dest: 'languages/', //directory to place the pot file
              keywords: [ //WordPress localisation functions
                '__:1',
                '_e:1',
                '_x:1,2c',
                'esc_html__:1',
                'esc_html_e:1',
                'esc_html_x:1,2c',
                'esc_attr__:1', 
                'esc_attr_e:1', 
                'esc_attr_x:1,2c', 
                '_ex:1,2c',
                '_n:1,2', 
                '_nx:1,2,4c',
                '_n_noop:1,2',
                '_nx_noop:1,2,3c'
               ],
          },
          files:{
              src:  [ '**/*.php' ], //Parse all php files
              expand: true,
          }
    },

  });


  // Load tasks
  grunt.loadNpmTasks('grunt-po2mo');
  grunt.loadNpmTasks('grunt-pot');

};
