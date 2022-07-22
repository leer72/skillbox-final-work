import $ from 'jquery';
$(function () {
  $('.custom-file').each(function () {
    const $container = $(this);

    $container.on('change', '.custom-file-input', function (event) {
      $container.find('.custom-file-label').html('');
      for(let index = 0; index < event.currentTarget.files.length; index++) {
        $container.find('.custom-file-label').append(event.currentTarget.files[index].name);
        if(event.currentTarget.files.length - index > 1) {
          $container.find('.custom-file-label').append('; ');
        }
      }
    });
  });
});
