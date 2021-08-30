<?php

namespace Yoast\WP\Test_Helper;

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', true );
}

class Query_Logger implements Integration {

    public function add_hooks() {
        add_action( 'shutdown', [ $this, 'store_queries' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_bar_menu', function( $admin_bar ) {
            $admin_bar->add_menu( array(
                'id'    => 'yoast-query-logger',
                'title' => 'YQM',
                'href'  => '#',
                'meta'  => array(
                    'title' => __('Yoast Query Monitor', 'yoast-test-helper'),            
                ),
            ));
        }, 100 );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'yoast-query-logger',
            \plugin_dir_url( \YOAST_TEST_HELPER_FILE ) . 'assets/js/dist/query-logger.js',
            [ "wp-element", "wp-polyfill", "wp-data", "yoast-seo-styled-components-package" ],
            \YOAST_TEST_HELPER_VERSION,
            true
        );
    }

    public function store_queries() {
        $this->create_table();
        global $wpdb;
        if( empty( $wpdb->queries ) ) {
            return;
        }
        $wpdb->query( $wpdb->prepare(
            '
            INSERT INTO wp_wpseo_query_log_requests(url)
            VALUES (%s);
            ',
            $_SERVER['REQUEST_URI'],
        ) );
        $request_id = $wpdb->get_var(
            '
            SELECT LAST_INSERT_ID();
            '
        );
        foreach( $wpdb->queries as $q ) {
            $wpdb->query( $wpdb->prepare(
                '
                INSERT INTO wp_wpseo_query_log_queries(request_id, query, time, trace)
                VALUES (%d, %s, %s, %s)
                ',
                $request_id,
                \trim( $q[ 0 ] ),
                $q[ 1 ],
                isset( $q[ 'yoast_stacktrace' ] ) ? $q[ 'yoast_stacktrace' ] : null
            ) );
        };
        $wpdb->query( $wpdb->prepare(
            '
            DELETE FROM wp_wpseo_query_log_requests
            WHERE id < %d
            '
        , $request_id - 50 ) );
    }

    private function create_table() {
        global $wpdb;
        $wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS `wp_wpseo_query_log_requests` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `url` longtext,
                PRIMARY KEY (`id`)
            );
            '
        );
        $wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS `wp_wpseo_query_log_queries` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `query` longtext,
                `trace` longtext,
                `time` double DEFAULT NULL,
                `request_id` int(11) unsigned,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`request_id`) REFERENCES wp_wpseo_query_log_requests(`id`) ON DELETE CASCADE
            );
            '
        );
    }
}
