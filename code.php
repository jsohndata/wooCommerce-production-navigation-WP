<?php
/**
 * WooCommerce: Looped prev/next product navigation with excluded categories
 * - Renders Prev/Next buttons on single product pages.
 * - Skips products in configured excluded categories.
 * - Wrap-around from last->first and first->last.
 * - Caches product ID list via transient for speed.
 */

if (! defined('ABSPATH')) {
  exit;
}

/**
 * ── Config ────────────────────────────────────────────────────────────────
 * Replace with your real category IDs (term_id from product_cat).
 */
$wcloop_cfg = [
  'excluded_category_ids' => [10000], // e.g., [12, 34, 56]
  'cache_key'             => 'wcloop_product_ids_excluding_terms',
  'cache_ttl'             => HOUR_IN_SECONDS, // adjust if you add/remove products often
];

/**
 * Build (and cache) the ordered list of product IDs excluding certain product_cat terms.
 * Order by menu_order then title for predictable nav.
 */
$wcloop_get_product_ids = static function (array $excluded_term_ids) use ($wcloop_cfg): array {
  $cached = get_transient($wcloop_cfg['cache_key']);
  if (is_array($cached)) {
    return $cached;
  }

  // Query all published products NOT in excluded categories
  $q = new WP_Query([
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'fields'              => 'ids',
    'posts_per_page'      => -1,
    'orderby'             => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'tax_query'           => $excluded_term_ids ? [[
      'taxonomy' => 'product_cat',
      'field'    => 'term_id',
      'terms'    => array_map('intval', $excluded_term_ids),
      'operator' => 'NOT IN',
    ]] : [],
    'no_found_rows'       => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
  ]);

  $ids = $q->posts ?: [];

  // Cache to avoid repeating the full scan on every page load
  set_transient($wcloop_cfg['cache_key'], $ids, (int) $wcloop_cfg['cache_ttl']);
  return $ids;
};

/**
 * Bust cache when products or product terms change.
 */
$wcloop_flush_cache = static function () use ($wcloop_cfg): void {
  delete_transient($wcloop_cfg['cache_key']);
};
add_action('save_post_product', $wcloop_flush_cache, 10, 0);
add_action('edited_product_cat', $wcloop_flush_cache, 10, 0);
add_action('created_product_cat', $wcloop_flush_cache, 10, 0);
add_action('delete_product_cat', $wcloop_flush_cache, 10, 0);

/**
 * Render navigation.
 */
add_action('woocommerce_after_single_product_summary', static function (): void {
  if (! is_product()) {
    return;
  }

  // Pull config inside closure:
  $cfg = [
    'excluded_category_ids' => [10000],
    'cache_key'             => 'wcloop_product_ids_excluding_terms',
    'cache_ttl'             => HOUR_IN_SECONDS,
  ];

  // Same helper as above (duplicated inside closure for WPCode scoping)
  $get_ids = static function (array $excluded_term_ids) use ($cfg): array {
    $cached = get_transient($cfg['cache_key']);
    if (is_array($cached)) {
      return $cached;
    }
    $q = new WP_Query([
      'post_type'           => 'product',
      'post_status'         => 'publish',
      'fields'              => 'ids',
      'posts_per_page'      => -1,
      'orderby'             => ['menu_order' => 'ASC', 'title' => 'ASC'],
      'tax_query'           => $excluded_term_ids ? [[
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => array_map('intval', $excluded_term_ids),
        'operator' => 'NOT IN',
      ]] : [],
      'no_found_rows'       => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    ]);
    $ids = $q->posts ?: [];
    set_transient($cfg['cache_key'], $ids, (int) $cfg['cache_ttl']);
    return $ids;
  };

  $current_id = get_the_ID();
  if (! $current_id) {
    return;
  }

  $ids = $get_ids($cfg['excluded_category_ids']);
  if (empty($ids)) {
    return;
  }

  $idx = array_search($current_id, $ids, true);
  if ($idx === false) {
    // Current product itself is excluded or not found in the ordered list.
    return;
  }

  $count = count($ids);
  if ($count < 2) {
    return; // nothing to navigate
  }

  $prev_id = $ids[$idx === 0 ? $count - 1 : $idx - 1];
  $next_id = $ids[$idx === $count - 1 ? 0 : $idx + 1];

  $prev_url = $prev_id ? get_permalink($prev_id) : '';
  $next_url = $next_id ? get_permalink($next_id) : '';

  if (! $prev_url && ! $next_url) {
    return;
  }

  // Minimal, theme-agnostic styling; Woo buttons classes included
  echo '<div class="woocommerce-product-navigation" style="margin:24px 0; display:flex; justify-content:space-between; gap:12px;">';

  if ($prev_url) {
    echo '<a class="button woocommerce-button" href="' . esc_url($prev_url) . '" rel="prev" aria-label="' . esc_attr__('Previous product', 'your-textdomain') . '">← ' . esc_html__('Prev', 'your-textdomain') . '</a>';
  } else {
    echo '<span></span>';
  }

  if ($next_url) {
    echo '<a class="button woocommerce-button" href="' . esc_url($next_url) . '" rel="next" aria-label="' . esc_attr__('Next product', 'your-textdomain') . '">' . esc_html__('Next', 'your-textdomain') . ' →</a>';
  }

  echo '</div>';
}, 15);
