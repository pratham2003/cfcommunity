<html>
<body><?php

wp_head();
the_post();
global $post_id;
$post_id = get_the_ID();
load_template(CP_PLUGIN_SECTIONS_DIR . 'single-part.php');

?><script type="text/javascript">
window.print();
</script><?php

wp_footer();

?></body></html>