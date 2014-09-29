<footer class="content-info container" role="contentinfo">
  <div class="row">
    <div class="col-lg-12">
      <?php dynamic_sidebar('sidebar-footer'); ?>
      <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
    </div>
  </div>
</footer>

<?php if ( is_user_logged_in() && ! wp_is_mobile() ): ?>
<script type="text/javascript" src="/cometchat/cometchatjs.php" charset="utf-8"></script>
<?php endif; ?>

<?php wp_footer(); ?>
