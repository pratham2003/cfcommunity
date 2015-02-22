<div class="wrap">

<?php
foreach ($data['messages'] as $message) {
    if (is_wp_error($message)) { ?>

        <div class="error">
            <p>
                An error occured: <?php echo $message->get_error_message(); ?>
            </p>
        </div>
    <?php } else { ?>
        <div class="updated"><p><?php echo $message; ?></p></div>
    <?php }
}
?>

    <?php include __DIR__ . '/' . $view . '.php'; ?>

    <hr>

    <div style="text-align: center; margin: 20px 0 20px 0;">
        <p>
            Copyright &copy; <?php echo date('Y'); ?>
            <a href="http://wppusher.com" target="_blank">WP Pusher</a>
            | <a href="http://wppusher.com/tos" target="_blank">Terms</a>
        </p>
        <a href="http://wppusher.com" target="_blank">
            <img src="http://wppusher.com/png_400px.png" width="50">
        </a>
    </div>
</div>
