<?php
/**
 * @copyright   Copyright (c) 2015, Addendio.com
 * @since       1.0
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function addplus_search_themes() {
	global $addplus_fs;
?>

	<div class="wrap" id="addplus_searchpage"> <!-- START WRAP DIV -->
		<h2>Search Themes with Addendio</h2>
			<div class="row"></div>
		<br>

			<!--Addendio widget - BEGIN-->
			<script id="addendio_widget_script" async type="text/javascript" data-api_key="3390221d619ec1622c65f3f5d0c65e48"
							data-uilanguage=""  data-forbidden_list="" data-platform_version="17.0" data-platform_platform=""></script>

			<div id="addendio-container" class="add-cpanel-container">
					<div id="addendio-loading">
							<img id="img_gears" src="https://assets.addendio.com/widget/prod/addendio-plus/img/gears.svg">
					</div>
			</div>
	<!--Addendio widget - END-->

	<?php
	/*if (ADDENDIO_FREEMIUS_ENABLED) {
			if( addplus_fs()->is_pending_activation() ) {
				echo 'You haven\'t activated your account. Please check your email and click on the link we sent you...';
			}
			if( !addplus_fs()->is_registered() ) {
				echo 'You need to <a href="'.ADDPLUS_ADMIN_FOLDER.'options-general.php?page=addplus_addendio_settings">activate your account</a> to use Addendio Plus. <a href="'.ADDPLUS_ADMIN_FOLDER.'options-general.php?page=addplus_addendio_settings">ACTIVATE NOW!</a> It only takes a couple of clicks...';
			}
		}
		*/
	?>
	</div>
<?php		
	
}
