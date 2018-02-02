<?php
/* 
Plugin Name: flipcard
Description: Create simple cards with text or pictures that flip over when moused over. Insert anywhere with shortcodes.
*/

/* 
    PLUGIN INSTALLATION
*/
function flipcard_setup_post_type() {
    // register the "flipcard" custom post type
    register_post_type('flipcard',
                       [
                           'labels'      => [
                               'name'           => __('FlipCards'),
                               'singular_name'  => __('FlipCard'),
                               'add_new_item'   => __('Add New FlipCard'),
                               'new_item'       => __('New FlipCard'),
                               'edit_item'      => __('Edit FlipCard'),
                               'view_item'      => __('View FlipCard'),
                               'view_items'     => __('View FlipCards'),
                           ],
                           'public'      => true,
                       ]
    );
}
add_action( 'init', 'flipcard_setup_post_type' );
function flipcard_install() {
    // trigger our function that registers the custom post type
    flipcard_setup_post_type();
 
    // clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}
register_activation_hook( 'plugin-flipcard.php', 'flipcard_install' );
function flipcard_deactivation() {
    // unregister the post type, so the rules are no longer in memory
    unregister_post_type( 'flipcard' );
    // clear the permalinks to remove our post type's rules from the database
    flush_rewrite_rules();
}
register_deactivation_hook( 'plugin-flipcard.php', 'flipcard_deactivation' );
function flipcard_register_meta_boxes(){
    add_meta_box(
        'flipcard',
        'FlipCard',
        'get_flipcard_meta_box',
        'flipcard',
        'normal'
    );
}
add_action( 'add_meta_boxes', 'flipcard_register_meta_boxes' );
// ALL THE ENQUEUE CSS/JS STUFF
function flipcard_enqueue_scripts(){
    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );      
    wp_enqueue_script( 'flipcard_js', plugins_url( 'plugin-flipcard.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 
    wp_enqueue_style( 'flipcard_css', plugin_dir_url(__FILE__) . 'plugin-flipcard.css');
    wp_enqueue_style( 'flipcard_css', plugin_dir_url(__FILE__) . 'plugin-flipcard-shortcode.css');
}
add_action('admin_enqueue_scripts','flipcard_enqueue_scripts');
function flipcard_enqueue_shortcode(){
    wp_enqueue_style( 'flipcard_css', plugin_dir_url(__FILE__) . 'plugin-flipcard-shortcode.css');
}
add_action('wp_enqueue_scripts','flipcard_enqueue_shortcode');
// removes the normal editor on flipcard posts
function init_remove_support(){
    $post_type = 'flipcard';
    remove_post_type_support( $post_type, 'editor');
}
add_action('init', 'init_remove_support',100);

/* 
    FLIPCARD EDITOR META BOX
*/
// creates the metabox for the flipcard custom post type
function get_flipcard_meta_box($post){
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'flipcard_nonce_action', 'flipcard_nonce_field' );
    // Get WordPress' media upload URL
    $upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );
    // Retrieve flipcard post meta data
    $flipcard_data = get_post_meta( $post->ID, 'flipcard', true );
    //print_r($flipcard_data);
    
    // Validate returned data
    if (empty($flipcard_data)){
        echo "Could not find flipcard data!";
        //return;
    }
    // Process meta data
    $front_text = htmlspecialchars_decode($flipcard_data['front_text']);
    $back_text = htmlspecialchars_decode($flipcard_data['back_text']);
    $front_image_id = $flipcard_data['front_image_id'];
    $back_image_id = $flipcard_data['back_image_id'];
    $has_front_img = !empty($front_image_id);
    $has_back_img = !empty($back_image_id);
    if ($has_front_img) $front_image_url = wp_get_attachment_image_url($front_image_id, [300,200]);
    if ($has_back_img) $back_image_url = wp_get_attachment_image_url($back_image_id, [300,200]);
    
    ?>

    <!-- HTML for flipcard creation form -->
    <div class="flipcard_info">
        Please ensure attached images are 300px wide and 200px tall; or maintain a 3:2 aspect ratio.
        <br>
        Copy and paste this shortcode into any page or post to display this flipcard.<br>
        <span class="highlight_shortcode">[flipcard id='<?php echo get_the_ID() ?>']</span>
    </div>
    <table style="width:100%">
        <tr class="text_row">
            <td>
                <label for="front_text">Front Text</label><br>
                <textarea style="width:100%" id="front_text" name="front_text" rows=3><?php echo $front_text ?></textarea>
                <input id="front_text_color" name="front_text_color" type="text" class="color-picker" value="<?php echo $flipcard_data['front_text_color']; ?>"/>
            </td>
            <td>
                <label for="back_text">Back Text</label><br>
                <textarea style="width:100%" id="back_text" name="back_text" rows=3><?php echo $back_text ?></textarea>
                <input id="back_text_color" name="back_text_color" type="text" class="color-picker" value="<?php echo $flipcard_data['back_text_color']; ?>"/>
            </td>
        </tr>
        <tr class="image_row">
            <td>
                <p>Front Image</p>
                <div class="image_preview" id="preview_front">
                    <?php if ($has_front_img) : ?>
                    <img 
                        class="flipcard_image flipcard_front_image"
                        src="<? echo $front_image_url ?>"
                    >
                    <?php endif ?>
                </div>
                <div class="hide-if-no-js">
                    <a id="upload_front" <?php if ( $has_front_img  ) { echo 'hidden'; } ?>
                    href="<?php echo $upload_link ?>">
                        <?php _e('Set custom image') ?>
                    </a>
                    <a id="delete_front" <?php if ( ! $has_front_img  ) { echo 'hidden'; } ?> 
                    href="#">
                        <?php _e('Remove this image') ?>
                    </a>
                </div>
                <input id="id_front" name="id_front" type="hidden" value="<?php echo $front_image_id ?>" />
                <input id="front_bkg_color" name="front_bkg_color" type="text" class="color-picker" value="<?php echo $flipcard_data['front_bkg_color']; ?>"/>
            </td>
            <td>
                <p>Back Image</p>
                <div class="image_preview" id="preview_back">
                    <?php if ($has_back_img) : ?>
                    <img 
                        class="flipcard_image flipcard_back_image"
                        src="<? echo $back_image_url ?>"
                    >
                    <?php endif ?>
                </div>
                <div class="hide-if-no-js">
                    <a id="upload_back" <?php if ( $has_back_img  ) { echo 'hidden'; } ?> 
                    href="<?php echo $upload_link ?>">
                        <?php _e('Set custom image') ?>
                    </a>
                    <a id="delete_back" <?php if ( ! $has_back_img  ) { echo 'hidden'; } ?> 
                    href="#">
                        <?php _e('Remove this image') ?>
                    </a>
                </div>
                <input id="id_back" name="id_back" type="hidden" value="<?php echo $back_image_id ?>" />
                <input id="back_bkg_color" name="back_bkg_color" type="text" class="color-picker" value="<?php echo $flipcard_data['back_bkg_color']; ?>"/>
            </td>
        </tr>
    </table>
    
    <?php
}

