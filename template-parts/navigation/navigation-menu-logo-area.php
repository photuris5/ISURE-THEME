<div class="navigation-bar menu-logo-area  <?php materialis_header_main_class() ?>" <?php materialis_navigation_sticky_attrs() ?>>
    <div class="<?php echo esc_attr(materialis_navigation_wrapper_class("navigation-wrapper")) ?>">
        <div class="row basis-auto">
            <div class="main_menu_col col-xs-fit col-sm start-xs">
                <?php materialis_print_primary_menu(); ?>
            </div>
            <div class="logo_col col-xs col-sm-fit end-xs">
                <?php materialis_print_logo(); ?>
            </div>

            <div class="custom_area_col col-sm end-xs">
                <?php materialis_print_navigation_custom_area(); ?>
            </div>
        </div>
    </div>
</div>
