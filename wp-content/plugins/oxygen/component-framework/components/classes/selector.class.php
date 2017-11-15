<?php 

Class CT_Selector extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );

		// remove component button
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
	}
}


// Create inctance
$selector = new CT_Selector( array( 
			'name' 		=> 'Selector',
			'tag' 		=> 'ct_selector'
			)
		);