// var selectors = '#gif_form_hours, #gif_form_minutes, #gif_form_seconds';
var selectors = '#gif_form_checkboxes_hours, #gif_form_checkboxes_minutes, #gif_form_checkboxes_seconds';

// We use on to bind the event to the elements rendered by ajax responses.
$(document).on('change', selectors, function() {
  var $triggered = $(this);
  var $form = $triggered.closest('form');

  // var data = {};
  // $(selectors.split(',')).each(function(key, value) {
  //   $value = $($.trim(value));
  //   data[$value.attr('name')] = $value.is(':checked')
  // });

  // data[$('#gif_form_date').attr('name')] = '111111111';
  // data[$('#gif_form_timezone').attr('name')] = $('#gif_form_timezone').val();

  var $target_selectors = $triggered.nextAll('input');

  $.ajax({
    url : $form.attr('action'),
    type: $form.attr('method'),
    // data : data,
    data: $form.serialize(),

    success: function(html) {

      $target_selectors.each(function (key, value) {
        $value = $(value);
        $value.replaceWith(
          $(html).find('#' + $value.attr('id'))
        );
      });

      // console.log(html);
      $('#gif_form_date').replaceWith(
        $(html).find('#gif_form_date')
      );

    }

  });

});
