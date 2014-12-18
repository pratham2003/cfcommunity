<header class="navbar navbar-default navbar-fixed-top navbar-inverse" role="banner">
  <div class="container">
    <div class="navbar-header">

      <button type="button" class="navbar-toggle navbar-toggle-left" data-toggle="offcanvas">
        <span class="sr-only">Toggle Sidebar</span>
            <?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
          <i class="fa fa-chevron-circle-right"></i>
      </button>

      <?php if ( wp_is_mobile() ): ?>
        <div class="mobile-notifications">
          <?php cf_notifications_buddybar_menu(); ?>
        </div>
      <?php endif; ?>

      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>

        <a class="navbar-brand" href="<?php echo home_url(); ?>/">
          <?php // bloginfo('name'); ?>
          <?php if ( is_user_logged_in() && wp_is_mobile() ) : ?>

<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFwAAABcCAMAAADUMSJqAAAAQlBMVEUAAACBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMqBqMozURfNAAAAFXRSTlMAv0Aw3w+A8KDPIbFhUHGQ+IkH5hngEyXPAAAEEElEQVRo3q2Z6XakIBCFZUdA1Fbe/1Vnkpl0gcVSJLk/Pfod+nKrWHohyW3aeiblJeXK/Bnu5ZfENTPpofVXyJs1qSb1Y/Ktr9QQ/yFa2CMlAly8ptHOAqkP9zLM4ZU+EhUuU1pnXOIyJSpcpA9Z6gy/9pTo8PjvgRQ0t9c0A/96+4iUZJtEEAwFnvmhNSGRVH1/dX22prEZBDF/fHXpZ6LJv78oTbzE9LixdB5E2thDomqDID4k70ZOElkOWQ6z+qrm25DZBlsOsrW6XBNZDCyvKGD4nujauwk40KTyNKGtqP2h7UqmND+fKtUVUfXQZVB2u8a4ozZtmouP4fEoG/VpExZODH5LRtUsXY2CiCTgW4HQzzihZj4IgW8PXKulB6ekVyDH2yvWWiuhXtnZelTW2oLCKpa71NFx/y/8q3AL2IK7GpyjIHayvtXH/RlApjC83hEbe1WL2fDUIzgbBxGm9JW9ZASwCxMYtlxQViuOjCqsYk+4gMIa+3JW9vTKPLoUwA3M8UB3+RKvlfteFpGvdEQpG435dcA71dXLqCLS8Wkci2Kpwk+YF+Q4mMW5LyyH2l/3TTWbjC9KwbWbJE659Zor2KKDciPOnitYdmIv9Vos/k40yUEhcud1kYUlDlavHcgF27TXcdkPIp5u+l4qZP6qbnlIhB0WU8zg/ddtC+463YUaFgwfBvcEOIMFk2SLClZAWupwg+GUhCtt3p/Ili10eGIcnRNc15e4yCEcJN9R13k8QzOKK4az8eGW5d/wZhF5mLF5uOn+VJE1rv45QHK+QuM0xTfNpSjgjhsah5ENBoCW7/pOW+DzgmjEnL/higJn+TK3w/1M/RSl36sz78OBt+IZ3evnN5nYDHwrSKLdi8TnVMzB72I1ts0whs+BAzz24bAJAtONghUAt5WQTYsmwM/HblXX1y793oTpCbh47lIE0N8fyA0AVDhsDV+meqoQmv2VDRAggO9jeMTJ8/2rmO054bx5OLqhIgdXbDuwavDuWdHnXgmEdiy14ArB8eyVwXPoyrgJx59je/1jpuMXRUTfaucQCd6/XxcHjhJjbG19I4vVK+CB069EMLxspHpwV6Sub8E1NhUeg7YpeL+G5KuzKaPDXS2JBw6zkvNwg7o/MgUSMwtn4OiogwQyXBVj1J0bFVCk3j679nxe9+T1Obaz6CyGeIFup+AMzacBdkU77RIX7XHxuOcv/1kBF49fK90yUDgIcAYtMdvgE/6CFb1qylt0KJv5Tvr7T/k23EFoZTFLZluICp0T93u4PHfF3wtZtx3Excot2zVe1GHDpqgmWakMo9UyK84Ifz67ZE71zX+hsfeyROmI0GSp4CH2la6HyLP87VwLuuHLr0rxuPtVfph0XJKRrfgDXCyEICuZ8CUAAAAASUVORK5CYII=' />

          <?php else: ?>

<img src='data:image/png;base64,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' />

          <?php endif;?>

        </a>
    </div>

    <?php
      get_template_part( 'templates/header-navigation' );
    ?>

  </div>
</header>

