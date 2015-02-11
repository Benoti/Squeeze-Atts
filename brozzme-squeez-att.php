<?php
/**
 * Plugin Name: Brozzme SQUEEZE Atts
 * Plugin URL: http://brozzme.com/squeeze-atts/
 * Description: Remove post and its attachments
 * Version: 0.1
 * Author: Benoît Faure
 * Author URI: http://brozzme.com
 */
// plugin activation



defined( 'ABSPATH' ) OR exit;

ini_set('display_errors', 'off');
function brozzme_bsa_Setup_Demo_on_activation()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "activate-plugin_{$plugin}" );


    # Uncomment the following line to see the function in action
    // exit( var_dump( $_GET ) );
}

function brozzme_bsa_Setup_Demo_on_deactivation()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "deactivate-plugin_{$plugin}" );

    # Uncomment the following line to see the function in action
    // exit( var_dump( $_GET ) );
}

function brozzme_bsa_Setup_Demo_on_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    check_admin_referer( 'bulk-plugins' );

    // Important: Check if the file is the one
    // that was registered during the uninstall hook.
    if ( __FILE__ != WP_UNINSTALL_PLUGIN )
        return;

    # Uncomment the following line to see the function in action
    //exit( var_dump( $_GET ) );
}

register_activation_hook(   __FILE__, 'brozzme_bsa_Setup_Demo_on_activation' );
register_deactivation_hook( __FILE__, 'brozzme_bsa_Setup_Demo_on_deactivation' );
register_uninstall_hook(    __FILE__, 'brozzme_bsa_Setup_Demo_on_uninstall' );

// add plugin menu to WordPress administration
add_action( 'admin_menu', 'brozzme_bsa_add_admin_menu' );

