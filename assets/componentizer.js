(function($) {
  $(document).ready(function(){
    $('.order-components').sortable({
      containment: 'parent',
      handle: '.sortable',
      items: '> .component',
    });
  });
})(jQuery);