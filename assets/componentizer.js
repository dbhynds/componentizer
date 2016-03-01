(function($) {
  $(document).ready(function(){
    $('.order-components').sortable({
      containment: 'parent',
      handle: '.sortable',
      items: '> .component',
    });

    $('#migrate-to-group-names').click(function(){
      var data = {
        'action': 'update_to_group_name'
      };
      // console.log(data);
      $.post(ajaxurl, data, function(response) {
        console.log(response);
      });
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