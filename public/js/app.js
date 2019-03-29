var selectors = '#gif_form_checkboxes_hours, #gif_form_checkboxes_minutes, #gif_form_checkboxes_seconds';

// We use on to bind the event to the elements rendered by ajax responses.
$(document).on('change', selectors, function() {
  var $triggered = $(this);
  var $form = $triggered.closest('form');

  var $target_selectors = $triggered.nextAll('input');

  $.ajax({
    url : "/_form-date-widget-ajax-callback",
    type: $form.attr('method'),
    data: $form.serialize(),

    success: function(html) {

      // Update the checkboxes.
      $target_selectors.each(function (key, value) {
        $value = $(value);
        $value.replaceWith(
          $(html).find('#' + $value.attr('id'))
        );
      });

      // Update the date widget.
      $('#gif_form_date').replaceWith(
        $(html).find('#gif_form_date')
      );

    }

  });

});
