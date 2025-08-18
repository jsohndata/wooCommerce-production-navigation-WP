add_action('woocommerce_after_single_product_summary', 'custom_looped_product_navigation_with_exclusions', 15);

function custom_looped_product_navigation_with_exclusions() {
    global $post;

    // Customize this list with the product category IDs you want to exclude:
    $excluded_category_ids = array(12, 34, 56);

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    $all_products = get_posts($args);
    $filtered = [];

    foreach ($all_products as $pid) {
        $terms = get_the_terms($pid, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            $exclude = false;
            foreach ($terms as $term) {
                if (in_array($term->term_id, $excluded_category_ids)) {
                    $exclude = true;
                    break;
                }
            }
            if (!$exclude) {
                $filtered[] = $pid;
            }
        } else {
            $filtered[] = $pid;
        }
    }

    $current_id = $post->ID;
    $idx = array_search($current_id, $filtered);
    if ($idx === false) return;

    $prev_idx = ($idx === 0) ? count($filtered) - 1 : $idx - 1;
    $next_idx = ($idx === count($filtered) - 1) ? 0 : $idx + 1;

    $prev_id = $filtered[$prev_idx];
    $next_id = $filtered[$next_idx];

    echo '<div class="woocommerce-product-navigation" style="margin-top: 40px; display: flex; justify-content: space-between;">';

    echo '<form action="' . esc_url(get_permalink($prev_id)) . '" method="get">';
    echo '<button type="submit" class="button woocommerce-button">← Previous Product</button>';
    echo '</form>';

    echo '<form action="' . esc_url(get_permalink($next_id)) . '" method="get">';
    echo '<button type="submit" class="button woocommerce-button">Next Product →</button>';
    echo '</form>';

    echo '</div>';
}
