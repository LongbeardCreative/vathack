<?php 

Class CT_Code_Block extends CT_Component {

	var $shortcode_options;
	var $shortcode_atts;

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		// output code
		add_action( "wp_footer", array( $this, 'output_code' ), 100 );

		add_filter( 'template_include', array( $this, 'ct_code_block_single_template'), 100 );

		add_action("ct_toolbar_component_settings", array( $this, "code_editor_button") );

		// woocommerce specific
		if(isset($_REQUEST['action']) && stripslashes($_REQUEST['action']) == 'ct_exec_code') {
			// do not redirect shop page when its a builder preview
			add_action( 'wp', array( $this, 'ct_code_remove_template_redirect'));
		}
	}

	function ct_code_remove_template_redirect() {
		global $wp_filter;
		if(isset($wp_filter['template_redirect']['10']['wc_template_redirect'])) {
			unset($wp_filter['template_redirect']['10']['wc_template_redirect']);
			//echo "WooCommerce Shop page is essentially a redirect to Products Archive.";
		}

	}

	/**
	 * This function hijacks the template to return special template that renders the code results
	 * for the ct_code_block element to load the content into the builder for preview.
	 * 
	 * @since 0.4.0
	 * @author gagan goraya
	 */
	
	function ct_code_block_single_template( $template ) {

		$new_template = '';

		if(isset($_REQUEST['action']) && stripslashes($_REQUEST['action']) == 'ct_exec_code') {
			$nonce  	= $_REQUEST['nonce'];
			$post_id 	= $_REQUEST['post_id'];
			
			// check nonce
			if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
			    // This nonce is not valid.
			    die( 'Security check' );
			}
			
			if ( file_exists(dirname(dirname( __FILE__)) . '/layouts/' . 'code-block.php') ) {
				$new_template = dirname(dirname( __FILE__)) . '/layouts/' . 'code-block.php';
			}
		}

		if ( '' != $new_template ) {
				return $new_template ;
			}

		return $template;
	}


	/**
	 * Add a [ct_code_block] shortcode to WordPress
	 *
	 * @since 0.3.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );
		$id = $options['id'];
		
		// save to instance
		$this->shortcode_options[$id] = $options;

		// save to instance
		$this->shortcode_atts[$id] = $atts;

		// lets base64_decode all the code types, if they are not coming from the
		if(isset(json_decode($atts['ct_options'])->original)) {
			if(isset(json_decode($atts['ct_options'])->original->{'code-php'}) ) {
				$options['code_php'] = 	base64_decode($options['code_php']);
			}
		}

		//$code_php = htmlspecialchars_decode($options['code_php'], ENT_QUOTES);
		
		$code_php = $options['code_php'];

		ob_start();

		?><<?php echo esc_attr($options['tag']) ?> id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php 

		// make sure errors are shwon
		$error_reporting = error_reporting(E_ERROR | E_WARNING | E_PARSE);
		$display_errors = ini_get('display_errors');
		ini_set('display_errors', 1); 
		
		eval(' ?>'.$code_php.'<?php ');

		// set errors params back
		ini_set('display_errors', $display_errors); 
		error_reporting($error_reporting);

		?></<?php echo esc_attr($options['tag']) ?>><?php

		return ob_get_clean();
	}


	/**
	 * Echo custom JS/CSS code added by user
	 *
	 * @since 0.3.1
	 */

	function output_code() {
		
		if ( is_array( $this->shortcode_options ) ) {

			foreach ( $this->shortcode_options as $component_id => $options ) {
				
				$component_id = sanitize_text_field($component_id);
				
				$selector 	= $options['selector'];

				$atts = $this->shortcode_atts[$component_id];
				
				// lets base64_decode all the code types, if they are not coming from the default
				if(isset(json_decode($atts['ct_options'])->original)) {
					if(isset(json_decode($atts['ct_options'])->original->{'code-js'}) ) {
						$options['code_js'] = 	base64_decode($options['code_js']);
					}
					if(isset(json_decode($atts['ct_options'])->original->{'code-css'}) ) {
						$options['code_css'] = 	base64_decode($options['code_css']);
					}
				}

				$code_js 	= $options['code_js'];
				$code_js 	= str_replace("%%ELEMENT_ID%%", $selector, $code_js);

				echo "<script type=\"text/javascript\" id=\"ct_code_block_js_{$component_id}\">";
					echo $code_js;
				echo "</script>\r\n";

				$code_css 	= $options['code_css'];
				$code_css 	= str_replace("%%ELEMENT_ID%%", $selector, $code_css);

				echo "<style type=\"text/css\" id=\"ct_code_block_css_{$component_id}\">";
					echo $code_css;
				echo "</style>\r\n";
			}
		}
	}


	/**
	 * Code editor toolbar button
	 *
	 * @since 1.3
	 */

	function code_editor_button() { 
			// The ng-show has been moved up into the div.ct-toolitem class, in order to supress doubline line
			// in the toolbar in safari
		?>
		<div class="ct-toolitem" ng-show="isActiveName('<?php echo $this->options['tag']; ?>')">
			<div class="ct-tab ct-code-editor-button" title="<?php _e( 'Code Editor', 'component-theme' ); ?>"
				
				ng-click="switchActionTab('codeEditor');"
				ng-class="{'ct-tooltab-closed ct-button-outlined' : !isActiveActionTab('codeEditor')}">
					<?php _e( 'Code Editor', 'component-theme' ); ?> 
					<span class="ct-icon ct-elipsis-icon"></span>
			</div>
		</div>

	<?php }

}


// Create instance
$html = new CT_Code_Block( array( 
			'name' 		=> 'Code Block',
			'tag' 		=> 'ct_code_block',
			'params' 	=> array(
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "code-php",
						"value" 		=> "<span class=\"code-block-date\">Today is <?php echo date(\"F j, Y\"); ?></span>",
						"hidden"		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "code-js",
						"value" 		=> "/* %%ELEMENT_ID%% will be replaced with the element's ID (without #). */",
						"hidden"		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "code-css",
						"value" 		=> "/* %%ELEMENT_ID%% will be replaced with the element's ID (without #). */",
						"hidden"		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "tag",
						"heading" 		=> __("Tag"),
						"param_name" 	=> "tag",
						"value" 		=> array (
											"div" => "DIV",
											"p" => "P",
											"h1" => "H1",
											"h2" => "H2",
											"h3" => "H3",
											"h4" => "H4",
											"h5" => "H5",
											"h6" => "H6",
										),
						"css" 			=> false,
					)
				)
			)
		);