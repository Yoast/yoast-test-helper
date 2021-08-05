<?php

namespace Yoast\WP\Test_Helper;

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', true );
}

class Query_Logger implements Integration {

    public function add_hooks() {
        add_action( 'shutdown', [ $this, 'store_queries' ] );
    }

    public function store_queries() {
        $this->create_table();
        global $wpdb;
        $max = $wpdb->get_var(
            '
            SELECT MAX(request)
            FROM qlqueries
            '
        );
        if ( $max === null ) {
            $max = 0;
        }
        foreach( $wpdb->queries as $q ) {
            $wpdb->query( $wpdb->prepare(
                '
                INSERT INTO wp_wpseo_query_log(request, query, time)
                VALUES (%d, %s, %s)
                ',
                $max + 1,
                \trim( $q[ 0 ] ),
                $q[ 1 ]
            ) );
        };
        $wpdb->query( $wpdb->prepare(
            '
            DELETE FROM qlqueries
            WHERE request < %d
            '
        , $max - 50 ) );
    }

    private function create_table() {
        global $wpdb;
        $wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS `wp_wpseo_query_log` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `query` longtext COLLATE utf8mb4_unicode_ci,
                `time` double DEFAULT NULL,
                `request` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=175809 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            '
        );
    }
}

