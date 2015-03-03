<div class="wrap">

	<h2><?php _e('Cooked Import / Export', 'cooked'); ?></h2>
	<?php settings_errors(); ?>
	<div id="cooked-admin-panel-container">
		
		<div class="section-row">
			<div class="section-head">
				<?php $section_title = __('Import / Export', 'cooked'); ?>
				<h2><?php echo esc_attr($section_title); ?></h2>
			</div><!-- /.section-head -->

			<div class="section-body">
				<form method="post" enctype="multipart/form-data" class="import-form cp-import-form">
					<div class="import-holder">
						<a href="#" class="button button-primary button-large button-imex btn-import"> <?php _e('Import', 'cooked'); ?></a>
						<input type="file" name="import_file" class="hidden-upload" id="upload-field" />
						<input type="hidden" name="settings-import" value="yes">
					</div>
				</form>
				<a href="<?php echo add_query_arg(array('export-settings' => true), remove_query_arg('settings-updated')); ?>" class="button button-primary button-large button-imex"> <?php _e('Export', 'cooked'); ?></a>
			</div><!-- /.section-body -->
		</div><!-- /.section-row -->
		
	</div>
</div>