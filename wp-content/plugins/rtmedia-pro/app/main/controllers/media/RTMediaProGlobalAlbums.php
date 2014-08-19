<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProGlobalAlbums
 *
 * @author ritz
 */
class RTMediaProGlobalAlbums {

    function __construct() {
        add_filter("rtmedia_add_settings_sub_tabs", array($this,"rtmedia_pro_add_settings_tab"), 1);
        add_filter("rtmedia_pro_options_save_settings", array($this,"rtmedia_save_options_settings"), 1);
        add_action("wp_ajax_delete_global_album",array($this,"delete_global_album"));
        add_action("wp_ajax_rename_global_album",array($this,"rename_global_album"));
        add_action("wp_ajax_change_default_global_album",array($this,"change_default_global_album"));
    }

    function change_default_global_album() {
        if(isset($_POST['album_id']) && $_POST['album_id'] != "" ) {
            $default_global_album  = $_POST['album_id'];
            $global_albums = get_site_option ( 'rtmedia-global-albums' );
            $new_default_global_album_index = array_search($default_global_album, $global_albums);
            $global_albums[$new_default_global_album_index] = $global_albums['0'];
            $global_albums['0'] = $default_global_album;
            rtmedia_update_site_option('rtmedia-global-albums', $global_albums);
            echo "1";
            die();
        }
    }

    function rename_global_album() {
        $rtmedia_album = new RTMediaAlbum();
        $album_id = $_POST['album_id'];
        $album_name = $_POST['album_name'];
        $rename_album_media_array = $rtmedia_album->media->model->get_media (array("id"=>$album_id));
        $rtmediamodel = new RTMediaModel();
        $rtmediamodel->update(array("media_title" => $album_name), array("id" => $album_id));
        global $wpdb;
        $wpdb->update ( $wpdb->posts, array( 'post_title' => $album_name), array( 'id' => $rename_album_media_array['0']->media_id ) );
        echo "1";
        die();
    }

    function delete_global_album () {
        $rtmedia_album = new RTMediaAlbum();
        $default_album_id = $rtmedia_album->get_default();
        $delete_album_id = $_POST['album_id'];
        if($default_album_id == $delete_album_id){
            echo "0";
            die();
        }
        $rtmediamodel = new RTMediaModel();
        $default_album_media_array = $rtmedia_album->media->model->get_media (array("id"=>$default_album_id));
        $delete_album_media_array = $rtmedia_album->media->model->get_media (array("id"=>$delete_album_id));
        $rtmediamodel->update(array("album_id" => $default_album_id), array("album_id" => $delete_album_id));
        global $wpdb;
        $wpdb->update ( $wpdb->posts, array( 'post_parent' => $default_album_media_array['0']->media_id ), array( 'post_parent' => $delete_album_media_array['0']->media_id ) );
        $rtmediamodel->delete(array("id" => $delete_album_id));
        $wpdb->delete($wpdb->posts,array("id" => $delete_album_media_array['0']->media_id ));
        echo "1";
        die();
    }


    function rtmedia_save_options_settings($options) {
        $rtmedia_album = new RTMediaAlbum();
        if(isset($options['default_global']) && $options['default_global'] != "" ) {
            $default_global_album  = $options['default_global'];
            $global_albums = get_site_option ( 'rtmedia-global-albums' );
	    if( is_array( $global_albums ) ) {
		$new_default_global_album_index = array_search($default_global_album, $global_albums);
		$global_albums[$new_default_global_album_index] = $global_albums['0'];
		$global_albums['0'] = $default_global_album;
		rtmedia_update_site_option('rtmedia-global-albums', $global_albums);
	    }
        }
	global $rtmedia_save_setting_single;
        if(isset( $rtmedia_save_setting_single ) && $rtmedia_save_setting_single && isset($options['new_global_album']) && $options['new_global_album'] != "" ) {
            $rtmedia_album->add_global($options['new_global_album']);
        }
        return $options;
    }
    function rtmedia_pro_add_settings_tab($sub_tabs) {
        $sub_tabs[25] = array(
                'href' => '#rtmedia-global-albums',
                'icon' => 'rtmicon-globe',
                'title' => __ ( 'rtMedia Default Albums', 'rtmedia' ),
                'name' => __ ( 'Default Albums', 'rtmedia' ),
                'callback' => array( 'RTMediaProGlobalAlbums', 'global_albums_content' )
            );
        return $sub_tabs;
    }

    public static function global_albums_content() {
        global $rtmedia;
        $global_albums = rtmedia_global_albums();
        $model = new RTMediaModel();
        $rtmedia_album = new RTMediaAlbum();
        $default_album = $rtmedia_album->get_default();
        $album_objects = $model->get_media ( array( 'id' => ($global_albums) ), false, false );
        ?>
            <div class="global-album-settings">
		<div class="postbox metabox-holder">
		    <h3 class="hndle"><span>Default Albums</span></h3>
		</div>
                <div class="row">
                    <div class="columns large-12">
                        <h4><?php _e('List of albums:', 'rtmedia'); ?></h4>
                    </div>
		</div>
		<div class="row">
                    <div class="columns large-12">
        <?php
                    if ( $album_objects ) {
	?>
			<table class="default-global-albums">
			    <thead>
			      <tr>
				<th>Album name</th>
				<th colspan="2">Actions</th>
				<th>Preselected album</th>
			      </tr>
			    </thead>
			    <tbody>
	<?php
                        foreach ( $album_objects as $album ) {
                            if($default_album == $album->id)
                                $checked = "checked='checked'";
                            else
                                $checked = "";
        ?>
				<tr>
				    <td class="global-album-name">
					<?php echo $album->media_title; ?>
				    </td>
				    <td>
					<label title="Rename" onclick ='rename_global_album(this)' class="rename-label" id='rename_global_album_<?php echo $album->id; ?>'>
					    <i class="rtmicon-edit" > <?php _e('Edit','rtmedia'); ?></i>
					</label>
				    </td>
				    <td class="delete-td">
			<?php
					if($checked == "") {
					    $style = "";
					}
					else {
					    $style = "style='display:none'";
					}
                        ?>
					<label title="Delete" onclick ='delete_global_album(this)' class="delete-label" id='delete_global_album_<?php echo $album->id; ?>' <?php echo $style; ?>>
					    <i class="rtm-delete-global-album rtmicon-trash-o"> <?php _e('Delete','rtmedia'); ?></i>
					</label>
				    </td>
				    <td>
					<input type='radio' name='rtmedia-options[default_global]' id='default_album_radio_<?php echo $album->id; ?>' value='<?php echo $album->id; ?>' <?php echo $checked; ?> />
				    </td>
				</tr>
        <?php
                        }
                    }
        ?>
			</tbody>
                    </table>
                </div>
	    </div>

	    <div class="postbox metabox-holder">
		<h3 class="hndle"><span>Add New Default Album</span></h3>
	    </div>
	    <div class="row">
		<div class="columns large-12">
		    <form name="add_new_global_album_form">
			<div class="columns large-12">
			    <input type="text" name="rtmedia-options[new_global_album]" />
			    <input type="submit" class="button" name="rtmedia-options[add_new_global_album]" value="<?php _e('Add','rtmedia'); ?>" />
			</div>
		    </form>
		</div>
	    </div>
	</div>
        <?php
    }
}