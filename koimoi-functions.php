<?php  
/* 
Plugin Name: KoiMoi Box Office Widget
Description: Show the current top grossing movies as a WordPress widget on your blog
Author: KoiMoi
Version: 1.1
*/   

error_reporting(0);

global $wpdb, $koimoi_table;
$koimoi_table = $wpdb->prefix . "koimoi_config";



add_action('wp_ajax_koimoi_update', 'koimoi_update');
add_action('wp_ajax_nopriv_koimoi_update', 'koimoi_update');

function koimoi_update()
{
    global $wpdb, $koimoi_table;
    $type = addslashes($_POST['step-1-type']);
    $theme = addslashes($_POST['step-2-type']);

    echo $type;


    $wpdb->query( "UPDATE $koimoi_table SET
        description = '$type'
        WHERE name = 'type'" );

    $wpdb->query( "UPDATE $koimoi_table SET
        description = '$theme'
        WHERE name = 'theme'" );

    die();
}

function koimoi_footer()
{
    add_options_page('KoiMoi Box Office Widget Config', 'KoiMoi Box Office', 'manage_options', 'koimoi-admin.php', 'koimoi_admin_page');
}

function koimoi_admin_page()
{
    include('koimoi-admin.php');
}

add_action('admin_menu', 'koimoi_footer');

function koimoi_activate()
{

    error_reporting(0);
    global $wpdb, $koimoi_table;

    if($wpdb->get_var("SHOW TABLES LIKE '$koimoi_table'") != $koimoi_table) {


        $sql = "CREATE TABLE $koimoi_table (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          name tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          description tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
          UNIQUE KEY id (id)
          ) CHARACTER SET utf8 COLLATE utf8_general_ci";


require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);


$insert = $wpdb->insert( $koimoi_table, array( 
    'name' => 'type',
    'description' => '1'
    ) );

$insert = $wpdb->insert( $koimoi_table, array( 
    'name' => 'theme',
    'description' => '1'
    ) );


}

}


register_activation_hook( __FILE__, 'koimoi_activate' );


function koimoi_register_styles()
{
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_style( 'koimoi_styles_2', plugins_url( 'koimoi-style.css', __FILE__ ));
}

class koimoi_widget extends WP_Widget
{
    function koimoi_widget()
    {
        $widget_ops2 = array('classname' => 'koimoi_widget', 'description' => 'KoiMoi Box Office');
        $this->WP_Widget('koimoi_widget', 'KoiMoi', $widget_ops2);
    }

    function form_km($instance)
    {
        echo 'Show KoiMoi widget';
    }

    function widget()
    {


        global $wpdb;
        $koimoi_table = $wpdb->prefix . "koimoi_config";

        $qry = $wpdb->get_results( "SELECT * FROM $koimoi_table", 'ARRAY_A' );

        $json_url = 'http://www.koimoi.com/wp-content/plugins/BO/BO_data.txt';
        $json_string = '[]';

        $ch = curl_init( $json_url ); 
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
            CURLOPT_POSTFIELDS => $json_string
            );

        curl_setopt_array( $ch, $options );
        $result =  curl_exec($ch);

        $result = json_decode($result, 1);


        koimoi_register_styles();
        $body = '';
        $footer = '';


        if ($qry[0]['description']=='2') {
        $head = "<div class='km_head theme".$qry[1]['description']."'>Box Office Top 5 <a href='http://koimoi.com'><img src='".plugins_url('koimoi-box-office/koimoi.png')."'></a></div>";

            foreach ($result as $key => $item)
            {
                $body .= "<a class='km_row' href='$item[2]' target='_blank'><span class='km_span_left'>$key. $item[1]</span><span class='km_span_right'>$item[3]</span></a>";
            }
            $footer = "<a class='km_row km_footer' href='http://www.koimoi.com/box-office-bollywood-films-of-2013/' target='_blank'><span class='km_span_left'><em>view the rest ...</em></span><span class='km_span_right'></span></a>";
        }
        else
        {
        $head = "<div class='km_head theme".$qry[1]['description']."'>Box Office Top 3 <img src='".plugins_url('koimoi-box-office/koimoi.png')."'></div>";
            $i = 0;
            foreach ($result as $key => $item)
            {
                if ($i>2)
                {
                    break;
                }
                $i++;

                $body .= "<a class='km_row' target='_blank'><span class='km_span_left'>$key. $item[1]</span><span class='km_span_right'>$item[3]</span></a>";
            }
        }


        echo "<div class='km_cover'>$head<div class='km_body'>$body$footer</div></div>";


    }
}



add_action( 'widgets_init', create_function('', 'return register_widget("koimoi_widget");') );


?>