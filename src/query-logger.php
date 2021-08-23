<?php

namespace Yoast\WP\Test_Helper;

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', true );
}

class Query_Logger implements Integration {

    public function add_hooks() {
        add_action( 'shutdown', [ $this, 'store_queries' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'yoast-query-logger',
            \plugin_dir_url( \YOAST_TEST_HELPER_FILE ) . 'assets/js/dist/query-logger.js',
            [ "wp-element", "wp-polyfill" ],
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
                INSERT INTO wp_wpseo_query_log_queries(request_id, query, time)
                VALUES (%d, %s, %s)
                ',
                $request_id,
                \trim( $q[ 0 ] ),
                $q[ 1 ]
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
                `url` longtext COLLATE utf8mb4_unicode_ci,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=175809 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            '
        );
        $wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS `wp_wpseo_query_log_queries` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `query` longtext COLLATE utf8mb4_unicode_ci,
                `time` double DEFAULT NULL,
                `request_id` int(11) unsigned,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`request_id`) REFERENCES wp_wpseo_query_log_requests(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=175809 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            '
        );
    }
}

