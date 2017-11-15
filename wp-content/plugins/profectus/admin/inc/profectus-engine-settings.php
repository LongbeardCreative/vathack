<?php settings_errors(); ?>
<?php
global $oxygen_api;
    $api_url = "\0CT_API\0api_url";
    $server_url = "\0CT_API\0server_url";
    $consumer_key = "\0CT_API\0consumer_key";
    $consumer_secret = "\0CT_API\0consumer_secret";;
    $a = (array) $oxygen_api;
?>
<div class="pf_info">
	<p><strong>Profectus Engine</strong></p>
    <span>Current SERVER URL: <?php echo $a[$server_url]; ?></span>
    <br>
    <span>Current API URL: <?php echo $a[$api_url]; ?></span>
    <br>
    <span>Consumer Key: <?php echo $a[$consumer_key]; ?></span>
    <br>
    <span>Consumer Secret: <?php echo $a[$consumer_secret]; ?></span>
</div>
<br><br>


<form method="post" action="options.php">
    <?php settings_fields( 'pf-engine-group' ); ?>
    <?php do_settings_sections( 'pf_settings' ); ?>
    <?php submit_button(); ?>
</form>