function brozzme_bsa_add_admin_menu(  ) {

    add_menu_page( 'Brozzme Squeeze', 'Squeeze Atts', 'manage_options','squeeze-att-admin', 'brozzme_bsa_welcome_page', 'dashicons-trash');
   add_submenu_page('squeeze-att-admin', 'Squeezer', 'Squezzer', 'manage_options', 'squeeze-att-dashboard', 'brozzme_bsa_dashboard_page');
    // add_submenu_page('rapid-pub-admin', 'Bloc d\affichage prédéfinis', 'Area presets', 'manage_options', 'rapid-pub-dashboard', 'brozzme_rapid_pub_welcome_page');
    //add_options_page('Settings Rapid Pub', 'Rapid Pub Settings', 'manage_options', 'rapid-pub-settings', 'brozzme_rapid_pub_options_page');
}
function brozzme_bsa_welcome_page(){

    echo '<h1>SQUEEZE Atts</h1>';
    echo '<p><b>SQUEEZE Atts</b> allow you to erase a post and its attachments.<br/>You just have to type the id of the post you want to scan and press "scan".</p>';

}
function brozzme_bsa_dashboard_page(){
    echo '<h3>SQUEEZE Atts dashboard</h3>';
    echo date('d/m/Y H:i:s'). ' | Attachments and old posts to SQUEEZE ?';
    echo '<br/>';
    echo '<div class="update-nag" style="padding-bottom: 25px;"><h2>This operation can not be undone, use it carefully !</h2></div>';
    echo '<br/><br />';
    echo '<b>Type the post ID you want to scan</b><br />';
    echo '<form action="admin.php" method="get">
            <input type="hidden" name="page" value="squeeze-att-dashboard" />
            <input type="text" name="bsa_id" value=""/>
            <input name="submit" type="submit" value="scan" class="button button-primary button-large">
          </form>';
    echo '<br/>';
    $version = get_bloginfo('version');
            if(isset($_GET['bsa_id'])!=''){

                if ($version < 3.6) {
                    $post_details = get_children( array( 'post_parent' => $_GET['bsa_id'] ) );
                } else {
                    $post_details = get_attached_media( 'image', $_GET['bsa_id'] );
                }


                $attachments = get_children( array( 'post_parent' => $_GET['bsa_id'] ) );
                $count = count( $attachments );
                         if($count == '1'){$atts_display = 'attachment';}
                        else{$atts_display='attachments';}
     echo '<h3>'. $count . ' ' .$atts_display. ' for this post</h3>';
                foreach($post_details as $attachment){
                    $image_attributes = wp_get_attachment_image_src( $attachment->ID, 'thumbnail', 'false' );
                    $image_metadata = wp_get_attachment_metadata( $attachment->ID, 'false' );
                        if( $image_attributes ) {
                        echo '<div style ="padding:15px;border-top:1px dashed #ccc;font-size:16px;margin-bottom:15px;">
                        <div style="float:left;padding-right:25px;"><img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'" class="size-thumbnail"/></div>
                        <div style="float:left;vertical-align:top line-height:24px;">
                        '.$attachment->ID . ' / <a href="' .$attachment->guid . '" target="_blank">Open attachment in a new tab</a><br / >
                        File : '.$image_metadata['file'].'<br />

                        </div>
                        <div style="clear:left;padding-bottom:15px;"></div></div>';

                        }
                }
                echo '<div style="padding-top:20px;"><form action="admin.php" method="get">
                    <input type="hidden" name="page" value="squeeze-att-dashboard" />
                    <input type="hidden" name="bsa_postid" value="'.$_GET['bsa_id'].'"/>
                    <input type="hidden" name="bsa_auth" value="true"/>

                    <h3 style="padding-bottom:10px;">Press if you want to erase ' .$atts_display.'</h3> <input name="submit" type="submit" value="Erase ' .$atts_display.'" class="button button-primary button-large">
                  </form></div>';
              //  var_dump($post_details);
            }
            if(isset($_GET['bsa_postid'])!='' && isset($_GET['bsa_auth'])== 'true' ){
                if ($version < 3.6) {
                    $post_details = get_children( array( 'post_parent' => $_GET['bsa_postid'] ) );
                } else {
                    $post_details = get_attached_media( 'image', $_GET['bsa_postid'] );
                }

                foreach($post_details as $attachment){
                    wp_delete_attachment( $attachment->ID );
                }
                echo '<h3>Do you want to erase this post ?</h3>';
                echo '<form action="admin.php" method="get">
                    <input type="hidden" name="page" value="squeeze-att-dashboard" />
                    <input type="hidden" name="bsa_gbpostid" value="'.$_GET['bsa_postid'].'"/>
                    <input type="hidden" name="bsa_auth_post" value="true"/>

                    <b>Press if you want to squeeze this post</b> ('.$_GET['bsa_postid'].') <input name="Supprimer" type="submit" class="button button-primary button-large">
                  </form>';

            }
        if(isset($_GET['bsa_gbpostid'])!='' && isset( $_GET['bsa_auth_post'])== 'true'){
            // deleting post datas
            $deleting_process = wp_delete_post($_GET['bsa_gbpostid'], true);
            if($deleting_process == true){
            echo '<div class="updated">OK, post squeezed...</div>';
            }
        }
}
add_filter('manage_posts_columns', 'posts_columns_attachment_count', 5);
add_action('manage_posts_custom_column', 'posts_custom_columns_attachment_count', 5, 2);
function posts_columns_attachment_count($defaults){
    $defaults['wps_post_attachments'] = __('Att');
    $defaults['wps_squeeze_attachments'] = __('Squeeze');
    return $defaults;
}
function posts_custom_columns_attachment_count($column_name, $id){
    if($column_name === 'wps_post_attachments'){
        $attachments = get_children(array('post_parent'=>$id));
        $count = count($attachments);
        if($count !=0){echo $count;}
    }
    if($column_name === 'wps_squeeze_attachments'){
        $attachments = get_children(array('post_parent'=>$id));
        $count = count($attachments);
        if($count !=0){echo '<a href="admin.php?page=squeeze-att-dashboard&bsa_id='.$id.'&submit=scan">Squeeze atts</a>';}
    }
}
