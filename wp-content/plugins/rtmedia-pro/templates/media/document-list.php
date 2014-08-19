<div class="rtmedia-container">
	<?php do_action( 'rtmedia_before_media_gallery' ); ?>
	<?php
	$title = get_rtmedia_gallery_title();
	global $rtmedia_query;
	if ( isset( $rtmedia_query->is_gallery_shortcode ) && $rtmedia_query->is_gallery_shortcode == true ){ // if gallery is displayed using gallery shortcode
		?>
		<h2><?php _e( 'Media Gallery', 'rtmedia' ); ?></h2>
	<?php
	} else {
		?>
		<div id="rtm-gallery-title-container" class="row">
			<h2 class="rtm-gallery-title columns large-7 small-12 medium-7">
				<?php if ( $title ){
					echo $title;
				} else {
					_e( 'Media Gallery', 'rtmedia' );
				} ?>
			</h2>

			<div id="rtm-media-options" class="columns large-5 small-12 medium-5"><?php do_action( 'rtmedia_media_gallery_actions' ); ?></div>
		</div>
		<div class="clear"></div>
		<div id="rtm-media-gallery-uploader">
			<?php rtmedia_uploader( array( 'is_up_shortcode' => false ) ); ?>
		</div>
	<?php
	}
	?>
	<?php do_action( 'rtmedia_after_media_gallery_title' ); ?>
	<?php if ( have_rtmedia() ){ ?>
		<table
			class="rtmedia-list rtmedia-list-media rtmedia-list-document <?php echo rtmedia_media_gallery_class(); ?>">
			<thead>
			<tr class="rtmedia-list-document-row">
				<th width="50%"><?php _e( 'Title', 'rtmedia' ); ?></th>
				<th width="17%"><?php _e( 'Uploaded', 'rtmedia' ); ?></th>
				<th widht="10%"><?php _e( 'Size', 'rtmedia' ); ?></th>
				<th width="7.5%"><?php _e( 'Edit', 'rtmedia' ); ?></th>
				<th width="10.5%"><?php _e( 'Delete', 'rtmedia' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php while ( have_rtmedia() ) : rtmedia(); ?>
                            <?php 
                            $rtmedia_title = rtmedia_title();
                            $rtmedia_upload_date = rtmedia_pro_document_other_files_list_date();
                            $rtmedia_file_size = rtmedia_file_size();
                            ?>
				<tr class="rtmedia-list-document-row" id="<?php echo rtmedia_id(); ?>">
					<td data-value="<?php echo str_replace(" ", "-", strtolower($rtmedia_title)); ?>">
						<a href="<?php rtmedia_permalink(); ?>">
							<?php echo $rtmedia_title; ?>
						</a>
					</td>
                                        <td data-value="<?php echo $rtmedia_upload_date; ?>">
						<?php
						echo $rtmedia_upload_date;
						?>
					</td>
					<td data-value="<?php echo $rtmedia_file_size; ?>">
						<?php
						if ( function_exists( 'rtmedia_file_size' ) ){
							echo round( $rtmedia_file_size / ( 1024 * 1024 ), 2 ) . ' MB';
						} else {
							echo '--';
						}
						?>
					</td>
					<td>
						<?php
						if ( is_user_logged_in() && rtmedia_edit_allowed() ){
							?>
							<a href="<?php rtmedia_permalink(); ?>edit" class='no-popup' target='_blank'
							   title='<?php _e( 'Edit this media', 'rtmedia' ); ?>'><i class='rtmicon-edit'></i></a>
						<?php
						}
						?>
					</td>
					<td>
						<?php
						if ( is_user_logged_in() && rtmedia_delete_allowed() ){
							?>
							<a href="#" class="no-popup rtmp-delete-media-document"
							   title='<?php _e( 'Delete this media', 'rtmedia' ); ?>'><i
									class='rtmicon-trash-o'></i></a>
						<?php
						}
						?>
					</td>
				</tr>

			<?php endwhile; ?>
			</tbody>
		</table>
	<?php } else { ?>
		<p>
			<?php
			$message = __( "Oops !! There's no media found for the request !!", "rtmedia" );
			echo apply_filters( 'rtmedia_no_media_found_message_filter', $message );
			?>
		</p>
	<?php } ?>

	<?php do_action( 'rtmedia_after_media_gallery' ); ?>

</div>
