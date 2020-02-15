a,
.comment-reply-link,
a.read-more{
color:<?php echo $color; ?>;
}

a:hover,
.comment-reply-link:hover,
.sidebar .widget > ul > li a:hover,
a.read-more:hover{
color:<?php echo $hoverColor; ?>;
}

.contact-form-wrapper input[type=submit],
.button,
.nav-links .numbers-navigation span.current, .post-comments .navigation .numbers-navigation span.current, .nav-links .numbers-navigation a:hover, .post-comments .navigation .numbers-navigation a:hover{
background-color:<?php echo $color; ?>;
border-color:<?php echo $color; ?>;
}

.contact-form-wrapper input[type=submit]:hover,
.nav-links .prev-navigation a:hover, .post-comments .navigation .prev-navigation a:hover, .nav-links .next-navigation a:hover, .post-comments .navigation .next-navigation a:hover,
button:hover, .button:hover{
background-color:<?php echo $hoverColor; ?>;
border-color:<?php echo $hoverColor; ?>;
}

/*
.post-comments,
.sidebar .widget,
.post-list .post-list-item{
border-bottom-color:<?php echo $color; ?>;
}
*/

.nav-links .prev-navigation a, .post-comments .navigation .prev-navigation a, .nav-links .next-navigation a, .post-comments .navigation .next-navigation a{
border-color:<?php echo $color; ?>;
color:<?php echo $color; ?>;
}

.tags-list a:hover{
border-color:<?php echo $color; ?>;
background-color:<?php echo $color; ?>;
}

svg.section-separator-top path.svg-white-bg,
svg.section-separator-bottom path.svg-white-bg{
/* */fill: <?php echo $color; ?>;
}

.sidebar .widget_about{
background-image: linear-gradient(to bottom, <?php echo $color; ?>, <?php materialis_print_color_style_brightness($color, 60, $as_color_template); ?> 100%)
}

.sidebar .widget .widgettitle i.widget-icon{
/* */background:<?php echo $color; ?>;
}

/* form style */

.mdc-text-field:not(.mdc-text-field--disabled):not(.mdc-text-field--outlined):not(.mdc-text-field--textarea) .mdc-text-field__input:hover {
border-bottom-color: <?php echo $color; ?>;
}

.mdc-text-field .mdc-line-ripple {
background-color: <?php echo $color; ?>;
}


.mdc-text-field:not(.mdc-text-field--disabled)+.mdc-text-field-helper-text {
color: <?php echo $color; ?>;
}

.mdc-text-field:not(.mdc-text-field--disabled):not(.mdc-text-field--textarea) {
border-bottom-color: <?php echo $color; ?>;
}

.mdc-text-field:not(.mdc-text-field--disabled) .mdc-text-field__icon {
color: <?php echo $color; ?>;
}


select:focus {
border-bottom-color: <?php echo $color; ?>;
}


textarea:not(.mdc-text-field--disabled) .mdc-text-field__input:focus,
.mdc-text-field--textarea:not(.mdc-text-field--disabled) .mdc-text-field__input:focus {
border-color: <?php echo $color; ?>;
}


.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-floating-label,
.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-text-field__input::-webkit-input-placeholder {
color: <?php echo $color; ?>;
}

.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-floating-label,
.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-text-field__input:-ms-input-placeholder {
color: <?php echo $color; ?>;
}

.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-floating-label,
.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-text-field__input::-ms-input-placeholder {
color: <?php echo $color; ?>;
}

.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-floating-label,
.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-text-field__input::placeholder {
color: <?php echo $color; ?>;
}

.mdc-text-field--textarea.mdc-text-field--focused:not(.mdc-text-field--disabled) {
border-color: <?php echo $color; ?>;
}

.mdc-text-field--textarea.mdc-text-field--focused:not(.mdc-text-field--disabled) .mdc-text-field__input:focus {
border-color: <?php echo $color; ?>;
}


.dark-text .mdc-text-field:not(.mdc-text-field--disabled):not(.mdc-text-field--outlined):not(.mdc-text-field--textarea) .mdc-text-field__input:hover {
border-bottom-color: <?php echo $color; ?>;
}

.dark-text .mdc-text-field .mdc-line-ripple {
background-color: <?php echo $color; ?>;
}


.dark-text textarea:not(.mdc-text-field--disabled) .mdc-text-field__input:focus,
.dark-text .mdc-text-field--textarea:not(.mdc-text-field--disabled) .mdc-text-field__input:focus {
border-color: <?php echo $color; ?>;
}


.sidebar .widget ul li a:hover:before{
/** **/background: <?php echo $color; ?>;
}
