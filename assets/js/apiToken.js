import $ from 'jquery';
$(function () {

  $('[data-id=apiTokenBlock]').each(function () {
    const $container = $(this);

    $container.on('click', '[data-id=apiTokenButton]', function (e) {
      e.preventDefault();
      
      const href = $(this).data('href');
      $.ajax({
        url: href,
        method: 'POST'
      }).then(function (data) {
        
        const $apiToken = $container.find('[data-id=apiToken]');
        $apiToken.html("<p>Ваш API токен: " + data.apiToken + "</p>");
      });
    });
  });

});
