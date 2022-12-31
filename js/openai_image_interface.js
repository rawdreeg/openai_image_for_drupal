(function ($, Drupal) {
  $(document).ready(function() {
    imagePreview();

    // attach event listener to the button
    $(document).on("click", ".openai-image-download-button", function(event) {
      // prevent the default form submission
      event.preventDefault();

      let drupalSelector = jQuery('.openai-filefield-parent .form-managed-file__main').find('input').first().data("drupal-selector");
      let selector = drupalSelector.split('-upload')[0];

      // get the image url
      let url = $(this).parent().find("img").attr("src");

      if(selector && url) {
        $('[data-drupal-selector="' + selector + '-openai-image-paths"]').val(url);
        $('[data-drupal-selector="' + selector + '-upload-button"]').mousedown();
        $('[data-drupal-selector="' + selector + '-preview"]').attr('src', url);
      }

    });

  });

  function imagePreview() {
    $(".image-widget").each(function( index ) {
      var oai_url = $(this).find('.image-widget-data .file--image a').attr("href");
      if(oai_url){
        if(oai_url.indexOf('oai') > 0) {
          $(this).find('.image-preview img').attr('src', oai_url);
        }
      }
    });
  }

})(jQuery, Drupal);
