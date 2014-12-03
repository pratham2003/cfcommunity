<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>" /> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>              
    <link rel="profile" href="http://gmpg.org/xfn/11" />        
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" /> 
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header role="banner">
	<div class="container">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<div class="row">			
			<div id="logo-tagline" class="col-md-12">				
				<?php if (get_theme_mod('crawford_logo_setting')): ?>
			        <a id="logo" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(get_theme_mod('crawford_logo_setting')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></a>
			    <?php else: ?>
			        <a id="site-name" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
			    <?php endif; ?>			   
			    <?php if ('yes' === get_theme_mod('crawford_tagline_setting')): ?>
					<p id="tagline"><?php bloginfo('description'); ?></p>
				<?php endif; ?>	
			</div>		
			<nav class="col-md-12" role="navigation">				
				<div class="collapse navbar-collapse"><?php wp_nav_menu(array('theme_location' => 'primary','depth' => 2,'container' => false,'fallback_cb' => false)); ?></div>
			</nav>
		</div>
	</div>
</header>