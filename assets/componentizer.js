(function($) {
  $(document).ready(function(){
    $('.order-components').sortable({
      containment: 'parent',
      handle: '.sortable',
      items: '> .component',
    });
    $('.button-sync-componentizer').click(function(){
      var data = {
        'action': 'sync',
        'json_file': $(this).val()
      };
      // console.log(data);
      $.post(ajaxurl, data, function(response) {
        console.log(response);
        if (response) {
          $(response).remove();
        }
      });
    });
  });
})(jQuery);