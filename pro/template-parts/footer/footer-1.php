<div <?php echo materialis_footer_container('footer-1') ?>>
    <div <?php echo materialis_footer_background('footer-content') ?>>
        <div class="gridContainer">
            <div class="row middle-xs">
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="footer-logo">
                        <h4><?php materialis_print_logo(true); ?></h4>
                    </div>
                    <div class="muted"><?php echo materialis_get_footer_copyright(); ?></div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6 center-xs menu-column">
                    <?php materialis_footer_menu(); ?>
                </div>
                <?php materialis_print_area_social_icons('footer', 'content', 'end-sm col-sm-fit col-md-fit footer-social-icons', 5); ?>
            </div>
        </div>
    </div>
</div>
