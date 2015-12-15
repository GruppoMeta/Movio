$(function() {
    Movio.init();

    // SHOW MENU
    $('.btn-menu').on('click', function(e) {
      $('.js-header').toggleClass("show-menu");
      $('.item-box').toggleClass("ie-hide-iframe");
      e.preventDefault();
    });
});
