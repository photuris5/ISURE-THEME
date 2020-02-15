<div class="navigation-bar logo-inside-menu  <?php materialis_header_main_class() ?>" <?php materialis_navigation_sticky_attrs() ?>>
    <div class="<?php echo esc_attr(materialis_navigation_wrapper_class("navigation-wrapper")) ?>">
        <div class="row basis-auto">
	        <div class="logo_col col-xs col-sm-fit">
	            <?php materialis_print_logo(); ?>
	        </div>
	        <div class="main_menu_col col-xs-fit col-sm">
	            <?php materialis_print_primary_menu(new Materialis_Logo_Nav_Menu(), 'materialis_no_menu_logo_inside_cb'); ?>
	        </div>
	    </div>
    </div>
</div>
