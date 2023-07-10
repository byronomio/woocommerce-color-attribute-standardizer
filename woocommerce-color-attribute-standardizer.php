<?php
/*
Plugin Name: WooCommerce Color Attribute Standardizer
Description: A WooCommerce plugin to standardize product attributes
Version: 1.0
Author: Byron Jacobs
Author URI: https://byronjacobs.co.za
License: GPLv2 or later
Text Domain: woocommerce-color-attribute-standardizer
*/

$limit = 5;

register_activation_hook(__FILE__, 'my_activation');
register_deactivation_hook(__FILE__, 'my_deactivation');

add_action('admin_menu', 'color_standardizer_menu');

function color_standardizer_menu()
{
    add_menu_page(
        'Color Attribute Standardizer',
        'Color Standardizer',
        'manage_options',
        'woocommerce-color-attribute-standardizer',
        'color_standardizer_page',
        'dashicons-admin-tools',
        200
    );
}

function my_activation()
{
    if (!wp_next_scheduled('my_daily_event')) {
        wp_schedule_event(time(), 'daily', 'my_daily_event');
    }
}

function my_deactivation()
{
    wp_clear_scheduled_hook('my_daily_event');
}

add_action('my_daily_event', 'do_this_daily');

function do_this_daily()
{
    global $limit;
    $products_processed = get_option('my_daily_products_processed', 0);
    start_standardization($products_processed, $limit);
}

add_action('wp_ajax_start_standardization', 'start_standardization');


function color_standardizer_page()
{
    global $limit;
?>
    <div class="wrap">
        <h1>Color Attribute Standardizer</h1>

        <p>Press the button below to standardize the color attributes for all products. This operation may take a while.</p>

        <button id="standardize_button" class="button button-primary">Start Standardization</button>

        <div id="standardize_status"></div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var start = 0;
            var limit = <?php echo $limit; ?>;

            $('#standardize_button').click(function() {
                $(this).prop('disabled', true);
                $('#standardize_status').html('Starting...');

                update_attributes(start, limit);
            });

            function update_attributes(start, limit) {
                $.post(ajaxurl, {
                    action: 'start_standardization',
                    start: start,
                    limit: limit
                }, function(response) {
                    $('#standardize_status').append('<p>' + response + '</p>');

                    if (response.indexOf('Done.') == -1) {
                        start += limit;
                        update_attributes(start, limit);
                    } else {
                        $('#standardize_button').prop('disabled', false);
                    }
                });
            }
        });
    </script>
<?php
}

add_action('wp_ajax_start_standardization', 'start_standardization');

function create_standard_attribute($name, $values)
{
    if (!taxonomy_exists($name)) {
        $args = array(
            'label' => ucwords(str_replace('pa_', '', $name)),
            'public' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'query_var' => true,
            'rewrite' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rest_base' => $name,
            'show_in_quick_edit' => true
        );
        register_taxonomy($name, 'product', $args);
    }

    $term_names = array();

    foreach ($values as $value) {
        $term = term_exists($value, $name);

        if ($term === 0 || $term === null) {
            $term = wp_insert_term($value, $name);

            if (!is_wp_error($term)) {
                $term_data = get_term($term['term_id'], $name);
                $term_names[] = ucwords($term_data->name);
            }
        } else {
            $term_data = get_term($term['term_id'], $name);
            $term_names[] = ucwords($term_data->name);
        }
    }

    $attribute = new WC_Product_Attribute();
    $attribute->set_id(wc_attribute_taxonomy_id_by_name($name));
    $attribute->set_name($name);
    $attribute->set_options($term_names);
    $attribute->set_position(0);
    $attribute->set_visible(true);
    $attribute->set_variation(false);

    return $attribute;
}


function start_standardization($start = 0)
{
    global $limit;
    $start = isset($_POST['start']) ? intval($_POST['start']) : $start;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : $limit;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $limit,
        'offset' => $start
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $colours = ['white', 'brown', 'black', 'red'];
        $box_colours = ['green', 'orange', 'purple'];

        while ($query->have_posts()) {
            $query->the_post();

            $product = wc_get_product(get_the_ID());
            $product_attributes = $product->get_attributes();

            echo "Product ID: " . get_the_ID() . "<br>";
            echo "Attributes: <br>";

            foreach ($product_attributes as $attribute) {
                $name = $attribute->get_name();
                $options = $attribute->get_options();

                $value_names = array_map(function ($term_id) {
                    $term = get_term($term_id);
                    return $term ? strtolower($term->name) : '';
                }, $options);

                echo $name . ": " . implode(", ", $value_names) . "<br>";

                if ($name === 'pa_colour') {
                    $found_colours = array();
                    foreach ($colours as $colour) {
                        foreach ($value_names as $value) {


                            if (strpos($value, $colour) !== false) {
                                $found_colours[] = $colour;
                            }
                        }
                    }

                    if (!empty($found_colours)) {
                        $standard_attr = create_standard_attribute('pa_custom_colour', $found_colours);
                        $product_attributes['pa_custom_colour'] = $standard_attr;
                    }
                }

                if ($name === 'pa_box_colour') {
                    $found_box_colours = array();
                    foreach ($box_colours as $colour) {
                        foreach ($value_names as $value) {
                            if (strpos($value, $colour) !== false) {
                                $found_box_colours[] = $colour;
                            }
                        }
                    }

                    if (!empty($found_box_colours)) {
                        $standard_attr = create_standard_attribute('pa_custom_box_colour', $found_box_colours);
                        $product_attributes['pa_custom_box_colour'] = $standard_attr;
                    }
                }
            }

            if (!empty($found_colours) || !empty($found_box_colours)) {
                $product->set_attributes($product_attributes);
                $product->save();
                echo 'Updated product ' . get_the_ID() . '<br>';
            }
        }

        echo 'Processed ' . ($start + $query->post_count) . ' products. <br> Processing next batch...';
    } else {
        echo 'Done. Processed ' . $start . ' products.';
    }

    update_option('my_daily_products_processed', $start + $query->post_count);

    if (!$query->have_posts()) {
        delete_option('my_daily_products_processed');
    }

    wp_die();
}
