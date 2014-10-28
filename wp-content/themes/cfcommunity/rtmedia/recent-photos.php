<?php if ( have_rtmedia () ) { ?>
<div class="widget bp-media-recent-photos">
    <h4>Recent Photos</h4>
        <ul>

            <?php while ( have_rtmedia () ) : rtmedia (); ?>
                <li class="rtmedia-list-item" id="<?php echo rtmedia_id(); ?>">
                    <a href ="<?php rtmedia_permalink(); ?>" title="<?php echo rtmedia_title(); ?>">
                        <div>
                            <img src="<?php rtmedia_image("rt_media_thumbnail"); ?>" alt="<?php rtmedia_image_alt(); ?>" >
                        </div>
                    </a>
                </li>
            <?php endwhile; ?>

        </ul>
</div>
<?php } ?>
