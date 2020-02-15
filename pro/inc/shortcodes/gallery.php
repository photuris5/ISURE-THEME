<?php

add_shortcode('materialis_gallery', 'materialis_gallery_shortcode');


$materialis_gallery_index = 0;


function materialis_gallery_masonry_script($atts)
{
    $script             = "";
    $isShortcodeRefresh = apply_filters('materialis_is_shortcode_refresh', false);
    if ($atts['masonry'] == 1) {
        ob_start();
        ?>
        <script>
            <?php materialis_print_contextual_jQuery(); ?>(function ($) {
                var masonryGallery = $("[data-unique-id='<?php echo $atts['uniqId']; ?>']"),
                    debounceRefresh = $.debounce(function () {
                        masonryGallery.data().masonry.layout();
                    }, 500);

                masonryGallery.masonry({
                    itemSelector: '.gallery-item',
                    percentPosition: true,
                });

                var images = masonryGallery.find('img');
                var loadedImages = 0;

                function imageLoaded() {
                    loadedImages++;
                    if (images.length === loadedImages) {
                        masonryGallery.data().masonry.layout();

                    }
                }

                images.each(function () {
                    $(this).on('load', imageLoaded);
                    debounceRefresh();
                });

                $(window).on('load', function () {
                    masonryGallery.data().masonry.layout();
                });

            });
        </script>
        <?php
        $script = ob_get_clean();
        $script = strip_tags($script);

    } else {
        if ($isShortcodeRefresh) {
            ob_start();
            ?>
            jQuery(function ($) {
            var masonryGallery = $("[data-unique-id='<?php echo $atts['uniqId']; ?>']");
            try {
            masonryGallery.masonry('destroy');
            } catch (e) {

            }
            });
            <?php
            $script = ob_get_clean();

        }
    }

    return $script;
}

function materialis_gallery_style($data)
{
    return "<div id='{{MATERIALIS_GALLERY_ID}}' class='{{MATERIALIS_GALLERY_CLASS}}' data-unique-id='{{MATERIALIS_DATA_UNIQUE_ID}}'>";
}

