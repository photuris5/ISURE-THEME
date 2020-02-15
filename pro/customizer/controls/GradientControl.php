<?php

namespace Materialis;

class GradientControlPro extends \Kirki_Customize_Control
{
    public $type = 'gradient-control-pro';
    public $button_label = '';


    public function __construct($manager, $id, $args = array())
    {
        parent::__construct($manager, $id, $args);

        $this->button_label = __('Select Gradient', 'materialis');
    }


    public function enqueue()
    {
        wp_enqueue_script(materialis_get_text_domain() . '-gradient-control-pro', materialis_pro_uri("/customizer/assets/js") . "/gradient-control.js");
    }


    public function to_json()
    {
        parent::to_json();

        $gradient = $this->json['value'];
        if (is_string($this->json['value'])) {
            $gradient = json_decode($this->json['value'], true);
        }

        $this->json['button_label'] = $this->button_label;
        $this->json['gradient']     = materialis_get_gradient_value($gradient['colors'], $gradient['angle']);
        $this->json['angle']        = intval($gradient['angle']);
    }


    protected function content_template()
    {
        ?>
        <label for="{{ data.settings['default'] }}-button">
            <# if ( data.label ) { #>
            <span class="customize-control-title">{{ data.label }}</span>
            <# } #>
            <# if ( data.description ) { #>
            <span class="description customize-control-description">{{{ data.description }}}</span>
            <# } #>
        </label>

        <div class="webgradient-icon-container">
            <div class="webgradient-icon-preview">
                <div class="webgradient" style="background: {{data.gradient}}"></i>
                </div>
                <div class="webgradient-controls">
                    <button type="button" class="button upload-button control-focus" id="_customize-button-{{ data.id }}">{{{ data.button_label }}}</button>
                </div>
            </div>


            <div class="fic-icon-container" style="display: flex;flex-flow: wrap;">
                <input type="hidden" value="{{ data.value }}" name="_customize-input-{{ data.id }}" {{{ data.link }}}/>
                <div style="flex:1;">
                    <span class="customize-control-title">Colors</span>
                    <div class="colors">
                    </div>
                </div>
                <div style="flex:1;">
                    <span class="customize-control-title">Angle</span>
                    <input class="angle" value="{{data.angle}}" disabled="disabled">
                </div>
            </div>
        <?php

    }
}
