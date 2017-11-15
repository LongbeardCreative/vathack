jQuery(document).ready(function($) {
	
	// Init tabs to show
	var checked = $(".ct-template-anchor:checked").val();
	$("#ct_"+checked).addClass("ct-section-active");

	var checked = $(".ct-template-options-anchor:checked").val();
	$("#ct_"+checked).addClass("ct-section-active");

	// Switch template tabs on radio button click
	$(".ct-template-anchor").change( function(){
		
		var tab = $(this).val();

		$(".ct-template-section").removeClass("ct-section-active");
		$("#ct_"+tab).addClass("ct-section-active");
	});

	// Switch template options tabs on radio button click
	$(".ct-template-options-anchor").change( function(){
		
		var tab = $(this).val();

		$(".ct-template-options-section").removeClass("ct-section-active");
		$("#ct_"+tab).addClass("ct-section-active");
	});

	// Init taxonomies
	function switchTaxonomies() {
		var checked = $("#ct_use_template_taxonomies:checked").val();
		if ( checked ) {
			$(".ct-template-taxonomies").show("fast");
		}
		else {
			$(".ct-template-taxonomies").hide();
		}
	}
	switchTaxonomies();
	
	$("#ct_use_template_taxonomies").click( function(){
		switchTaxonomies();
	});


	// add taxonomies
	$(".ct-template-taxonomies").on("click", ".ct-add-taxonomy", function(){

		var placeholder = $("#ct-template-taxonomy-placeholder").html();

		$(".ct-template-taxonomies").append(placeholder);
	});

	// remove taxonomy
	$(".ct-template-taxonomies").on("click", ".ct-remove-taxonomy", function() {
		
		var taxonomy = $(this).parent(".ct-template-taxonomy");

		$(taxonomy).remove();
	});

	/**
	 * Show/hide builder shortcdes in Oxygen metabox for CPTs
	 */
	$("#ct-toggle-shortcodes").click(function() {
		$("#ct-builder-shortcodes").slideToggle("fast");
		$(this).toggleClass("ct-toggle-shortcodes-show");
	});
	

	$('a#ct_create_custom_view_from').on('click', function(e) {
		e.preventDefault();
		var form = $(this).closest('form');
		form.append('<input type="hidden" name="ct_custom_view_on_create_copy" value="true" />');
		form.submit();
	//	$('input#ct_custom_view_on_create_copy').trigger('click');
	})

	$('a#ct_edit_inner_content').on('click', function(e) {
		e.preventDefault();
		var form = $(this).closest('form');
		form.append('<input type="hidden" name="ct_redirect_inner_content" value="true" />');
		form.submit();
	//	$('input#ct_custom_view_on_create_copy').trigger('click');
	})

	$('a#ct_delete_custom_view').on('click', function() {
		$('#ct_builder_shortcodes').val('');
		alert('You must save for changes to take effect. If you do not wish to delete, simply leave the page without saving.');
		/*var form = $(this).closest('form');
		form.append('<input type="hidden" name="ct_delete_custom_view" value="true" />');
		form.submit();*/
	});

	$("input.ct_render_post_using").on('change', function() {
		if($(this).val() === 'other_template') {
			$(".ct_template_option_panel:eq(0)").hide("fast");	
			$(".ct_template_option_panel:eq(1)").show("fast");
		} else {
			$(".ct_template_option_panel:eq(1)").hide("fast");	
			$(".ct_template_option_panel:eq(0)").show("fast");
		}
	});

	$("input.ct_use_inner_content").on('change', function() {
		if($(this).val() === 'layout') {
			$(".ct-user-inner-content-layout").css("display", "inline-block");
		} else {
			$(".ct-user-inner-content-layout").css("display", "none");	
		}
	});
});