function save_flipcard_meta_form($post_id){
    // Check if our nonce is set.
    if ( ! isset( $_POST['flipcard_nonce_field'] ) ) {
        return;
    }
    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['flipcard_nonce_field'], 'flipcard_nonce_action' ) ) {
        return;
    }
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check the user's permissions.
    // TODO: look into this further later
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    /* OK, it's safe for us to save the data now. */

    // validation
    // could check if empty or proper
    // text may have character limit
    // check if url is good url

    // Sanitize user input.
    //TODO allow html in text fields
    $flipcard_data = Array(
        'front_text'        => htmlspecialchars( $_POST['front_text'] ),
        'back_text'         => htmlspecialchars( $_POST['back_text'] ),
        'front_image_id'    => sanitize_text_field( $_POST['id_front'] ),
        'back_image_id'     => sanitize_text_field( $_POST['id_back'] ),
        'front_text_color'  => sanitize_text_field( $_POST['front_text_color'] ),
        'back_text_color'   => sanitize_text_field( $_POST['back_text_color'] ),
        'front_bkg_color'   => sanitize_text_field( $_POST['front_bkg_color'] ),
        'back_bkg_color'    => sanitize_text_field( $_POST['back_bkg_color'] ),
    );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'flipcard', $flipcard_data );
}
add_action( 'save_post', 'save_flipcard_meta_form' );

/* 
    FLIPCARD SHORTCODE
*/
add_shortcode("flipcard","flipcard_shortcode");
function flipcard_shortcode($atts){
    // combine given params with defaults 
    $atts = shortcode_atts(
        array(
            'id'=>'-1'
        ), $atts, 'flipcard');
    $flipcard_id = $atts['id'];
    // No ID value given
    if(strcmp($flipcard_id, '-1') == 0){
        return "";
    }

    // Retrieve flipcard post meta data
    $flipcard_data = get_post_meta( $flipcard_id, 'flipcard', true );
    // Validate returned data
    if (empty($flipcard_data)){
        return "";
    }
    // Process meta data
    $front_text = htmlspecialchars_decode($flipcard_data['front_text']);
    $front_text_color = $flipcard_data['front_text_color'];
    $back_text = htmlspecialchars_decode($flipcard_data['back_text']);
    $back_text_color = $flipcard_data['back_text_color'];
    $front_image_id = $flipcard_data['front_image_id'];
    $back_image_id = $flipcard_data['back_image_id'];
    $has_front_img = !empty($front_image_id);
    $has_back_img = !empty($back_image_id);
    if ($has_front_img) $front_image_url = wp_get_attachment_image_url($front_image_id, [300,200]);
    if ($has_back_img) $back_image_url = wp_get_attachment_image_url($back_image_id, [300,200]);
    $front_bkg_color = $flipcard_data['front_bkg_color'];
    $back_bkg_color = $flipcard_data['back_bkg_color'];

    ?>
    <!-- HTML to display the flipcard -->
    <div class="flipcard_container">
        <div class="flipcard_rotates">
            <div class="flipcard_front" 
                <?php 
                    $out = 'style="background-color:'.$front_bkg_color.';';
                    if ($has_front_img) $out .= 'background-image:url('.$front_image_url.')';
                    $out .= '"';
                    echo $out;
                ?>>
                <div class="flipcard_text flipcard_front_text" style="color:<? echo $front_text_color ?>">
                    <?php echo $front_text; ?>
                </div>
            </div>
            <div class="flipcard_back" 
                <?php 
                    $out = 'style="background-color:'.$back_bkg_color.';';
                    if ($has_back_img) $out .= 'background-image:url('.$back_image_url.')';
                    $out .= '"';
                    echo $out;
                ?>>
                <div class="flipcard_text flipcard_back_text" style="color:<? echo $back_text_color ?>">
                    <?php echo $back_text; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
}