function materialis_gallery_shortcode($atts)
{
    global $materialis_gallery_index;
    $atts = shortcode_atts(
        array(
            'id'      => 'ope-gallery-' . (++$materialis_gallery_index),
            'columns' => '4',
            'columns_tablet' => '4',
            'ids'     => '',
            'link'    => 'file',
            'lb'      => '1',
            'orderby' => '',
            'skin'    => 'skin01',
            'masonry' => '1',
            'size'    => 'medium',
            'caption' => 'no',
        ),
        $atts
    );


    $galleryClass = $atts['id'] . "-dls-wrapper gallery-items-wrapper materialis-gallery";
    $uniqId       = uniqid($atts['id'] . "-");

    if (materialis_to_bool($atts['caption'])) {
        $galleryClass .= " captions-enabled";
    }


    $atts['uniqId'] = $uniqId;

    if (empty($atts['ids'])) {

        $colors = array('03A9F4', '4CAF50', 'FBC02D', '9C27B0');

        ob_start();

        $imagesColors = array();

        ?>
        <div class="<?php echo $galleryClass; ?>" data-unique-id="<?php echo $uniqId; ?>">
            <?php for ($img = 0; $img < ($atts['columns'] * 2); $img++): ?>
                <dl class="gallery-item">
                    <dt class="gallery-icon landscape">
                        <?php
                        $imgIndex = $img % 8 + 1;
                        $prefix   = ($atts['masonry'] == 1) ? 'masonry-' : '';
                        $imgURL   = "//extendthemes.com/assets/general/gallery/{$prefix}{$imgIndex}.jpg";
                        ?>
                        <a <?php echo($atts['lb'] == '1' ? "data-fancybox='{$atts['id']}-group'" : "") ?> href="<?php echo $imgURL ?>">
                            <img src="<?php echo $imgURL ?>" class="<?php echo $atts['id'] ?>-image" alt=""></a>
                    </dt>

                    <?php if (materialis_to_bool($atts['caption'])): ?>
                        <dd class="wp-caption-text gallery-caption"><?php _e('Gallery demo image', 'materialis'); ?></dd>
                    <?php endif; ?>
                </dl>
            <?php endfor; ?>
        </div>
        <?php

        $gallery = ob_get_clean();
    } else {

        // make sure the gallery_shortcode function will return the default gallery
        // fixes japck issue
        add_filter('post_gallery', '__return_empty_string', PHP_INT_MAX);
        add_filter('use_default_gallery_style', '__return_false');
        add_filter('gallery_style', 'materialis_gallery_style', PHP_INT_MAX);


        $gallery = gallery_shortcode($atts);

        remove_filter('post_gallery', '__return_empty_string', PHP_INT_MAX); // remove the previous filter
        remove_filter('use_default_gallery_style', '__return_false');
        remove_filter('gallery_style', 'materialis_gallery_style', PHP_INT_MAX);

        $gallery = preg_replace("/<br(.*?)>/is", "", $gallery);
        $gallery = str_replace("{{MATERIALIS_GALLERY_ID}}", $atts['id'], $gallery);
        $gallery = str_replace("{{MATERIALIS_GALLERY_CLASS}}", $galleryClass, $gallery);
        $gallery = str_replace("{{MATERIALIS_DATA_UNIQUE_ID}}", $uniqId, $gallery);
        $gallery = preg_replace("/<img(.*)class=\"(.*?)\"/", "<img $1 class='" . $atts['id'] . "-image'", $gallery);


        if ( ! materialis_to_bool($atts['caption'])) {
            $gallery = $gallery . '<style>' . "[data-unique-id='{$atts['uniqId']}']" . ' .wp-caption-text.gallery-caption{display:none;}</style>';
        } else {
            $gallery = $gallery . "<script>(function($){window.MaterialisCaptionsGallery && window.MaterialisCaptionsGallery($('[data-unique-id=\"{$uniqId}\"]'))})(jQuery)</script>";
        }
    }


    $itemWidth = 100 / $atts['columns'];
    $itemTabletWidth = 100 / $atts['columns_tablet'];
    $style     = "" .
                 "<style type=\"text/css\">
                    @media only screen and (min-width: 768px) and (max-width: 1023px) {
                    [data-unique-id='{$atts['uniqId']}'] dl {
                        float: left;
                        width: {$itemTabletWidth}% !important;
                        max-width:  {$itemTabletWidth}% !important;
                        min-width:  {$itemTabletWidth}% !important;
                    }

                    [data-unique-id='{$atts['uniqId']}'] dl:nth-of-type({$atts['columns_tablet']}n +1 ) {
                      clear: both;
                      }
                    }
                    @media only screen and (min-width: 1024px) {
                    [data-unique-id='{$atts['uniqId']}'] dl {
                        float: left;
                        width: {$itemWidth}% !important;
                        max-width:  {$itemWidth}% !important;
                        min-width:  {$itemWidth}% !important;
                    }

                    [data-unique-id='{$atts['uniqId']}'] dl:nth-of-type({$atts['columns']}n +1 ) {
                      clear: both;
                      }
                    }
                </style>";

    $gallery = $style . $gallery;

    if ($atts['lb'] == 1) {
        $gallery = preg_replace('/<a/', '<a data-fancybox="' . $atts['id'] . '-group"', $gallery);
    }

    $masonry = materialis_gallery_masonry_script($atts);

    $isShortcodeRefresh = apply_filters('materialis_is_shortcode_refresh', false);
    if ( ! $isShortcodeRefresh) {
        wp_add_inline_script('masonry', $masonry);
    } else {
        $gallery .= "<script>{$masonry}</script>";
    }


    return "<div id='{$atts['id']}' class='gallery-wrapper'>{$gallery}</div>";

}
