/*=========================================================================================
    File Name: form-repeater.js
    Description: form repeater page specific js
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy HTML Admin Template
    Version: 1.0
    Author: PIXINVENT
    Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

$(function () {
  'use strict';

  // form repeater jquery
  $('.invoice-repeater, .repeater-default').repeater({
    show: function () {
      $(this).slideDown();
      // Feather Icons
      if (feather) {
        feather.replace({ width: 14, height: 14 });
      }

      $('.summernote').summernote({
        height: 150,
        minHeight: 100,
        maxHeight: 600,
      });
      $('.note-insert').remove();

      $('.clone_img').last().remove();
      $('.item_id').select2({
        placeholder: "-- Select --",
        allowClear: true
    });
    },
    
    hide: function (e) {      
      if(($('.remove-item').length) > 1){
        $(this).slideUp(e);  
      }else{
        // confirm("Are you sure you want to delete this element?");
        $(this).slideDown();
      }       
    }
  });
});
