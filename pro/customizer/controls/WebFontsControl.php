<?php

namespace Materialis;

class WebFontsControl extends \Kirki_Customize_Control
{
    public $type = "web-fonts";

    public function __construct($manager, $id, $args = array())
    {
        $this->button_label = __('Change Font', 'materialis');
        parent::__construct($manager, $id, $args);
    }

    public function enqueue()
    {
        $jsRoot     = materialis_pro_uri("/customizer/assets/js");
        $theme      = wp_get_theme();
        $textDomain = $theme->get('TextDomain');

        wp_enqueue_script('webfonts-control', $jsRoot . "/webfonts-control.js", array('customizer-base'));

        wp_enqueue_script($textDomain . '-webfonts', $jsRoot . '/web-fonts.js', array('customizer-base'), false, true);
        wp_localize_script($textDomain . '-webfonts', 'cpWebFonts', array(
            'url' => $jsRoot . "/web-f/index.html",
        ));
    }


    public function to_json()
    {
        parent::to_json();
        $this->json['button_label'] = $this->button_label;

        $values = $this->value() == '' ? $this->default() : $this->value();

        if (is_string($values)) {
            $values = json_decode($values);
        }

        $this->json['fonts'] = $values;
    }

    protected function content_template()
    {
        ?>
        <# if ( data.tooltip ) { #>
        <a href="#" class="tooltip hint--left" data-hint="{{ data.tooltip }}"><span class='dashicons dashicons-info'></span></a>
        <# } #>
        <label>
            <# if ( data.label ) { #>
                <span class="customize-control-title">{{{ data.label }}}</span>
                <# } #>
                    <# if ( data.description ) { #>
                        <span class="description customize-control-description">{{{ data.description }}}</span>
                        <# } #>
        </label>

        <div class="web-fonts-container">
            <div class="background-setting">
                <div class="viewer">
                    <div class="selector">
                        <iframe id="cp-webfonts-list-iframe" style="width: 100%;"></iframe>
                    </div>
                </div>
            </div>
        </div>


        <div id="webfonts-popup-template" style="display:none">
            <iframe id="cp-webfonts-iframe" style="width: 100%;height: 500px;border:0px;"></iframe>
            <div id="cp-items-footer">
                <button type="button" class="button button-large" id="cp-item-cancel"><?php _e('Cancel', 'materialis'); ?></button>
                <button type="button" class="button button-large button-primary" id="cp-item-ok"><?php _e('Apply Changes', 'materialis'); ?></button>
            </div>
        </div>

        <?php
    }
}
