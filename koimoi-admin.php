<?php


global $wpdb;
$koimoi_table = $wpdb->prefix . "koimoi_config";

$qry = $wpdb->get_results( "SELECT * FROM $koimoi_table", 'ARRAY_A' );



wp_enqueue_style( 'koimoi_style', plugins_url( 'koimoi-style.css', __FILE__ ));


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


$head = "<div class='km_head'>Box Office Top 5 <img src='".plugins_url('koimoi-box-office/koimoi.png')."'></div>";
$body = '';

foreach ($result as $key => $item)
{
	$body .= "<a class='km_row' href='$item[2]' target='_blank'><span class='km_span_left'>$key. $item[1]</span><span class='km_span_right'>$item[3]</span></a>";
}

$footer = "<a class='km_row km_footer' href='http://www.koimoi.com/box-office-bollywood-films-of-2013/' target='_blank'><span class='km_span_left'><em>view the rest ...</em></span><span class='km_span_right'></span></a>";

$widget1 = "<div class='km_cover' style='width: 310px; text-align: left'>$head<div class='km_body'>$body$footer</div></div>";




$head = "<div class='km_head'>Box Office Top 3 <img src='".plugins_url('koimoi-box-office/koimoi.png')."'></div>";
$body = '';

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

$widget2 = "<div class='km_cover' style='width: 310px; text-align: left'>$head<div class='km_body'>$body</div></div>";


?>





<form id='koimoi_form'>
	<div class='step step-1' style='width: 75%'>

		<h1>Widget Type</h1>

		<label class='label-1 mb9'>

			<?php echo $widget2; ?>
			<p>Show the Top 3 Movies on the Box Office</p>
			<p>No links to Movie Reviews</p>

			<input type='radio' class='koimoi_form_radio' name='step-1-type' value='1' <?php if ($qry[0]['description']=='1') {echo 'checked'; }?> >
		</label>
		<label class='label-1'>

			<?php echo $widget1; ?>
			<p>Show the Top 5 Movies on the Box Office</p>
			<p>Links to Movie Reviews</p>

			<input type='radio' class='koimoi_form_radio' name='step-1-type' value='2' <?php if ($qry[0]['description']=='2') {echo 'checked'; }?> >
		</label>

	</div>

	<div class='step step-2' style='width: 22%'>

		<h1>Theme</h1>

		<label class='label-1' style='background-color: #777'>Black
			<input type='radio' class='koimoi_form_radio' name='step-2-type' value='1' <?php if ($qry[1]['description']=='1') {echo 'checked'; }?> >
		</label>
		<label class='label-1' style='background-color: rgb(50, 107, 255)'>Blue
			<input type='radio' class='koimoi_form_radio' name='step-2-type' value='2' <?php if ($qry[1]['description']=='2') {echo 'checked'; }?> >
		</label>
		<label class='label-1' style='background-color: green'>Green
			<input type='radio' class='koimoi_form_radio' name='step-2-type' value='3' <?php if ($qry[1]['description']=='3') {echo 'checked'; }?> >
		</label>
		<label class='label-1' style='background-color: red'>Red
			<input type='radio' class='koimoi_form_radio' name='step-2-type' value='4' <?php if ($qry[1]['description']=='4') {echo 'checked'; }?> >
		</label>

	</div>
	<div class='step step-3' style='display: none'>
		Status
	</div>
</form>
<script>
function setup_klabels()
{

	jQuery('.koimoi_form_radio').each(function(){
		if(jQuery(this).is(':checked'))
		{
			jQuery(this).parents('label').addClass('deep');
		}
		else
		{
			jQuery(this).parents('label').removeClass('deep');
		}
	});


jQuery('.km_head').removeClass('theme1').removeClass('theme2').removeClass('theme3').removeClass('theme4');

var abc = jQuery("input:radio[name='step-2-type']:checked").val();
jQuery('.km_head').addClass('theme'+abc);




}

	jQuery(document).ready(function(){
		setup_klabels();
		jQuery('.label-1').click(function(){
			setup_klabels();
		});

		jQuery('.koimoi_form_radio').change(function(){
			var url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
			jQuery('.step-3').text('Saving ...');
			jQuery.ajax({
				url: ajaxurl,
				type: "POST",
				data: 'action=koimoi_update&'+jQuery('#koimoi_form').serialize(),
				success: function (response) {
					jQuery('.step-3').text('Saved');
					window.saving = false;
				},
				error: function (response) {
					jQuery('.step-3').text('Failed');
					window.saving = false;
				}
			});
		});
	});
</script>
