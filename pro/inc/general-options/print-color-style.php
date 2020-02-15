<?php foreach ($textElements as $element): ?>
    <?php echo "{$element}{$colorClass}" ?>{
    color : <?php echo $color; ?>;
    }
<?php endforeach; ?>

.card.bg-<?php echo $colorName; ?>,
.bg-<?php echo $colorName; ?>{
/* */background-color:<?php echo $color ?>;
}

a<?php echo $colorClass; ?>:not(.button){
/* */color:<?php echo $color; ?>;
}

a<?php echo $colorClass; ?>:not(.button):hover{
/* */color:<?php echo $hoverColor; ?>;
}

button<?php echo $colorClass; ?>,
.button<?php echo $colorClass; ?>{
/* */background-color:<?php echo $color; ?>;
/* */border-color:<?php echo $color; ?>;
}

button<?php echo $colorClass; ?>:hover,
.button<?php echo $colorClass; ?>:hover{
/* */background-color:<?php echo $hoverColor; ?>;
/* */border-color:<?php echo $hoverColor; ?>;
}

button.outline<?php echo $colorClass; ?>:not(:disabled),
.button.outline<?php echo $colorClass; ?>:not(:disabled){
/* */background:none;
/* */border:2px solid <?php echo $color; ?>;
/* */color:<?php echo $color; ?>;
}

button.outline<?php echo $colorClass; ?>:not(:disabled):hover,
.button.outline<?php echo $colorClass; ?>:not(:disabled):hover{
/* */background:none;
/* */border-color:<?php echo materialis_print_color_style_rgba($color, 0.7, $as_color_template); ?>;
/* */color:<?php echo materialis_print_color_style_rgba($color, 0.9, $as_color_template); ?>;
}

button<?php echo $colorClass; ?>.button.link,
.button<?php echo $colorClass; ?>.link{
/* */color:<?php echo $color; ?>;
/* */padding: 0 8px;
/* */background:none;
}

button<?php echo $colorClass; ?>.link::before,
button<?php echo $colorClass; ?>.link::after,
.button<?php echo $colorClass; ?>.link::before,
.button<?php echo $colorClass; ?>.link::after {
/* */background-color:<?php echo $color; ?>;
}


i.mdi<?php echo $colorClass ?>{
/* */color:<?php echo $color; ?>;
}


i.mdi.icon.bordered<?php echo $colorClass ?>{
/* */border-color:<?php echo $color; ?>;
}

i.mdi.icon.reverse<?php echo $colorClass ?>{
/* */background-color:<?php echo $color; ?>;
/* */color: #ffffff;
}

i.mdi.icon.reverse.color-white<?php echo $colorClass ?>{
/* */color: #d5d5d5;
}

i.mdi.icon.bordered<?php echo $colorClass ?>{
/* */border-color:<?php echo $color; ?>;
}

i.mdi.icon.reverse.bordered<?php echo $colorClass ?>{
/* */background-color:<?php echo $color; ?>;
/* */color: #ffffff;
}

.top-right-triangle<?php echo $colorClass ?>{
/* */border-right-color:<?php echo $color; ?>;
}
.checked.decoration-<?php echo $colorName; ?> li:before {
/* */color:<?php echo $color; ?>;
}

.stared.decoration-<?php echo $colorName; ?> li:before {
/* */color:<?php echo $color; ?>;
}

.card.card-<?php echo $colorName; ?>{
/* */background-color:<?php echo $color; ?>;
}


.card.bottom-border-<?php echo $colorName; ?>{
/* */border-bottom-color: <?php echo $color; ?>;
}

.grad-180-transparent-<?php echo $colorName; ?>{
/* */ background-image: linear-gradient(180deg, <?php echo materialis_print_color_style_rgba($color, 0, $as_color_template); ?> 0%, <?php echo materialis_print_color_style_rgba($color, 0, $as_color_template); ?> 50%, <?php echo materialis_print_color_style_rgba($color, 0.6, $as_color_template); ?> 78%, <?php echo materialis_print_color_style_rgba($color, 0.9, $as_color_template); ?> 100%) !important;
}

.circle-counter<?php echo $colorClass ?> .circle-bar{
/* */ stroke: <?php echo $color; ?>;
}

.border-<?php echo $colorName; ?>{
/* */border-color: <?php echo $color; ?>;
}

.border-top-<?php echo $colorName; ?>{
/* */border-top-color: <?php echo $color; ?>;
}

.circle-counter.<?php echo $colorName; ?> .circle-bar{
/* */stroke: <?php echo $color; ?>;
}
