<?php

class RTMediaEditorLoader {

    public static function get_context_option($curr_context) {
        $post_types = get_post_types(array('show_ui'=>true));
        $context_option = "<option value='default'>Default</option>";
        if(is_array($post_types) && $post_types != "") {
	    foreach($post_types as $post_type) {
                if($post_type == "attachment") {
		    continue;
		}
                if($post_type == $curr_context) {
		    $context_selected = "selected";
		} else {
		    $context_selected = "";
		}
                $context_option.=  "<option value='$post_type' $context_selected> ".  ucfirst(str_replace("_", " ", $post_type))."</option>";
            }
	}
        $context_option .= "<option value='profile'>Profile</option>  <option value='group'>Group</option>";
        return $context_option;
    }

    public static function content() {
?>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>rtMedia Pro Shortcode</title>
                <script type="text/javascript" src="<?php echo includes_url("js/tinymce/tiny_mce_popup.js"); ?>"></script>
                <style type="text/css">
                    .button-primary {
                        background-color: #21759B;
                        background-image: linear-gradient(to bottom, #2A95C5, #21759B);
                        border-color: #21759B #21759B #1E6A8D;
                        box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset;
                        color: #FFFFFF;
                        text-decoration: none;
                        text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
                    }
					.rtm-shortcode-generator p > label {
						display: inline-block;
						width: 40%;
					}
                </style>
            </head>
			<body>
		<?php
		    $query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
		    parse_str($query, $params);
		    $curr_context = ( isset( $params['post_type']) ? $params['post_type'] : "");
		    $curr_context_id = ( isset( $params['post'] ) ? $params['post'] : "" );
		?>
				<div class="rtm-shortcode-generator">
					<p>
						<label>Shortcode:</label>
						<select name="shortcode_select" id="shortcode_select">
							<option value="gallery">Gallery</option>
							<option value="uploader">Uploader</option>
						</select>
					</p>

                    <div class="gallery-container" id="gallery_container">
                        <form name="rtmedia_pro_shortcode_gallery" onsubmit="return rtmediaproshortcode.insert();" action="#">
                            <p>
                                <label>Global:</label>
                                <span><input type="radio" name="global_media" onclick="return change_global(this);" id="global_media_true" value="true" /><label for="global_media_true">True</label></span>
                                <span><input type="radio" name="global_media" onclick="return change_global(this);" id="global_media_false" value="false" checked="checked" /><label for="global_media_false">False</label></span>
                            </p>
                            <p id="context_container">
                                <label>Context:</label>
                                <select name="gal_context" id="gal_context" >
                                    <?php echo self::get_context_option($curr_context); ?>
                                </select>
                            </p>
                            <p id="context_id_container">
                                <label>Context ID:</label>
                                <input type="number" name="gal_context_id" id="gal_context_id" <?php if($curr_context_id != "" && $curr_context_id != "0") echo "value='$curr_context_id'"  ?>><sup>*</sup>
                            </p>
                            <p id="media_type_container">
                                <label>Media Type:</label>
                                <select name="media_type" id="media_type" >
                                    <option value="all">All</option>
                                        <?php
                                            global $rtmedia;
                                            foreach ( $rtmedia->allowed_types as $value ) {
                                                echo "<option value='$value[name] '>$value[label]</option>";
                                            }
                                        ?>
                                </select>
                            </p>
							<p id="favlist_id_container">
								<label>FavList:</label>
								<input type="number" name="favlist_id" id="favlist_id" />
							</p>
                            <p>
                                <label>Author:</label>
                                <input type="number" name="author_id" id="author_id" /><sup>*</sup>
                            </p>
			    			<p>
								<label>Uploader:</label>
								<select name="rtmedia_gallery_uploader" id="rtmedia_gallery_uploader">
									<option value="false" selected="selected">False</option>
									<option value="true">True</option>
									<option value="before">Before</option>
									<option value="after">After</option>
								</select>
			    			</p>
							<p>
								<label>Lightbox:</label>
								<select name="rtmedia_gallery_lightbox" id="rtmedia_gallery_lightbox">
									<option value="true">Enable</option>
									<option value="false">Disable</option>
								</select>
							</p>
							<p>
								<label>Media Title:</label>
								<select name="rtmedia_gallery_media_title" id="rtmedia_gallery_media_title">
									<option value="true">Show</option>
									<option value="false">Hide</option>
								</select>
							</p>
                            <input type="submit" class="button-primary" value="Insert into Post" name="submit_gallery" />
                        </form>
                    </div>

                    <div class="uploader-container" id="uploader_container" style="display: none">
                        <form name="rtmedia_pro_shortcode_uploader" onsubmit="return rtmediaproshortcode.insert();" action="#">
                            <p>
                                <label>Context:</label>
                                <select name="upl_context" id="upl_context" >
                                    <?php echo self::get_context_option($curr_context); ?>
                                </select>
                            </p>
                            <p>
                                <label>Context ID:</label>
                                <input type="number" name="upl_context_id" id="upl_context_id" <?php if($curr_context_id != "" && $curr_context_id != "0") echo "value='$curr_context_id'"  ?>><sup>*</sup>
                            </p>
			    			<p>
                                <label>Media Type:</label>
                                <select name="media_type" id="media_type_upload" >
                                    <option value="all">All</option>
                                        <?php
                                            global $rtmedia;
                                            foreach ( $rtmedia->allowed_types as $value ) {
						if( $value['name'] == "playlist" ) {
						    continue;
						}
                                                echo "<option value='$value[name] '>$value[label]</option>";
                                            }
                                        ?>
                                </select>
                            </p>
                            <p>
                                <label>Album:</label>
                                <select name="album_list" id="album_list">
                                    <?php  echo rtmedia_user_album_list(); ?>
                                </select>
                            </p>
                            <p>
                                <label>Privacy:</label>
                                    <?php $rtmedia_privacy = new RTMediaPrivacy(false);
					    echo str_replace("rtSelect0", "privacy",$rtmedia_privacy->select_privacy_ui(false)) ;
				    ?>
                            </p>
                            <p>
                                <label>Redirect:</label>
                                <input type='radio' name="redirect" value='true' id="redirect_true" onclick="change_redirect(this.value)" ><label for='redirect_true'>True</label>
                                <input type='radio' name="redirect" value='false' checked='checked' id="redirect_false" onclick="change_redirect(this.value)"><label for='redirect_false'>false</label>
                            </p>
                            <input type="submit" class="button-primary" value="Insert into Post" name="submit_uploader" />
                        </form>
                    </div>
                    <p><sup>*</sup> Leave blank for default</p>
				</div>
                <script type="text/javascript" src="<?php echo trailingslashit(RTMEDIA_PRO_URL); ?>app/assets/js/rtmedia_pro_short_code_script.js"></script>
            </body>
        </html>
<?php
        die();
    }
}