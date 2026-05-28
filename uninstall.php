<?php
/**
 * Uninstall script for Bible by Midvash.
 *
 * Runs when the user clicks “Delete” on the plugins screen (NOT on deactivate).
 * Removes everything we own: the bbm_options row and every transient we cached
 * (verse bodies, version catalogs, votd lookups, AJAX rate-limit counters).
 *
 * Hardcoded prefixes (bbm_, bbm_votd_, bbm_versions_, bbm_rl_) are intentionally
 * spelled out so future renames stay caught here.
 *
 * @package Bible_by_Midvash
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Single-site
if (!is_multisite()) {
    bbm_uninstall_cleanup();
    return;
}

// Multisite: clean every blog
global $wpdb;
$blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
if (!is_array($blog_ids)) {
    return;
}
foreach ($blog_ids as $blog_id) {
    switch_to_blog((int) $blog_id);
    bbm_uninstall_cleanup();
    restore_current_blog();
}

/**
 * Wipes plugin state on the current blog.
 */
function bbm_uninstall_cleanup()
{
    global $wpdb;

    delete_option('bbm_options');

    // Direct DELETE is the only reasonable way to nuke transients by prefix —
    // there's no bulk WP API for it. We avoid object-cache calls because on
    // uninstall the object cache is about to be irrelevant anyway, and the
    // canonical source (options table) is what wp.org reviewers verify.
    // phpcs:disable WordPress.DB.DirectDatabaseQuery
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_bbm\_%' ESCAPE '\\\\'"
    );
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_bbm\_%' ESCAPE '\\\\'"
    );
    // phpcs:enable WordPress.DB.DirectDatabaseQuery
}
