<?php

class Yoast_DB extends \wpdb {
    public $qm_php_vars = array(
        'max_execution_time'  => null,
        'memory_limit'        => null,
        'upload_max_filesize' => null,
        'post_max_size'       => null,
        'display_errors'      => null,
        'log_errors'          => null,
    );

    /**
     * Class constructor
     */
    public function __construct( $dbuser, $dbpassword, $dbname, $dbhost ) {
        foreach ( $this->qm_php_vars as $setting => &$val ) {
            $val = ini_get( $setting );
        }

        parent::__construct( $dbuser, $dbpassword, $dbname, $dbhost );

        // Initialize the WordPress tables names.
        global $wpdb;
        $this->set_prefix( $wpdb->prefix );
    }

    /**
     * Performs a MySQL database query, using current database connection.
     *
     * @see wpdb::query()
     *
     * @param string $query Database query
     * @return int|bool Boolean true for CREATE, ALTER, TRUNCATE and DROP queries. Number of rows
     *                  affected/selected for all other queries. Boolean false on error.
     */
    public function query( $query ) {
        if ( ! $this->ready ) {
            if ( isset( $this->check_current_query ) ) {
                // This property was introduced in WP 4.2
                $this->check_current_query = true;
            }
            return false;
        }

        if ( $this->show_errors ) {
            $this->hide_errors();
        }

        $result = parent::query( $query );
        $i      = $this->num_queries - 1;

        if ( ! isset( $this->queries[ $i ] ) ) {
            return $result;
        }

        $this->queries[ $i ]['yoast_stacktrace'] = $this->generate_stacktrace();

        if ( ! isset( $this->queries[ $i ][3] ) ) {
            $this->queries[ $i ][3] = $this->time_start;
        }

        if ( $this->last_error ) {
            $code = 'qmdb';
            if ( $this->use_mysqli ) {
                if ( $this->dbh instanceof \mysqli ) {
                    $code = mysqli_errno( $this->dbh );
                }
            } else {
                if ( is_resource( $this->dbh ) ) {
                    // Please do not report this code as a PHP 7 incompatibility. Observe the surrounding logic.
                    // phpcs:ignore
                    $code = \mysql_errno( $this->dbh );
                }
            }
            $this->queries[ $i ]['result'] = new \WP_Error( $code, $this->last_error );
        } else {
            $this->queries[ $i ]['result'] = $result;
        }

        return $result;
    }

    private function generate_stacktrace() {
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());
        array_shift($trace); // remove call to this method
        array_shift($trace); // remove call to Yoast_DB::query
        array_pop($trace); // remove {main}
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++)
        {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return "\t" . implode("\n\t", $result);
    }
}

$wpdb = new Yoast_DB( \DB_USER, \DB_PASSWORD, \DB_NAME, \DB_HOST );
