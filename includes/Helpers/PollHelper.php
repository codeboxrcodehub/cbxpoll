<?php
/**
 * The helper functionality of the plugin.
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 */

namespace Cbx\Poll\Helpers;

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

use Cbx\Poll\PollSettings;


/**
 * Helper functionality of the plugin.
 *
 * lots of micro methods that help get set
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */
class PollHelper
{
    /**
     * Initialize poll tracking cookie.
     *
     * - Logged-in users: user-{id} (hashed for privacy).
     * - Guests: guest-{random}.
     * - Sets cookie if not present or invalid.
     *
     * @return void
     */
    public static function init_cookie()
    {
        if (is_admin()) {
            return;
        }

        if (is_user_logged_in()) {
            $user_id      = get_current_user_id();
            $cookie_value = 'user-'.wp_hash($user_id); // Avoid exposing raw user IDs.
        } else {
            $cookie_value = 'guest-'.wp_rand(CBXPOLL_RAND_MIN, CBXPOLL_RAND_MAX);
        }

        $cookie_name = CBXPOLL_COOKIE_NAME;

        // Always unslash before sanitizing superglobals.
        $current_cookie = isset($_COOKIE[$cookie_name])
            ? sanitize_text_field(wp_unslash($_COOKIE[$cookie_name]))
            : '';

        if (empty($current_cookie)) {
            self::set_cookie_value($cookie_name, $cookie_value);
        } elseif (strpos($current_cookie, 'guest-') !== 0) {
            // Reset cookie if invalid (not a guest cookie when expected).
            self::set_cookie_value($cookie_name, $cookie_value);
        }
    }

    /**
     * Helper to set a secure cookie value.
     *
     * @param  string  $cookie_name  Cookie name.
     * @param  string  $cookie_value  Cookie value.
     *
     * @return void
     * @since 2.0.0
     */
    private static function set_cookie_value($cookie_name, $cookie_value)
    {
        setcookie(
            $cookie_name,
            $cookie_value,
            [
                'expires'  => time() + CBXPOLL_COOKIE_EXP_14D,
                'path'     => SITECOOKIEPATH,
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );

        // Ensure the cookie is available immediately in the current request.
        $_COOKIE[$cookie_name] = $cookie_value;
    }


    /**
     * Get IP address
     *
     * @return string|void
     */
    public static function get_ipaddress_temp()
    {

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip_address = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));
        } elseif (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field(wp_unslash($_SERVER["REMOTE_ADDR"])) : '';
        } else {

            $ip_address = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? sanitize_text_field(wp_unslash($_SERVER["HTTP_X_FORWARDED_FOR"])) : '';
        }

        if (strpos($ip_address, ',') !== false) {
            $ip_address = explode(',', $ip_address);
            $ip_address = $ip_address[0];
        }

        return esc_attr($ip_address);
    }//end method get_ipaddress_temp

    /**
     * Get IP address
     *
     * @return string|void
     */
    public static function get_ipaddress_last()
    {
        foreach (
            [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR'
            ] as $key
        ) {
            if (array_key_exists($key, $_SERVER) === true) {
                $keys = isset($_SERVER[$key]) ? sanitize_text_field(wp_unslash($_SERVER[$key])) : '';

                foreach (explode(',', $keys) as $ip) {
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP,
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return '';
    }

    /**
     * Determines the user's actual IP address and attempts to partially
     * anonymize an IP address by converting it to a network ID.
     *
     * Geolocating the network ID usually returns a similar location as the
     * actual IP, but provides some privacy for the user.
     *
     * $_SERVER['REMOTE_ADDR'] cannot be used in all cases, such as when the user
     * is making their request through a proxy, or when the web server is behind
     * a proxy. In those cases, $_SERVER['REMOTE_ADDR'] is set to the proxy address rather
     * than the user's actual address.
     *
     * Modified from https://stackoverflow.com/a/2031935/450127, MIT license.
     * Modified from https://github.com/geertw/php-ip-anonymizer, MIT license.
     *
     * SECURITY WARNING: This function is _NOT_ intended to be used in
     * circumstances where the authenticity of the IP address matters. This does
     * _NOT_ guarantee that the returned address is valid or accurate, and it can
     * be easily spoofed.
     *
     * @return string|false The anonymized address on success; the given address
     *                      or false on failure.
     * @since 4.8.0
     *
     */
    public static function get_ipaddress()
    {
        $client_ip = false;

        // In order of preference, with the best ones for this purpose first.
        $address_headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($address_headers as $header) {
            if (array_key_exists($header, $_SERVER)) {
                /*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */

                $headers       = isset($_SERVER[$header]) ? sanitize_text_field(wp_unslash($_SERVER[$header])) : '';
                $address_chain = explode(',', $headers);
                $client_ip     = trim($address_chain[0]);

                break;
            }
        }

        if ( ! $client_ip) {
            return false;
        }

        $anon_ip = wp_privacy_anonymize_ip($client_ip, true);

        if ('0.0.0.0' === $anon_ip || '::' === $anon_ip) {
            return false;
        }

        return $anon_ip;
    }

    /**
     * Create custom post type poll
     */
    public static function create_cbxpoll_post_type()
    {
        $settings = new PollSettings();

        $slug_single  = $settings->get_field('slug_single', 'cbxpoll_slugs_settings', 'cbxpoll');
        $slug_archive = $settings->get_field('slug_archive', 'cbxpoll_slugs_settings', 'cbxpoll');


        $args = [
            'labels'          => [
                'name'                  => esc_html__('Polls', 'cbxpoll'),
                'singular_name'         => esc_html__('Poll', 'cbxpoll'),
                'menu_name'             => _x('Polls', 'Admin Menu text', 'cbxpoll'),
                'name_admin_bar'        => _x('Poll', 'Add New on Toolbar', 'cbxpoll'),
                'add_new'               => __('Add New', 'cbxpoll'),
                'add_new_item'          => __('Add New Poll', 'cbxpoll'),
                'new_item'              => __('New Poll', 'cbxpoll'),
                'edit_item'             => __('Edit Poll', 'cbxpoll'),
                'view_item'             => __('View Poll', 'cbxpoll'),
                'all_items'             => __('All Polls', 'cbxpoll'),
                'search_items'          => __('Search Polls', 'cbxpoll'),
                'parent_item_colon'     => __('Parent Polls:', 'cbxpoll'),
                'not_found'             => __('No books found.', 'cbxpoll'),
                'not_found_in_trash'    => __('No books found in Trash.', 'cbxpoll'),
                'featured_image'        => _x('Poll Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'cbxpoll'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'cbxpoll'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'cbxpoll'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'cbxpoll'),
                'archives'              => _x('Poll archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'cbxpoll'),
                'insert_into_item'      => _x('Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'cbxpoll'),
                'uploaded_to_this_item' => _x('Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'cbxpoll'),
                'filter_items_list'     => _x('Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'cbxpoll'),
                'items_list_navigation' => _x('Polls list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'cbxpoll'),
                'items_list'            => _x('Polls list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'cbxpoll')

            ],
            'menu_icon'       => esc_url(CBXPOLL_ROOT_URL).'assets/images/poll_icon.png', // 16px16
            'public'          => true,
            //'has_archive'     => true,
            'has_archive'     => sanitize_title($slug_archive),
            'capability_type' => 'page',
            'supports'        => apply_filters('cbxpoll_post_type_supports', [
                'title',
                'editor',
                'author',
                'thumbnail'
            ]),
            'rewrite'         => ['slug' => sanitize_title($slug_single)],
        ];

        register_post_type('cbxpoll', apply_filters('cbxpoll_post_type_args', $args));


	    // register cbxpoll_cat taxonomy
	    $cat_enable       = $settings->get_field( 'cat_enable', 'cbxpoll_global_settings', 'on' );
	    $cat_slug_default = $settings->get_field( 'cat_slug', 'cbxpoll_global_settings', 'poll-cat');
	    $cat_slug         = apply_filters( 'cbxpoll_category_slug', $cat_slug_default );

	    if ( $cat_enable == 'on' ) {
		    $poll_cat_labels = [
			    'name'                       => _x( 'Categories', 'Taxonomy General Name', 'cbxpoll' ),
			    'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'cbxpoll' ),
			    'menu_name'                  => esc_html__( 'Categories', 'cbxpoll' ),
			    'all_items'                  => esc_html__( 'All Categories', 'cbxpoll' ),
			    'parent_item'                => esc_html__( 'Parent Category', 'cbxpoll' ),
			    'parent_item_colon'          => esc_html__( 'Parent Category:', 'cbxpoll' ),
			    'new_item_name'              => esc_html__( 'New Category Name', 'cbxpoll' ),
			    'add_new_item'               => esc_html__( 'Add New Category', 'cbxpoll' ),
			    'edit_item'                  => esc_html__( 'Edit Category', 'cbxpoll' ),
			    'update_item'                => esc_html__( 'Update Category', 'cbxpoll' ),
			    'view_item'                  => esc_html__( 'View Category', 'cbxpoll' ),
			    'separate_items_with_commas' => esc_html__( 'Separate Categories with commas', 'cbxpoll' ),
			    'add_or_remove_items'        => esc_html__( 'Add or remove Categories', 'cbxpoll' ),
			    'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'cbxpoll' ),
			    'popular_items'              => esc_html__( 'Popular Categories', 'cbxpoll' ),
			    'search_items'               => esc_html__( 'Search Categories', 'cbxpoll' ),
			    'not_found'                  => esc_html__( 'Not Found', 'cbxpoll' ),
			    'no_terms'                   => esc_html__( 'No Categories', 'cbxpoll' ),
			    'items_list'                 => esc_html__( 'Categories list', 'cbxpoll' ),
			    'items_list_navigation'      => esc_html__( 'Categories list navigation', 'cbxpoll' ),
		    ];

		    $poll_cat_args = [
			    'labels'            => apply_filters( 'cbxpoll_post_tax_category_labels', $poll_cat_labels ),
			    'hierarchical'      => true,
			    'public'            => true,
			    'show_ui'           => true,
			    'show_admin_column' => true,
			    'show_in_nav_menus' => true,
			    'show_tagcloud'     => true,
			    'rewrite'           => [
				    'slug' => $cat_slug
			    ]
		    ];
		    register_taxonomy( 'cbxpoll_cat', [ 'cbxpoll' ], apply_filters( 'cbxpoll_post_tax_category_args', $poll_cat_args ) );
	    }


	    // register cbxpoll_tag taxonomy
	    $tag_enable       = $settings->get_field( 'tag_enable', 'cbxpoll_global_settings', 'on' );
	    $tag_slug_default = $settings->get_field( 'tag_slug', 'cbxpoll_global_settings', 'poll-tag');
	    $tag_slug         = apply_filters( 'cbxpoll_tag_slug', $tag_slug_default );

	    if ( $tag_enable == 'on' ) {
		    $poll_tag_labels = [
			    'name'                       => _x( 'Tags', 'Taxonomy General Name', 'cbxpoll' ),
			    'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'cbxpoll' ),
			    'menu_name'                  => esc_html__( 'Tags', 'cbxpoll' ),
			    'all_items'                  => esc_html__( 'All Tags', 'cbxpoll' ),
			    'parent_item'                => esc_html__( 'Parent Tag', 'cbxpoll' ),
			    'parent_item_colon'          => esc_html__( 'Parent Tag:', 'cbxpoll' ),
			    'new_item_name'              => esc_html__( 'New Tag Name', 'cbxpoll' ),
			    'add_new_item'               => esc_html__( 'Add New Tag', 'cbxpoll' ),
			    'edit_item'                  => esc_html__( 'Edit Tag', 'cbxpoll' ),
			    'update_item'                => esc_html__( 'Update Tag', 'cbxpoll' ),
			    'view_item'                  => esc_html__( 'View Tag', 'cbxpoll' ),
			    'separate_items_with_commas' => esc_html__( 'Separate Tags with commas', 'cbxpoll' ),
			    'add_or_remove_items'        => esc_html__( 'Add or remove Tags', 'cbxpoll' ),
			    'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'cbxpoll' ),
			    'popular_items'              => esc_html__( 'Popular Tags', 'cbxpoll' ),
			    'search_items'               => esc_html__( 'Search Tags', 'cbxpoll' ),
			    'not_found'                  => esc_html__( 'Not Found', 'cbxpoll' ),
			    'no_terms'                   => esc_html__( 'No Tags', 'cbxpoll' ),
			    'items_list'                 => esc_html__( 'Tags list', 'cbxpoll' ),
			    'items_list_navigation'      => esc_html__( 'Tags list navigation', 'cbxpoll' ),
		    ];

		    $poll_tag_args = [
			    'labels'            => apply_filters( 'cbxpoll_post_tax_tag_labels', $poll_tag_labels ),
			    'hierarchical'      => false,
			    'public'            => true,
			    'show_ui'           => true,
			    'show_admin_column' => true,
			    'show_in_nav_menus' => true,
			    'show_tagcloud'     => true,
			    'rewrite'           => [
				    'slug' => $tag_slug
			    ]
		    ];

		    register_taxonomy( 'cbxpoll_tag', [ 'cbxpoll' ], apply_filters( 'cbxpoll_post_tax_tag_args', $poll_tag_args ) );
	    }
    }//end method create_cbxpoll_post_type

    /**
     * create table with plugin activate hook
     */

    public static function install_table()
    {
        global $wpdb;
        $charset_collate = '';
        if ( ! empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

        require_once(ABSPATH.'/wp-admin/includes/upgrade.php');

        $poll_vote_table = esc_sql(PollHelper::poll_table_name());

        $sql = "CREATE TABLE $poll_vote_table (
                  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  poll_id bigint(20) NOT NULL,
                  poll_title text NOT NULL,
                  user_name varchar(255) NOT NULL,
                  is_logged_in tinyint(1) NOT NULL,
                  user_cookie varchar(1000) NOT NULL,
                  user_ip varchar(45) NOT NULL,
                  user_id bigint(20) unsigned NOT NULL,
                  user_answer text NOT NULL,
                  published tinyint(3) NOT NULL DEFAULT '1',
                  comment LONGTEXT NOT NULL,
                  guest_hash VARCHAR(32) NOT NULL,
                  guest_name varchar(100) DEFAULT NULL,
                  guest_email varchar(100) DEFAULT NULL,
                  created int(20) NOT NULL,
                  reasons text NOT NULL,
                  reason_comments text NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;";
        dbDelta($sql);
    }//end method install_table

    /**
     * Insert user vote
     *
     * @param  array  $user_vote
     *
     * @return bool | vote id
     */
    public static function update_poll($user_vote)
    {
        global $wpdb;

        if ( ! empty($user_vote)) {
            $votes_table = esc_sql(PollHelper::poll_table_name());

            $vote_insert_format = [
                '%d',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d'
            ];

            $vote_insert_format = apply_filters('cbxpoll_vote_insert_format', $vote_insert_format);
            $success            = $wpdb->insert($votes_table, $user_vote, $vote_insert_format);//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

            return ($success) ? $wpdb->insert_id : false;
        }

        return false;
    }//end method update_poll


    /**
     * CBX Poll vote table name
     *
     * @return string
     */
    public static function poll_table_name()
    {
        global $wpdb;

        return $wpdb->prefix.'cbxpoll_votes';
    }//end method poll_table_name

    /**
     * @param $string
     *
     * @return string
     *
     */
    public static function check_value_type($string)
    {
        $t   = gettype($string);
        $ret = '';

        switch ($t) {
            case 'string' :
                $ret = '\'%s\'';
                break;

            case 'integer':
                //$ret = '\'%d\'';
                $ret = '%d';
                break;
        }

        return $ret;
    }//end method check_value_type

    /**
     * Returns all votes for any poll
     *
     * @param  int  $poll_id  cbxpoll type post id
     * @param  bool  $is_object  array or object return type
     *
     *
     * @return mixed
     *
     */
    public static function get_pollResult($poll_id, $is_object = false)
    {
        global $wpdb;
        $poll_vote_table = esc_sql(PollHelper::poll_table_name());

        $sql = $wpdb->prepare("SELECT * FROM {$poll_vote_table} WHERE poll_id=%d AND published = 1", absint($poll_id));//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return $wpdb->get_results($sql, ARRAY_A);//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
    }// end of function get_pollresult

    /**
     * Is poll voted or not by vote count (not taking publish status into account)
     *
     * @param $poll_id
     *
     * @return int
     */
    public static function is_poll_voted($poll_id)
    {
        global $wpdb;
        $poll_vote_table = esc_sql(PollHelper::poll_table_name());

        $sql = $wpdb->prepare("SELECT COUNT(*) AS total_count FROM {$poll_vote_table} WHERE poll_id=%d", absint($poll_id)); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        $total_count = absint($wpdb->get_var($sql));//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

        return ($total_count > 0) ? 1 : 0;
    }//end method is_poll_voted

    /**
     * Returns single vote result by id
     *
     * @param  int  $vote  single vote id
     * @param  bool  $is_object  array or object return type
     *
     *
     * @return mixed
     *
     */
    public static function get_voteResult($vote_id, $is_object = false)
    {
        global $wpdb;
        $poll_vote_table = esc_sql(PollHelper::poll_table_name());

        $sql = $wpdb->prepare("SELECT * FROM {$poll_vote_table} WHERE id=%d", $vote_id);//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return $wpdb->get_results($sql, ARRAY_A);//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
    }// end of function get_pollresult

    /**
     * @param $array
     *
     * @return array
     */
    public static function check_array_element_value_type($array)
    {
        $ret = [];

        if ( ! empty($array)) {
            foreach ($array as $val) {
                $ret[] = PollHelper::check_value_type($val);
            }
        }

        return $ret;
    } //end of function check_array_element_value_type

    /**
     * Defination of all Poll Display/Chart Types
     *
     * @return array
     */
    public static function cbxpoll_display_options()
    {
        $methods = [];

        return apply_filters('cbxpoll_display_options', $methods);
    }//end method cbxpoll_display_options

    /**
     * Return poll display option as associative array
     *
     * @param  array  $methods
     *
     * @return array
     */
    public static function cbxpoll_display_options_linear($methods)
    {
        $linear_methods = [];

        foreach ($methods as $key => $val) {
            $linear_methods[$key] = $val['title'];
        }

        return $linear_methods;
    }//end method cbxpoll_display_options_linear

    public static function getVoteCountByStatus($poll_id = 0)
    {
        global $wpdb;

        $poll_vote_table = esc_sql(PollHelper::poll_table_name());

        $where_sql = '';
        if ($poll_id != 0) {
            $where_sql .= $wpdb->prepare('poll_id=%d', $poll_id);
        }

        if ($where_sql == '') {
            $where_sql = '1';
        }

        $sql_select = "SELECT published, COUNT(*) as vote_counts FROM {$poll_vote_table}  WHERE   $where_sql GROUP BY published";

        $results = $wpdb->get_results("$sql_select", 'ARRAY_A');//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

        $total = 0;
        $data  = [
            '0'     => 0,
            '1'     => 0,
            '2'     => 0,
            '3'     => 0,
            'total' => $total
        ];


        if ($results != null) {
            foreach ($results as $result) {
                $total                      += absint($result['vote_counts']);
                $data[$result['published']] = $result['vote_counts'];
            }
            $data['total'] = $total;
        }

        return $data;
    }//end method getVoteCountByStatus

    /**
     * Filter the format of the sending mail
     *
     * @param  type  $content_type
     *
     * @return string
     */
    public static function cbxppoll_mail_content_type($content_type = 'text/plain')
    {
        if ($content_type == 'html') {
            return 'text/html';
        } elseif ($content_type == 'multipart') {
            return 'multipart/mixed';
        } else {
            return 'text/plain';
        }
    }

    /**
     * Char Length check  thinking utf8 in mind
     *
     * @param $text
     *
     * @return int
     */
    public static function utf8_compatible_length_check($text)
    {
        if (wp_is_valid_utf8($text)) {
            $length = mb_strlen($text);
        } else {
            $length = strlen($text);
        }

        return $length;
    }//end method utf8_compatible_length_check

    /**
     * Returns poll possible status as array, keys are value of status
     *
     * @return array
     */
    public static function cbxpoll_status_by_value()
    {
        $states = [
            '0' => esc_html__('Unapproved', 'cbxpoll'),
            '1' => esc_html__('Approved', 'cbxpoll'),
            '2' => esc_html__('Spam', 'cbxpoll'),
            '3' => esc_html__('Unverified', 'cbxpoll')
        ];

        return apply_filters('cbxpoll_status_by_value', $states);
    }//end method cbxpoll_status_by_value

    /**
     * Returns poll possible status as array, keys are slug of status
     *
     * @return array
     */
    public static function cbxpoll_status_by_slug()
    {
        $states = [
            'unapprove'  => esc_html__('Unapproved', 'cbxpoll'),
            'approve'    => esc_html__('Approved', 'cbxpoll'),
            'spam'       => esc_html__('Spam', 'cbxpoll'),
            'unverified' => esc_html__('Unverified', 'cbxpoll')
        ];

        return apply_filters('cbxpoll_status_by_value', $states);
    }

    /**
     * Returns poll possible status as array, keys are value of status and values are slug
     *
     * @return array
     */
    public static function cbxpoll_status_by_value_with_slug()
    {
        $states = [
            '0' => 'unapprove',
            '1' => 'approve',
            '2' => 'spam',
            '3' => 'unverified',
        ];

        return apply_filters('cbxpoll_status_by_value_with_slug', $states);
    }

    /**
     * Get the user roles for voting purpose
     *
     * @param  string  $useCase
     *
     * @return array
     */
    public static function user_roles($plain = true, $include_guest = false, $ignore = [])
    {
        global $wp_roles;

        if ( ! function_exists('get_editable_roles')) {
            require_once(ABSPATH.'/wp-admin/includes/user.php');

        }

        $userRoles = [];
        if ($plain) {
            foreach (get_editable_roles() as $role => $roleInfo) {
                if (in_array($role, $ignore)) {
                    continue;
                }
                $userRoles[$role] = $roleInfo['name'];
            }
            if ($include_guest) {
                $userRoles['guest'] = esc_html__("Guest", 'cbxpoll');
            }
        } else {
            //optgroup
            $userRoles_r = [];
            foreach (get_editable_roles() as $role => $roleInfo) {
                if (in_array($role, $ignore)) {
                    continue;
                }
                $userRoles_r[$role] = $roleInfo['name'];
            }

            $userRoles = [
                'Registered' => $userRoles_r,
            ];

            if ($include_guest) {
                $userRoles['Anonymous'] = [
                    'guest' => esc_html__("Guest", 'cbxpoll')
                ];
            }
        }

        return apply_filters('cbxpoll_userroles', $userRoles, $plain, $include_guest);
    }

    /**
     * Get all  core tables list
     */
    public static function getAllDBTablesList()
    {
        global $wpdb;

        $table_names                  = [];
        $table_names['cbxpoll_votes'] = PollHelper::poll_table_name();


        return apply_filters('cbxpoll_table_list', $table_names);
    }//end method getAllDBTablesList

    /**
     * List all global option name with prefix cbxpoll_
     */
    public static function getAllOptionNames()
    {
        global $wpdb;

        $prefix = 'cbxpoll_';

        $wild = '%';
        $like = $wpdb->esc_like($prefix).$wild;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $option_names = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s",
            $like), ARRAY_A);

        return apply_filters('cbxpoll_option_names', $option_names);
    }//end method getAllOptionNames

    /**
     * Option names only
     *
     * @return array
     */
    public static function getAllOptionNamesValues()
    {
        $option_values = self::getAllOptionNames();
        $names_only    = [];

        foreach ($option_values as $key => $value) {
            $names_only[] = $value['option_name'];
        }

        return apply_filters('cbxpoll_option_names_only', $names_only);
    }//end method getAllOptionNamesValues

    /**
     * (Recommended not to use)Setup a post object and store the original loop item so we can reset it later
     *
     * @param  obj  $post_to_setup  The post that we want to use from our custom loop
     */
    public static function setup_admin_postdata($post_to_setup)
    {

        //only on the admin side
        if (is_admin()) {

            //get the post for both setup_postdata() and to be cached
            global $post;

            //only cache $post the first time through the loop
            if ( ! isset($GLOBALS['post_cache'])) {
                $GLOBALS['post_cache'] = $post; //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            }

            //setup the post data as usual
            $post = $post_to_setup;
            setup_postdata($post);
        } else {
            setup_postdata($post_to_setup);
        }
    }//end method setup_admin_postdata


    /**
     * (Recommended not to use)Reset $post back to the original item
     *
     */
    public static function wp_reset_admin_postdata()
    {
        //only on the admin and if post_cache is set
        if (is_admin() && ! empty($GLOBALS['post_cache'])) {

            //globalize post as usual
            global $post;

            //set $post back to the cached version and set it up
            $post = $GLOBALS['post_cache'];
            setup_postdata($post);

            //cleanup
            unset($GLOBALS['post_cache']);
        } else {
            wp_reset_postdata();
        }
    }//end method wp_reset_admin_postdata

    /**
     * List polls
     *
     * @param  int  $user_id
     * @param  int  $per_page
     * @param  int  $page_number
     * @param  string  $chart_type
     * @param  string  $answer_grid_list
     * @param  string  $description
     * @param  string  $reference
     *
     * @return array
     * @throws Exception
     */
    public static function poll_list($user_id = 0, $per_page = 10, $page_number = 1, $chart_type = '', $answer_grid_list = '', $description = '', $reference = 'shortcode')
    {
        $setting_api = new PollSettings();


        global $post;
        $output = [];

        $args = [
            'post_type'      => 'cbxpoll',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page_number
        ];

        if (absint($user_id) > 0) {
            $args['author'] = $user_id;
        }

        $content = '';

        $posts_array = new \WP_Query($args);


        $total_count = absint($posts_array->found_posts);

        if ($posts_array->have_posts()) {
            $output['found']         = 1;
            $output['found_posts']   = $total_count;
            $output['max_num_pages'] = ceil($total_count / $per_page);

            //foreach ( $posts_array as $post ) : setup_postdata( $post );
            while ($posts_array->have_posts()) : $posts_array->the_post();
                $poll_id = get_the_ID();

                $content .= PollHelper::cbxpoll_single_display($poll_id, $reference, $chart_type, $answer_grid_list,
                    $description);
                //endforeach;
            endwhile;
            wp_reset_postdata();

        } else {
            $output['found'] = 0;
        }

        $output['content'] = $content;

        return $output;
    }//end method poll_list

    /**
     * Shows a single poll
     *
     * @param  int  $post_id
     * @param  string  $reference
     * @param  string  $result_chart_type
     * @param  string  $grid
     * @param  int  $description
     *
     * @return string
     * @throws Exception
     */
    public static function cbxpoll_single_display($post_id = 0, $reference = 'shortcode', $result_chart_type = '', $grid = '', $description = '')
    {
        //if poll id
        if (absint($post_id) == 0) {
            return '';
        }

        global $wpdb;

        $setting_api  = $settings = new PollSettings();
        $current_user = wp_get_current_user();
        $user_id      = $current_user->ID;
        $user_ip      = PollHelper::get_ipaddress();
        $poll_output  = '';

        //$allow_guest_sign = $settings->get_field( 'allow_guest_sign', 'cbxpoll_global_settings', 'on' );

        $poll_status = get_post_status($post_id);
        if ($poll_status !== 'publish') {
            $this_user_role = $current_user->roles;
            if (in_array('administrator', $this_user_role) || in_array('editor', $this_user_role)) {
                $poll_output .= esc_html__('Note: Poll is not published yet or poll doesn\'t exists. You are checking this as administrator/editor.',
                    'cbxpoll');
            } else {
                return esc_html__('Sorry, poll is not published yet or poll doesn\'t exists.', 'cbxpoll');
            }
        }//end checking publish status

        //todo: need to get it from single poll if we introduce this inside poll setting
        $grid = ($grid == '') ? absint($settings->get_field('answer_grid_list', 'cbxpoll_global_settings',
            0)) : absint($grid);

        $grid_class = ($grid != 0) ? 'cbxpoll-form-insidewrap-grid' : '';


        if ($user_id == 0) {
            $user_session = isset($_COOKIE[CBXPOLL_COOKIE_NAME]) ? sanitize_text_field(wp_unslash($_COOKIE[CBXPOLL_COOKIE_NAME])) : ''; //this is string

        } elseif (is_user_logged_in()) {
            $user_session = 'user-'.$user_id; //this is string
        }

        //$setting_api = get_option('cbxpoll_global_settings');
        $poll_vote_table = PollHelper::poll_table_name();

        //poll informations from meta

        $poll_start_date = get_post_meta($post_id, '_cbxpoll_start_date', true); //poll start date
        $poll_end_date   = get_post_meta($post_id, '_cbxpoll_end_date', true);   //poll end date
        $poll_user_roles = get_post_meta($post_id, '_cbxpoll_user_roles', true); //poll user roles

        if ( ! is_array($poll_user_roles)) {
            $poll_user_roles = [];
        }

        $show_description               = intval(get_post_meta($post_id, '_cbxpoll_content',
            true)); //show poll description or not
        $poll_never_expire              = intval(get_post_meta($post_id, '_cbxpoll_never_expire',
            true)); //poll never epire
        $poll_show_result_before_expire = intval(get_post_meta($post_id, '_cbxpoll_show_result_before_expire',
            true));                                                                                    //poll never epire
        $poll_result_chart_type         = get_post_meta($post_id, '_cbxpoll_result_chart_type', true); //chart type
        $poll_is_voted                  = PollHelper::is_poll_voted($post_id);

        //$poll_show_result_all           = get_post_meta( $post_id, '_cbxpoll_show_result_all', true ); //show_result_all
        //$poll_is_voted          = intval( get_post_meta( $post_id, '_cbxpoll_is_voted', true ) ); //at least a single vote


        $poll_answers_extra = get_post_meta($post_id, '_cbxpoll_answer_extra', true);

        //new field from v1.0.1

        $poll_multivote = absint(get_post_meta($post_id, '_cbxpoll_multivote', true));   //at least a single vote

        $vote_input_type = ($poll_multivote) ? 'checkbox' : 'radio';

        //$global_result_chart_type   = isset($setting_api['result_chart_type'])? $setting_api['result_chart_type']: 'text';
        //$poll_result_chart_type = get_post_meta($post_id, '_cbxpoll_result_chart_type', true);

        $result_chart_type = ($result_chart_type != '') ? $result_chart_type : $poll_result_chart_type;


        $description = ($description != '') ? absint($description) : $show_description;


        //fallback as text if addon no installed
        $result_chart_type = PollHelper::chart_type_fallback($result_chart_type);          //make sure that if chart type is from pro addon then it's installed


        $poll_answers = get_post_meta($post_id, '_cbxpoll_answer', true);

        $poll_answers = is_array($poll_answers) ? $poll_answers : [];
        $poll_colors  = get_post_meta($post_id, '_cbxpoll_answer_color', true);

        $log_method = $setting_api->get_option('logmethod', 'cbxpoll_global_settings', 'both');
      

        //$setting_api->get_option('user_roles', 'cbxpoll_global_settings', 'both')

        //$log_metod = ($log_method != '') ? $log_method : 'both';

        $is_poll_expired = new \DateTime($poll_end_date) < new \DateTime();        //check if poll expired from it's end data
        $is_poll_expired = ($poll_never_expire == 1) ? false : $is_poll_expired;   //override expired status based on the meta information

        //$poll_allowed_user_group = empty($poll_user_roles) ? $setting_api['user_roles'] : $poll_user_roles;
        //$poll_allowed_user_group = empty( $poll_user_roles ) ? $setting_api->get_option( 'user_roles', 'cbxpoll_global_settings', array() ) : $poll_user_roles;
        $poll_allowed_user_group = $poll_user_roles;

        $cb_question_list_to_find_ans = [];
        foreach ($poll_answers as $poll_answer) {
            array_push($cb_question_list_to_find_ans, $poll_answer);
        }


        $nonce = wp_create_nonce('cbxpolluservote');

        $poll_output .= '<div class="cbx-chota cbxpoll_wrapper cbxpoll_wrapper-'.$post_id.' cbxpoll_wrapper-'.$reference.'" data-reference ="'.$reference.'" >';
        //$poll_output .= '<div class="cbxpoll-qresponse cbxpoll-qresponse-' . $post_id . '"></div>';

        //check if the poll started still
        if (new \DateTime($poll_start_date) <= new \DateTime()) {


            if ($reference != 'content_hook') {
                $poll_output .= '<h3>'.get_the_title($post_id).'</h3>';
            }

            if ($reference != 'content_hook') {
                //if enabled from shortcode and enabled from post meta field
                if (absint($description) == 1) {
                    $poll_conobj  = get_post($post_id);
                    $poll_content = '';
                    if (is_object($poll_conobj)) {
                        $poll_content = $poll_conobj->post_content;
                        $poll_content = strip_shortcodes($poll_content);
                        $poll_content = wpautop($poll_content);
                        $poll_content = convert_smilies($poll_content);
                        //$poll_content 	= apply_filters('the_content', $poll_content);
                        $poll_content = str_replace(']]>', ']]&gt;', $poll_content);
                    }


                    $poll_output .= '<div class="cbxpoll-description">'.apply_filters('cbxpoll_description',
                            $poll_content, $post_id).'</div>';
                }

            }

            $poll_is_voted_by_user = 0;

            if ($log_method == 'cookie') {


                $sql = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM {$poll_vote_table} ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_cookie = %s", $post_id, $user_id, $user_session);//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

                $poll_is_voted_by_user = $wpdb->get_var($sql); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

            } elseif ($log_method == 'ip') {

                $sql = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM {$poll_vote_table} ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $post_id, $user_id, $user_ip);//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

                $poll_is_voted_by_user = $wpdb->get_var($sql);//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

            } else {
                if ($log_method == 'both') {

                    $sql = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM {$poll_vote_table} ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_cookie = %s", $post_id, $user_id, $user_session);//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

                    $vote_count_cookie = $wpdb->get_var($sql); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

                    $sql           = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM {$poll_vote_table} ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $post_id, $user_id, $user_ip);//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $vote_count_ip = $wpdb->get_var($sql); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

                    if ($vote_count_cookie >= 1 || $vote_count_ip >= 1) {
                        $poll_is_voted_by_user = 1;
                    }

                }
            }

            $poll_is_voted_by_user = apply_filters('cbxpoll_is_user_voted', $poll_is_voted_by_user);

            if ($is_poll_expired) { // if poll has expired


                //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("SELECT ur.id AS answer FROM {$poll_vote_table} ur WHERE ur.poll_id = %d", $post_id);

                //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
                $cb_has_answer = $wpdb->get_var($sql);

                if ($cb_has_answer != null) {

                    $poll_output .= PollHelper:: show_single_poll_result($post_id, $reference, $result_chart_type);
                }

                //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("SELECT ur.user_answer AS answer FROM {$poll_vote_table} ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_ip = %s AND ur.user_cookie = %s ",
                    $post_id, $user_id, $user_ip, $user_session);

                //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
                $answers_by_user = $wpdb->get_var($sql);

                $answers_by_user_html = '';

                if ($answers_by_user !== null) {
                    $answers_by_user = maybe_unserialize($answers_by_user);
                    if (is_array($answers_by_user)) {
                        $user_answers_textual = [];
                        foreach ($answers_by_user as $uchoice) {
                            $user_answers_textual[] = isset($poll_answers[$uchoice]) ? $poll_answers[$uchoice] : esc_html__('Unknown or answer deleted',
                                'cbxpoll');
                        }

                        $answers_by_user_html = implode(", ", $user_answers_textual);
                    } else {
                        $answers_by_user      = absint($answers_by_user);
                        $answers_by_user_html = $poll_answers[$answers_by_user];

                    }

                    if ($answers_by_user_html != "") {
                        /* translators: 1: Poll answer made by user*/
                        $poll_output .= '<p class="cbxpoll-voted-info cbxpoll-voted-info-'.absint($post_id).'">'.wp_kses(sprintf(__('The Poll is out of date. You have already voted for <strong>"%s"</strong>',
                                'cbxpoll'), $answers_by_user_html), ['strong' => []]).' </p>';
                    } else {
                        /* translators: 1: Poll answer made by user*/
                        $poll_output .= '<p class="cbxpoll-voted-info cbxpoll-voted-info-'.absint($post_id).'"> '.wp_kses(sprintf(__('The Poll is out of date. You have already voted for <strong>"%s"</strong>',
                                'cbxpoll'), $answers_by_user_html), ['strong' => []]).' </p>';

                    }

                } else {
                    $poll_output .= '<p class="cbxpoll-voted-info cbxpoll-voted-info-'.absint($post_id).'"> '.esc_html__('The Poll is out of date. You have not voted.',
                            'cbxpoll').'</p>';
                }

            } // end of if poll expired
            else {
                if (is_user_logged_in()) {
                    global $current_user;
                    $this_user_role = $current_user->roles;
                } else {
                    $this_user_role = ['guest'];
                }

                $allowed_user_group = array_intersect($poll_allowed_user_group, $this_user_role);

                //current user is not allowed
                if ((sizeof($allowed_user_group)) < 1) {

                    //we know poll is not expired, and user is not allowed to vote
                    //now we check if the user i allowed to see result and result is allow to show before expire
                    //if ( $poll_show_result_all == '1' && $poll_show_result_before_expire == '1' ) {
                    if ($poll_show_result_before_expire == 1) {
                        if ($poll_is_voted) {
                            $poll_output .= PollHelper::show_single_poll_result($post_id, $reference,
                                $result_chart_type);
                        }

                        $poll_output .= '<p class="cbxpoll-voted-info cbxpoll-voted-info-'.absint($post_id).'"> '.esc_html__('You are not allowed to vote.',
                                'cbxpoll').'</p>';

                        //integrate user login for guest user

                        //if ( ! is_user_logged_in() && $allow_guest_sign == 'on' ):
                        if ( ! is_user_logged_in()):
                            $guest_login_form = esc_attr($settings->get_field('guest_login_form',
                                'cbxpoll_global_settings', 'wordpress'));
                            //$show_login = $settings->get_field( 'show_login_form', 'cbxpoll_general', 'yes' );
                            $login_html = '';

                            if ($guest_login_form != 'off') {
                                if ($guest_login_form != 'none') {
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    $login_html .= cbxpoll_get_template_html('global/login_form.php', [
                                        'settings' => $settings,
                                    ]);
                                } else {
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    $login_html .= cbxpoll_get_template_html('global/login_url.php', [
                                        'settings' => $settings,
                                    ]);
                                }
                            } else {
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                $login_html .= cbxpoll_get_template_html('global/login_off.php', [
                                    'settings' => $settings,
                                ]);
                            }


                            //echo $login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                            $poll_output .= $login_html;
                        endif;
                    }

                } else {
                    //current user is allowed

                    //current user has voted this once
                    if ($poll_is_voted_by_user) {

                        //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        $sql = $wpdb->prepare("SELECT ur.user_answer AS answer FROM {$poll_vote_table} ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_ip = %s AND ur.user_cookie = %s ",
                            $post_id, $user_id, $user_ip, $user_session);

                        $answers_by_user = $wpdb->get_var($sql);//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter


                        if ($answers_by_user !== null) {
                            $answers_by_user = maybe_unserialize($answers_by_user);
                            if (is_array($answers_by_user)) {
                                $user_answers_textual = [];
                                foreach ($answers_by_user as $uchoice) {
                                    $user_answers_textual[] = isset($poll_answers[$uchoice]) ? $poll_answers[$uchoice] : esc_html__('Unknown or answer deleted',
                                        'cbxpoll');
                                }

                                $answers_by_user_html = implode(", ", $user_answers_textual);
                            } else {
                                $answers_by_user      = absint($answers_by_user);
                                $answers_by_user_html = $poll_answers[$answers_by_user];

                            }


                            if ($answers_by_user_html !== "") {
                                /* translators: 1: User's answer for vote  */
                                $poll_output .= '<p class="cbxpoll-voted-info cbxpoll-voted-info-'.absint($post_id).'">'.sprintf(wp_kses(__('You have already voted for <strong>"%s"</strong>',
                                        'cbxpoll'), ['strong' => []]), $answers_by_user_html).' </p>';
                            } else {
                                $poll_output .= '<p class="cbxpoll-voted-info cbxpoll-voted-info-'.absint($post_id).'">'.esc_html__('You have already voted ',
                                        'cbxpoll').' </p>';

                            }
                        }

                        if ($poll_show_result_before_expire == 1) {
                            $poll_output .= PollHelper:: show_single_poll_result($post_id, $reference,
                                $result_chart_type);
                        }

                    } else {
                        //current user didn't vote yet
                        $poll_form_html = '';

                        $poll_form_html = apply_filters('cbxpoll_form_html_before', $poll_form_html, $post_id);

                        //if ( ! is_user_logged_in() && $allow_guest_sign == 'on' ):
                        if ( ! is_user_logged_in()):
                            $guest_login_form = esc_attr($settings->get_field('guest_login_form',
                                'cbxpoll_global_settings', 'wordpress'));
                            //$show_login = $settings->get_field( 'show_login_form', 'cbxpoll_general', 'yes' );
                            $login_html = '';

                            if ($guest_login_form != 'off') {
                                if ($guest_login_form != 'none') {
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    $login_html .= cbxpoll_get_template_html('global/login_form.php', [
                                        'settings' => $settings,
                                    ]);
                                } else {
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    $login_html .= cbxpoll_get_template_html('global/login_url.php', [
                                        'settings' => $settings,
                                    ]);
                                }
                            } else {
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                $login_html .= cbxpoll_get_template_html('global/login_off.php', [
                                    'settings' => $settings,
                                ]);
                            }


                            //echo $login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            $poll_form_html .= $login_html;
                        endif;

                        $poll_form_html .= '								
                                <div class="cbxpoll_answer_wrapper cbxpoll_answer_wrapper-'.absint($post_id).'" data-id="'.absint($post_id).'">
                                    <form action="#" class="cbxpoll-form cbxpoll-form-'.absint($post_id).'" method="post" novalidate="true">
                                        <div class="cbxpoll-form-insidewrap '.$grid_class.' cbxpoll-form-insidewrap-'.$post_id.'">';

                        $poll_form_html = apply_filters('cbxpoll_form_html_before_question', $poll_form_html, $post_id);

                        $poll_answer_list_class = 'cbxpoll-form-ans-list cbxpoll-form-ans-list-'.absint($post_id);


                        $poll_form_html .= '<ul class="'.apply_filters('cbxpoll_form_answer_list_style_class',
                                $poll_answer_list_class, $post_id).'">';

                        $poll_form_html = apply_filters('cbxpoll_form_answer_start', $poll_form_html, $post_id);

                        //listing poll answers as radio button
                        foreach ($poll_answers as $index => $answer) {

                            $poll_answers_extra_single = isset($poll_answers_extra[$index]) ? $poll_answers_extra[$index] : ['type' => 'default'];

                            $input_name = 'cbxpoll_user_answer';
                            if ($poll_multivote) {
                                $input_name .= '-'.$index;
                            }

                            $poll_answer_listitem_class = 'cbxpoll-form-ans-listitem cbxpoll-form-ans-listitem-'.absint($post_id);

                            $extra_list_style = '';
                            $extra_list_attr  = '';

                            $poll_form_html .= '<li class="'.apply_filters('cbxpoll_form_answer_listitem_style_class',
                                    $poll_answer_listitem_class, $post_id, $index, $answer,
                                    $poll_answers_extra_single).'" style="'.apply_filters('cbxpoll_form_answer_listitem_style',
                                    $extra_list_style, $post_id, $index, $answer,
                                    $poll_answers_extra_single).'" '.apply_filters('cbxpoll_form_answer_listitem_attr',
                                    $extra_list_attr, $post_id, $index, $answer, $poll_answers_extra_single).'>';

                            $form_answer_listitem_inside_html_start = '';

                            $poll_form_html .= apply_filters('cbxpoll_form_answer_listitem_inside_html_start',
                                $form_answer_listitem_inside_html_start, $post_id, $index, $answer,
                                $poll_answers_extra_single);

                            $poll_form_html .= '<div class="'.esc_attr($vote_input_type).'_field magic_'.esc_attr($vote_input_type).'_field checkbox-alignment">';

                            $poll_form_html .= '<input type="'.esc_attr($vote_input_type).'" value="'.esc_attr($index).'" class="magic-'.esc_attr($vote_input_type).' cbxpoll_single_answer cbxpoll_single_answer-radio cbxpoll_single_answer-radio-'.absint($post_id).'" data-pollcolor = "'.$poll_colors[$index].' "data-post-id="'.absint($post_id).'" name="'.$input_name.'"  data-answer="'.esc_attr($answer).' " id="cbxpoll_single_answer-radio-'.esc_attr($index).'-'.absint($post_id).'"  />';

                            $poll_form_html .= '<label class="cbxpoll_single_answer_label cbxpoll_single_answer_label_radio" for="cbxpoll_single_answer-radio-'.esc_attr($index).'-'.absint($post_id).'"><span class="cbxpoll_single_answer cbxpoll_single_answer-text cbxpoll_single_answer-text-'.absint($post_id).'"  data-post-id="'.absint($post_id).'" data-answer="'.esc_attr($answer).' ">'.apply_filters('cbxpoll_form_listitem_answer_title',
                                    $answer, $post_id, $index, $poll_answers_extra_single).'</span>';

                            $form_answer_listitem_label_extra = '';

                            $poll_form_html .= apply_filters('cbxpoll_form_answer_listitem_label_extra', $form_answer_listitem_label_extra, $post_id, $index, $answer, $poll_answers_extra_single);

                            $poll_form_html .= '</label>';

                            $poll_form_html .= '</div>';//.checkbox-alignment

                            $form_answer_listitem_inside_html_end = '';

                            $poll_form_html .= apply_filters('cbxpoll_form_answer_listitem_inside_html_end',
                                $form_answer_listitem_inside_html_end, $post_id, $index, $answer,
                                $poll_answers_extra_single);


                            $poll_form_html .= '</li>';
                        }

                        $poll_form_html = apply_filters('cbxpoll_form_answer_end', $poll_form_html, $post_id);


                        $poll_form_html .= '</ul>';

                        //hook
                        $poll_form_html = apply_filters('cbxpoll_form_html_after_question', $poll_form_html, $post_id);

                        //$poll_form_html .= ' <div class="cbxpoll-qresponse cbxpoll-qresponse-' . $post_id . '"></div>';

                        //show the poll button
                        $poll_form_html .= '<p class = "cbxpoll_ajax_link"><button type="submit" class="button primary cbxpoll_vote_btn ld-ext-right" data-reference = "'.esc_attr($reference).'" data-charttype = "'.esc_attr($result_chart_type).'" data-busy = "0" data-post-id="'.absint($post_id).'"  data-security="'.esc_attr($nonce).'" ><span>'.esc_html__('Vote',
                                'cbxpoll').'</span><span class="ld ld-spin ld-ring"></span></button></p>';
                        $poll_form_html .= '<input type="hidden" name="action" value="cbxpoll_user_vote" />';
                        $poll_form_html .= '<input type="hidden" name="reference" value="'.esc_attr($reference).'" />';
                        $poll_form_html .= '<input type="hidden" name="chart_type" value="'.esc_attr($result_chart_type).'"/>';
                        $poll_form_html .= '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'" />';
                        $poll_form_html .= '<input type="hidden" name="poll_id" value="'.absint($post_id).'"/>';
                        $poll_form_html .= '
                                         </div>
                                    </form>
                                    <div class="cbxpoll_clearfix"></div>
                                </div>
                                <div class="cbxpoll-qresponse cbxpoll-qresponse-'.absint($post_id).'"></div>
                                <div class="cbxpoll_clearfix"></div>';


                        $poll_form_html = apply_filters('cbxpoll_form_html_after', $poll_form_html, $post_id);

                        $poll_output .= apply_filters('cbxpoll_form_html', $poll_form_html, $post_id);

                    }
                    // end of if voted
                }
                // end of allowed user
            }
            // end of pole expires


        }//poll didn't start yet
        else {
            $poll_output = esc_html__('Poll Status: Yet to start', 'cbxpoll');
        }

        $poll_output .= '</div>'; //end of cbxpoll_wrapper

        return $poll_output;
    }//end method cbxpoll_single_display

    /**
     * Get result from a single poll
     *
     * @param  int  $post_id
     *
     * return string|mixed
     */
    public static function show_single_poll_result($poll_id, $reference, $result_chart_type = 'text')
    {
        global $wpdb;

        $current_user = wp_get_current_user();
        $user_id      = $current_user->ID;


        $user_ip = PollHelper::get_ipaddress();

        if ($user_id == 0) {
            $user_session = isset($_COOKIE[CBXPOLL_COOKIE_NAME]) ? sanitize_text_field(wp_unslash($_COOKIE[CBXPOLL_COOKIE_NAME])) : ''; //this is string
        } elseif (is_user_logged_in()) {
            $user_session = 'user-'.$user_id; //this is string
        }

        $setting_api     = get_option('cbxpoll_global_settings', []);
        $poll_start_date = get_post_meta($poll_id, '_cbxpoll_start_date', true); //poll start date
        $poll_end_date   = get_post_meta($poll_id, '_cbxpoll_end_date', true);   //poll end date
        $poll_user_roles = get_post_meta($poll_id, '_cbxpoll_user_roles', true); //poll user roles
        if ( ! is_array($poll_user_roles)) {
            $poll_user_roles = [];
        }

        $poll_content                   = get_post_meta($poll_id, '_cbxpoll_content', true); //poll content
        $poll_never_expire              = intval(get_post_meta($poll_id, '_cbxpoll_never_expire', true)); //poll never epire
        $poll_show_result_before_expire = intval(get_post_meta($poll_id, '_cbxpoll_show_result_before_expire', true)); //poll never epire


        $poll_result_chart_type = get_post_meta($poll_id, '_cbxpoll_result_chart_type', true); //chart type

        $result_chart_type = PollHelper::chart_type_fallback($result_chart_type);

        $poll_answers = get_post_meta($poll_id, '_cbxpoll_answer', true);
        $poll_answers = is_array($poll_answers) ? $poll_answers : [];

        $poll_colors = get_post_meta($poll_id, '_cbxpoll_answer_color', true);
        $poll_colors = is_array($poll_colors) ? $poll_colors : [];

        $total_results = PollHelper::get_pollResult($poll_id);

        $poll_result = [];

        $poll_result['reference'] = $reference;
        $poll_result['poll_id']   = $poll_id;
        $poll_result['total']     = count($total_results);

        $poll_result['colors'] = $poll_colors;
        $poll_result['answer'] = $poll_answers;
        //$poll_result['results']    		= json_encode($total_results);
        $poll_result['chart_type'] = $result_chart_type;
        $poll_result['text']       = '';

        $poll_answers_weight = [];


        foreach ($total_results as $result) {
            $user_ans = maybe_unserialize($result['user_answer']);

            if (is_array($user_ans)) {

                foreach ($user_ans as $u_ans) {
                    $old_val                     = isset($poll_answers_weight[$u_ans]) ? intval($poll_answers_weight[$u_ans]) : 0;
                    $poll_answers_weight[$u_ans] = ($old_val + 1);
                }
            } else {
                $user_ans                       = intval($user_ans);
                $old_val                        = isset($poll_answers_weight[$user_ans]) ? intval($poll_answers_weight[$user_ans]) : 0;
                $poll_answers_weight[$user_ans] = ($old_val + 1);
            }
        }

        $poll_result['answers_weight'] = $poll_answers_weight;

        //ready mix :)
        $poll_weighted_index  = [];
        $poll_weighted_labels = [];

        foreach ($poll_answers as $index => $answer_title) {
            //$poll_weighted_labels[ $answer ] = isset( $poll_answers_weight[ $index ] ) ? $poll_answers_weight[ $index ] : 0;
            $poll_weighted_index[$index]         = isset($poll_answers_weight[$index]) ? $poll_answers_weight[$index] : 0;
            $poll_weighted_labels[$answer_title] = isset($poll_answers_weight[$index]) ? $poll_answers_weight[$index] : 0;
        }

        $poll_result['weighted_index'] = $poll_weighted_index;
        $poll_result['weighted_label'] = $poll_weighted_labels;


        ob_start();

        do_action('cbxpoll_answer_html_before', $poll_id, $reference, $poll_result);
        echo '<div class="cbxpoll_result_wrap cbxpoll_result_wrap_'.esc_attr($reference).' cbxpoll_'.esc_attr($result_chart_type).'_result_wrap cbxpoll_'.esc_attr($result_chart_type).'_result_wrap_'.absint($poll_id).' cbxpoll_result_wrap_'.esc_attr($reference).'_'.absint($poll_id).' ">';

        do_action('cbxpoll_answer_html_before_question', $poll_id, $reference, $poll_result);

        $poll_display_methods = PollHelper::cbxpoll_display_options();
        $poll_display_method  = $poll_display_methods[$result_chart_type];

        $method = $poll_display_method['method'];

        if ($method != '' && is_callable($method)) {
            call_user_func_array($method, [$poll_id, $reference, $poll_result]);
        }

        do_action('cbxpoll_answer_html_after_question', $poll_id, $reference, $poll_result);

        echo '</div>';
        do_action('cbxpoll_answer_html_after', $poll_id, $reference, $poll_result);

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }//end method show_single_poll_result

    /**
     * Chart Type fallback
     *
     * @param $chart_type
     *
     * @return string
     */
    public static function chart_type_fallback($chart_type)
    {
        $poll_display_methods = PollHelper::cbxpoll_display_options();
        $chart_info           = (isset($poll_display_methods[$chart_type])) ? $poll_display_methods[$chart_type] : '';

        if ($chart_info != '' && is_callable($chart_info['method'])) {
            return $chart_type;
        }

        return 'text';
    }//end method chart_type_fallback

    /**
     * Sanitizes a hex color.
     *
     * Returns either '', a 3 or 6 digit hex color (with #), or nothing.
     * For sanitizing values without a #, see sanitize_hex_color_no_hash().
     *
     * @param  string  $color
     *
     * @return string|void
     * @since 3.4.0
     *
     */
    public static function sanitize_hex_color($color)
    {

        /*if ('' === $color) {
            return '';
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }*/

        // Return empty string if input is empty
        if ('' === $color) {
            return '';
        }

        // Remove any whitespace and ensure the color starts with #
        $color = trim($color);
        if (strpos($color, '#') !== 0) {
            $color = '#'.ltrim($color, '#');
        }

        // Validate 3, 6, or 8 hex digits (8 for RGBA)
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color)) {
            return $color;
        }

        // Return empty string for invalid formats
        return '';
    }//end method sanitize_hex_color

    /**
     * cbxpoll post type meta fields array
     *
     * @return array
     *
     * initialize with init
     */
    public static function get_meta_fields()
    {

        $roles           = PollHelper::user_roles(false, true);
        $global_settings = get_option('cbxpoll_global_settings', []);


        $default_user_roles = isset($global_settings['user_roles']) ? $global_settings['user_roles'] : PollHelper::user_roles(true,
            true);

        $default_never_expire   = isset($global_settings['never_expire']) ? intval($global_settings['never_expire']) : 0;
        $default_content        = isset($global_settings['content']) ? $global_settings['content'] : 1;
        $default_result_chart   = isset($global_settings['result_chart_type']) ? $global_settings['result_chart_type'] : 'text';
        $default_poll_multivote = isset($global_settings['poll_multivote']) ? intval($global_settings['poll_multivote']) : 0;
        //$default_show_result_all           = isset( $global_settings['show_result_all'] ) ? intval($global_settings['show_result_all']) : 0;
        $default_show_result_before_expire = isset($global_settings['show_result_before_expire']) ? intval($global_settings['show_result_before_expire']) : 0;


        // Field Array
        $prefix = '_cbxpoll_';


        $poll_display_methods = PollHelper::cbxpoll_display_options();
        $poll_display_methods = PollHelper::cbxpoll_display_options_linear($poll_display_methods);


        $start_date = new \DateTime();
        $timestamp  = time() - 86400;
        $end_date   = strtotime("+7 day", $timestamp);
        //sanitize_text_field()

        $post_meta_fields = [
            '_cbxpoll_start_date'   => [
                'label'    => esc_html__('Start Date', 'cbxpoll'),
                'desc'     => wp_kses(__('Poll Start Date. [<strong> Note:</strong> Field required. Default is today]', 'cbxpoll'), ['strong' => []]),
                'id'       => '_cbxpoll_start_date',
                'type'     => 'date',
                'default'  => $start_date->format('Y-m-d H:i:s'),
                'sanitize' => 'sanitize_text_field'
            ],
            '_cbxpoll_end_date'     => [
                'label'    => esc_html__('End Date', 'cbxpoll'),
                'desc'     => wp_kses(__('Poll End Date.  [<strong> Note:</strong> Field required. Default is next seven days. ]',
                    'cbxpoll'), ['strong' => []]),
                'id'       => '_cbxpoll_end_date',
                'type'     => 'date',
                'default'  => gmdate('Y-m-d H:i:s', $end_date),
                'sanitize' => 'sanitize_text_field'
            ],
            '_cbxpoll_user_roles'   => [
                'label'    => esc_html__('Who Can Vote', 'cbxpoll'),
                'desc'     => esc_html__('Which user role will have vote capability', 'cbxpoll'),
                'id'       => '_cbxpoll_user_roles',
                'type'     => 'multiselect',
                'options'  => $roles,
                'optgroup' => 1,
                'default'  => $default_user_roles
            ],
            '_cbxpoll_content'      => [
                'label'    => esc_html__('Show Poll Description in shortcode', 'cbxpoll'),
                'desc'     => esc_html__('Select if you want to show content.', 'cbxpoll'),
                'id'       => '_cbxpoll_content',
                'type'     => 'radio',
                'default'  => $default_content,
                'options'  => [
                    '1' => esc_html__('Yes', 'cbxpoll'),
                    '0' => esc_html__('No', 'cbxpoll')
                ],
                'sanitize' => 'absint'

            ],
            '_cbxpoll_never_expire' => [
                'label'    => esc_html__('Never Expire', 'cbxpoll'),
                'desc'     => 'Select if you want your poll to never expire.(can be override from shortcode param)',
                'id'       => '_cbxpoll_never_expire',
                'type'     => 'radio',
                'default'  => $default_never_expire,
                'options'  => [
                    '1' => esc_html__('Yes', 'cbxpoll'),
                    '0' => esc_html__('No', 'cbxpoll')
                ],
                'sanitize' => 'absint'
            ],

            '_cbxpoll_show_result_before_expire' => [
                'label'    => esc_html__('Show result before expires', 'cbxpoll'),
                'desc'     => esc_html__('Select if you want poll to show result before expires. After expires the result will be shown always. Please check it if poll never expires.',
                    'cbxpoll'),
                'id'       => '_cbxpoll_show_result_before_expire',
                'type'     => 'radio',
                'default'  => $default_show_result_before_expire,
                'options'  => [
                    '1' => esc_html__('Yes', 'cbxpoll'),
                    '0' => esc_html__('No', 'cbxpoll')
                ],
                'sanitize' => 'absint'
            ],
            /*'_cbxpoll_show_result_all'           => array(
				'label'   => esc_html__( 'Show result to all', 'cbxpoll' ),
				'desc'    => esc_html__( 'Check this if you want to show result to them who can not vote.', 'cbxpoll' ),
				'id'      => '_cbxpoll_show_result_all',
				'type'    => 'radio',
				'default' => $default_show_result_all,
				'options' => array(
					'1' => esc_html__( 'Yes', 'cbxpoll' ),
					'0' => esc_html__( 'No', 'cbxpoll' )
				)
			),*/  //removed for good
            '_cbxpoll_result_chart_type'         => [
                'label'    => esc_html__('Result Chart Style', 'cbxpoll'),
                'desc'     => esc_html__('Select how you want to show poll result.', 'cbxpoll'),
                'id'       => '_cbxpoll_result_chart_type',
                'type'     => 'select',
                'options'  => $poll_display_methods,  //new poll display method can be added via plugin
                'default'  => $default_result_chart,
                'sanitize' => 'sanitize_text_field'
            ],
            '_cbxpoll_multivote'                 => [
                'label'    => esc_html__('Enable Multi Choice', 'cbxpoll'),
                'desc'     => esc_html__('Can user vote multiple option', 'cbxpoll'),
                'id'       => '_cbxpoll_multivote',
                'type'     => 'radio',
                'default'  => $default_poll_multivote,
                'options'  => [
                    '1' => esc_html__('Yes', 'cbxpoll'),
                    '0' => esc_html__('No', 'cbxpoll')
                ],
                'sanitize' => 'absint'
            ],
        ];

        return apply_filters('cbxpoll_fields', $post_meta_fields);
    }//end method get_meta_fields


    /**
     * Single answer field template
     *
     * @param  int  $index
     * @param  string  $answers_title
     * @param  string  $answers_color
     * @param  int  $is_voted
     * @param        $answers_extra
     * @param        $poll_id
     *
     * @return string
     */
    public static function cbxpoll_answer_field_template(
        $index = 0,
        $answers_title = '',
        $answers_color = '',
        $is_voted = 0,
        $answers_extra = [],
        $poll_id = 0
    ) {
        $choose_color = esc_html__('Choose Color', 'cbxpoll');
        $color_class  = 'cbxpoll_answer_color';

        $delete_svg = cbxpoll_esc_svg(cbxpoll_load_svg('icon_delete'));

        $answer_type = isset($answers_extra['type']) ? $answers_extra['type'] : 'default';


        //$answer_reason_count = isset($answers_extra['reason_count']) ? intval($answers_extra['reason_count']) : 0;

        $answer_fields_html       = '<li class="cbx_poll_items" id="cbx-poll-answer-'.esc_attr($index).'">';
        $answer_fields_html_start = apply_filters('cbxpoll_answer_extra_fields_start', '', $index, $answers_extra,
            $is_voted, $poll_id);

        $answer_fields_html .= $answer_fields_html_start;

        $answer_fields_html .= '<span title="'.esc_attr__('Drag and Drop to reorder poll answers',
                'cbxpoll').'" class="button outline icon icon-only cbx_pollmove"><i  class="cbx-icon cbx-icon-move"></i></span>';


        $answer_fields_html .= '<input type="text" style="width:330px;" name="_cbxpoll_answer['.esc_attr($index).']" value="'.esc_attr($answers_title).'"   id="cbxpoll_answer-'.esc_attr($index).'" class="cbxpoll_answer" />';

        $answer_fields_html .= '<div data-rendered="0" class="meta-color-picker-wrapper cbxpoll_answer_color-wrap">';
        $answer_fields_html .= '<input type="hidden" id="cbxpoll_answer_color-'.esc_attr($index).'" class="'.esc_attr($color_class).' setting-color-picker" name="_cbxpoll_answer_color['.esc_attr($index).']" value="'.esc_attr($answers_color).'" />';
        $answer_fields_html .= '<span data-current-color="'.esc_attr($answers_color).'"  class="button setting-color-picker-fire">'.esc_html($choose_color).'</span>';
        $answer_fields_html .= '</div>';

        $answer_fields_html_extra = '<input type="hidden" id="cbxpoll_answer_extra_type_'.esc_attr($index).'" value="'.esc_attr($answer_type).'" name="_cbxpoll_answer_extra['.esc_attr($index).'][type]" />';
        //$answer_fields_html_extra .= '<input class="cbxpoll_answer_extra_reason_count" data-index="'.$index.'" type="hidden" id="cbxpoll_answer_extra_reason_count_'.$index.'" value="'.$answer_reason_count.'" name="_cbxpoll_answer_extra['.$index.'][reason_count]" />';

        $answer_fields_html_extra = apply_filters('cbxpoll_answer_extra_fields', $answer_fields_html_extra, $index,
            $answers_extra, $is_voted, $poll_id);

        $answer_fields_html .= $answer_fields_html_extra;


        $answer_fields_html .= '<span class="button outline error icon icon-only cbx_pollremove " title="'.esc_attr__('Remove',
                'cbxpoll').'"><i class="cbx-icon">'.$delete_svg.'</i></span>';


        $answer_fields_html .= '<div class="clear clearfix"></div>';

        $answer_fields_html_end = apply_filters('cbxpoll_answer_extra_fields_end', '', $index, $answers_extra,
            $is_voted, $poll_id);

        $answer_fields_html .= $answer_fields_html_end;
        $answer_fields_html .= '</li>';

        return $answer_fields_html;
    }//end method cbxpoll_answer_field_template

    /**
     * Get all votes of a user by various criteria
     *
     * @param  int  $user_id
     * @param  string  $orderby
     * @param  string  $order
     * @param  int  $perpage
     * @param  int  $page
     * @param  string  $status
     *
     * @return array|null|object
     */
    public static function getAllVotesByUser(
        $user_id = 0,
        $orderby = 'id',
        $order = 'desc',
        $perpage = 20,
        $page = 1,
        $status = 'all'
    ) {

        $user_id = intval($user_id);
        $data    = [];
        if (intval($user_id) == 0) {
            return $data;
        }

        global $wpdb;
        $poll_vote_table = esc_sql(PollHelper::poll_table_name());


        $sql_select = "SELECT * FROM $poll_vote_table";

        $where_sql = '';


        if (is_numeric($status)) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('published=%d', intval($status));
        }

        if (intval($user_id) > 0) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('user_id=%d', intval($user_id));
        }


        if ($where_sql == '') {
            $where_sql = '1';
        }

        $limit_sql = '';

        if ($perpage != -1) {
            $perpage     = intval($perpage);
            $start_point = ($page * $perpage) - $perpage;
            $limit_sql   .= "LIMIT";
            $limit_sql   .= ' '.$start_point.',';
            $limit_sql   .= ' '.$perpage;
        }


        $sortingOrder = " ORDER BY $orderby $order ";


        return $wpdb->get_results("$sql_select  WHERE  $where_sql $sortingOrder  $limit_sql", 'ARRAY_A');//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }//end method getAllVotesByUser

    /**
     * Get all votes by different criteria
     *
     * @param  string  $orderby
     * @param  string  $order
     * @param  int  $perpage
     * @param  int  $page
     * @param  int  $poll_id
     * @param  string  $status
     * @param  int  $vote_id
     *
     * @return array|null|object
     */
    public static function getAllVotes(
        $orderby = 'id',
        $order = 'DESC',
        $perpage = 20,
        $page = 1,
        $poll_id = 0,
        $status = 'all',
        $vote_id = 0
    ) {
        $poll_id = absint($poll_id);
        $vote_id = absint($vote_id);

        $order = strtoupper($order);

        global $wpdb;
        $poll_vote_table = esc_sql(PollHelper::poll_table_name());


        $sql_select = "SELECT * FROM {$poll_vote_table}";

        $where_sql = '';
        if ($poll_id != 0) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('poll_id=%d', $poll_id);
        }

        if ($vote_id > 0) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('id=%d', $vote_id);
        }

        if (is_numeric($status)) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('published=%d', intval($status));
        }


        if ($where_sql == '') {
            $where_sql = '1';
        }

        $limit_sql = '';

        if ($perpage != -1) {
            $perpage     = intval($perpage);
            $start_point = ($page * $perpage) - $perpage;
            $limit_sql   .= "LIMIT";
            $limit_sql   .= ' '.$start_point.',';
            $limit_sql   .= ' '.$perpage;
        }


        $sortingOrder = " ORDER BY $orderby $order ";


        return $wpdb->get_results("$sql_select  WHERE  $where_sql $sortingOrder  $limit_sql", 'ARRAY_A');//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }//end method getAllVotes


    /**
     * Get total vote count based on multiple criteria
     *
     * @param  int  $poll_id
     * @param  string  $status
     * @param  int  $vote_id
     *
     * @return null|string
     */
    public static function getVoteCount($poll_id = 0, $status = 'all', $vote_id = 0)
    {

        $poll_id = intval($poll_id);
        $vote_id = intval($vote_id);

        global $wpdb;
        $poll_vote_table = PollHelper::poll_table_name();

        $sql_select = "SELECT COUNT(*) FROM {$poll_vote_table}";

        $where_sql = '';
        if ($poll_id != 0) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('poll_id=%d', $poll_id);
        }

        if ($vote_id > 0) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('id=%d', $vote_id);
        }

        if (is_numeric($status)) {
            if ($where_sql != '') {
                $where_sql .= ' AND ';
            }
            $where_sql .= $wpdb->prepare('published=%d', intval($status));
        }


        if ($where_sql == '') {
            $where_sql = '1';
        }


        return $wpdb->get_var("$sql_select  WHERE  $where_sql");//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }//end method getVoteCount

    /**
     * Get single vote information usign vote id
     *
     * @param $vote_id
     *
     * @return array|null|object|void
     */
    public static function getVoteInfo($vote_id)
    {
        global $wpdb;

        $poll_vote_table = esc_sql(PollHelper::poll_table_name());
        $sql             = $wpdb->prepare("SELECT * FROM {$poll_vote_table} WHERE id=%d ", absint($vote_id));//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return $wpdb->get_row($sql, ARRAY_A);//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }//end method getVoteInfo

    /**
     * Add utm params to any url
     *
     * @param  string  $url
     *
     * @return string
     */
    public static function url_utmy($url = '')
    {
        if ($url == '') {
            return $url;
        }

        $url = add_query_arg([
            'utm_source'   => 'plgsidebarinfo',
            'utm_medium'   => 'plgsidebar',
            'utm_campaign' => 'wpfreemium',
        ], $url);

        return $url;
    }//end url_utmy

    /**
     * Random color
     *
     * https://thisinterestsme.com/random-rgb-hex-color-php/
     *
     * @return string[]
     */
    public static function randomColor()
    {
        $result = ['rgb' => '', 'hex' => ''];
        foreach (['r', 'b', 'g'] as $col) {
            $rand = wp_rand(0, 255);
            //$result['rgb'][$col] = $rand;
            $dechex = dechex($rand);
            if (strlen($dechex) < 2) {
                $dechex = '0'.$dechex;
            }
            $result['hex'] .= $dechex;
        }

        return $result;
    }//end randomColor

    /**
     * Login form
     *
     * @return array
     * @since 1.2.4
     *
     */
    public static function guest_login_forms()
    {
        $forms = [];

        $forms['wordpress'] = esc_html__('WordPress Core Login Form', 'cbxpoll');
        $forms['none']      = esc_html__('Don\'t show login form, show default login url', 'cbxpoll');
        $forms['off']       = esc_html__('Show nothing!', 'cbxpoll');

        return apply_filters('cbxpoll_guest_login_forms', $forms);
    }//end guest_login_forms

    /**
     * Kses wysiwyg html
     *
     * @param  string  $html
     *
     * @return mixed|string
     *
     * @since 3.0.9
     *
     */
    public static function sanitize_wp_kses($html = '')
    {
        return wp_kses($html, PollHelper::allowedHtmlTags());
    }//end method sanitize_wp_kses

    /**
     * HTML elements, attributes, and attribute values will occur in your output
     *
     * @return array
     * @since 3.0.9
     *
     */
    public static function allowedHtmlTags()
    {
        $allowed_html_tags = [
            'a'      => [
                'href'  => [],
                'title' => [],
                //'class' => array(),
                //'data'  => array(),
                //'rel'   => array(),
            ],
            'br'     => [],
            'em'     => [],
            'ul'     => [//'class' => array(),
            ],
            'ol'     => [//'class' => array(),
            ],
            'li'     => [//'class' => array(),
            ],
            'strong' => [],
            'p'      => [
                //'class' => array(),
                //'data'  => array(),
                //'style' => array(),
            ],
            'span'   => [
                //					'class' => array(),
                //'style' => array(),
            ],
        ];

        return apply_filters('cbxpoll_allowed_html_tags', $allowed_html_tags);
    }//end method allowedHtmlTags

    /**
     * Get any plugin version number
     *
     * @param $plugin_slug
     *
     * @return mixed|string
     */
    public static function get_any_plugin_version($plugin_slug = '')
    {
        if ($plugin_slug == '') {
            return '';
        }

        // Ensure the required file is loaded
        if ( ! function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        // Get all installed plugins
        $all_plugins = get_plugins();

        // Check if the plugin exists
        if (isset($all_plugins[$plugin_slug])) {
            return $all_plugins[$plugin_slug]['Version'];
        }

        // Return false if the plugin is not found
        return '';
    }//end method get_pro_addon_version

    /**
     * Plugin reset html table
     *
     * @return string
     * @since 1.1.0
     *
     */
    public static function setting_reset_html_table()
    {
        $option_values = PollHelper::getAllOptionNames();
        $table_names   = PollHelper::getAllDBTablesList();

        $table_html = '<div id="cbxpoll_resetinfo"';
        $table_html .= '<p style="margin-bottom: 15px;" id="cbxpoll_plg_gfig_info"><strong>'.esc_html__('Following option values created by this plugin(including addon) from WordPress core option table',
                'cbxpoll').'</strong></p>';

        $table_html .= '<p style="margin-bottom: 10px;" class="grouped gapless grouped_buttons" id="cbxpoll_setting_options_check_actions"><a href="#" class="button primary cbxpoll_setting_options_check_action_call">'.esc_html__('Check All',
                'cbxpoll').'</a><a href="#" class="button outline cbxpoll_setting_options_check_action_ucall">'.esc_html__('Uncheck All',
                'cbxpoll').'</a></p>';

        $table_html .= '<table class="widefat widethin cbxpoll_table_data">
	<thead>
	<tr>
		<th class="row-title">'.esc_attr__('Option Name', 'cbxpoll').'</th>
		<th>'.esc_attr__('Option ID', 'cbxpoll').'</th>		
	</tr>
	</thead>';

        $table_html .= '<tbody>';

        $i = 0;
        foreach ($option_values as $key => $value) {
            $alternate_class = ($i % 2 == 0) ? 'alternate' : '';
            $i++;
            $table_html .= '<tr class="'.esc_attr($alternate_class).'">
									<td class="row-title"><input checked class="magic-checkbox reset_options" type="checkbox" name="reset_options['.$value['option_name'].']" id="reset_options_'.esc_attr($value['option_name']).'" value="'.$value['option_name'].'" />
  <label for="reset_options_'.esc_attr($value['option_name']).'">'.esc_attr($value['option_name']).'</td>
									<td>'.esc_attr($value['option_id']).'</td>									
								</tr>';
        }

        $table_html .= '</tbody>';
        $table_html .= '<tfoot>
	<tr>
		<th class="row-title">'.esc_attr__('Option Name', 'cbxpoll').'</th>
		<th>'.esc_attr__('Option ID', 'cbxpoll').'</th>				
	</tr>
	</tfoot>
</table>';

        if (sizeof($table_names) > 0):
            $table_html .= '<p style="margin-bottom: 15px;" id="cbxpoll_info"><strong>'.esc_html__('Following database tables will be reset/deleted and then re-created.',
                    'cbxpoll').'</strong></p>';

            $table_html .= '<table class="widefat widethin cbxpoll_table_data">
        <thead>
        <tr>
            <th class="row-title">'.esc_attr__('Table Name', 'cbxpoll').'</th>
            <th>'.esc_attr__('Table Name in DB', 'cbxpoll').'</th>		
        </tr>
        </thead>';

            $table_html .= '<tbody>';


            $i = 0;
            foreach ($table_names as $key => $value) {
                $alternate_class = ($i % 2 == 0) ? 'alternate' : '';
                $i++;
                $table_html .= '<tr class="'.esc_attr($alternate_class).'">
                                        <td class="row-title"><input checked class="magic-checkbox reset_tables" type="checkbox" name="reset_tables['.esc_attr($key).']" id="reset_tables_'.esc_attr($key).'" value="'.$value.'" />
  <label for="reset_tables_'.esc_attr($key).'">'.esc_attr($key).'</label></td>
                                        <td>'.esc_attr($value).'</td>									
                                    </tr>';
            }

            $table_html .= '</tbody>';
            $table_html .= '<tfoot>
        <tr>
            <th class="row-title">'.esc_attr__('Table Name', 'cbxpoll').'</th>
            <th>'.esc_attr__('Table Name in DB', 'cbxpoll').'</th>		
        </tr>
        </tfoot>
    </table>';

        endif;

        $table_html .= '</div>';

        return $table_html;
    }//end method setting_reset_html_table

    /**
     * Plugin sections
     *
     * @return mixed|null
     */
    public static function get_settings_sections()
    {
        $sections = [
            [
                'id'    => 'cbxpoll_global_settings',
                'title' => esc_html__('Poll Default Settings', 'cbxpoll')
            ],
            [
                'id'    => 'cbxpoll_slugs_settings',
                'title' => esc_html__('Urls & Slugs', 'cbxpoll')
            ],
            [
                'id'    => 'cbxpoll_email_tpl',
                'title' => esc_html__('Global Email Template', 'cbxpoll'),
            ],
            [
                'id'    => 'cbxpoll_tools',
                'title' => esc_html__('Tools', 'cbxpoll')
            ]
        ];

        return apply_filters('cbxpoll_setting_sections', $sections);
    }//end method get_settings_sections

    /**
     * Array of strings and properties for common js translation vars
     *
     * @return mixed|null
     */
    public static function cbxpoll_common_js_vars()
    {
        return apply_filters('cbxpoll_common_js_vars', [
            'ajaxurl'                  => admin_url('admin-ajax.php'),
            'ajax_fail'                => esc_html__('Request failed, please reload the page.', 'cbxpoll'),
            'nonce'                    => wp_create_nonce('cbxpollnonce'),
            'is_user_logged_in'        => is_user_logged_in() ? 1 : 0,
            'please_select'            => esc_html__('Please Select', 'cbxpoll'),
            'upload_title'             => esc_html__('Window Title', 'cbxpoll'),
            'copycmds'                 => [
                'copy'       => esc_html__('Copy', 'cbxpoll'),
                'copied'     => esc_html__('Copied', 'cbxpoll'),
                'copy_tip'   => esc_html__('Click to copy', 'cbxpoll'),
                'copied_tip' => esc_html__('Copied to clipboard', 'cbxpoll'),
            ],
            'placeholder'              => [
                'select' => esc_html__('Please Select', 'cbxpoll'),
                'search' => esc_html__('Search...', 'cbxpoll'),
            ],
            'delete_dialog'            => [
                'ok'                       => esc_attr_x('Ok', 'cbxpoll-dialog', 'cbxpoll'),
                'cancel'                   => esc_attr_x('Cancel', 'cbxpoll-dialog', 'cbxpoll'),
                'delete'                   => esc_attr_x('Delete', 'cbxpoll-dialog', 'cbxpoll'),
                'are_you_sure_global'      => esc_html__('Are you sure?', 'cbxpoll'),
                'are_you_sure_delete_desc' => esc_html__('Once you delete, it\'s gone forever. You can not revert it back.',
                    'cbxpoll'),
            ],
            'confirm_msg'              => esc_html__('Are you sure to remove this step?', 'cbxpoll'),
            'confirm_msg_all'          => esc_html__('Are you sure to remove all steps?', 'cbxpoll'),
            'confirm_yes'              => esc_html__('Yes', 'cbxpoll'),
            'confirm_no'               => esc_html__('No', 'cbxpoll'),
            'are_you_sure_global'      => esc_html__('Are you sure?', 'cbxpoll'),
            'are_you_sure_delete_desc' => esc_html__('Once you delete, it\'s gone forever. You can not revert it back.',
                'cbxpoll'),
            'pickr_i18n'               => [
                // Strings visible in the UI
                'ui:dialog'       => esc_html__('color picker dialog', 'cbxpoll'),
                'btn:toggle'      => esc_html__('toggle color picker dialog', 'cbxpoll'),
                'btn:swatch'      => esc_html__('color swatch', 'cbxpoll'),
                'btn:last-color'  => esc_html__('use previous color', 'cbxpoll'),
                'btn:save'        => esc_html__('Save', 'cbxpoll'),
                'btn:cancel'      => esc_html__('Cancel', 'cbxpoll'),
                'btn:clear'       => esc_html__('Clear', 'cbxpoll'),

                // Strings used for aria-labels
                'aria:btn:save'   => esc_html__('save and close', 'cbxpoll'),
                'aria:btn:cancel' => esc_html__('cancel and close', 'cbxpoll'),
                'aria:btn:clear'  => esc_html__('clear and close', 'cbxpoll'),
                'aria:input'      => esc_html__('color input field', 'cbxpoll'),
                'aria:palette'    => esc_html__('color selection area', 'cbxpoll'),
                'aria:hue'        => esc_html__('hue selection slider', 'cbxpoll'),
                'aria:opacity'    => esc_html__('selection slider', 'cbxpoll'),
            ],
            'awn_options'              => [
                'tip'           => esc_html__('Tip', 'cbxpoll'),
                'info'          => esc_html__('Info', 'cbxpoll'),
                'success'       => esc_html__('Success', 'cbxpoll'),
                'warning'       => esc_html__('Attention', 'cbxpoll'),
                'alert'         => esc_html__('Error', 'cbxpoll'),
                'async'         => esc_html__('Loading', 'cbxpoll'),
                'confirm'       => esc_html__('Confirmation', 'cbxpoll'),
                'confirmOk'     => esc_html__('OK', 'cbxpoll'),
                'confirmCancel' => esc_html__('Cancel', 'cbxpoll')
            ],
            'teeny_setting'            => [
                'teeny'         => true,
                'media_buttons' => true,
                'editor_class'  => '',
                'textarea_rows' => 5,
                'quicktags'     => false,
                'menubar'       => false,
            ],
            'lang'                     => get_user_locale()
        ]);
    }//end method cbxpoll_common_js_vars

    /**
     * Get user display name
     *
     * @param  null  $user_id
     *
     * @return string
     * @since 2.0.0
     */
    public static function userDisplayName($user_id = null)
    {
        $current_user      = $user_id ? new \WP_User($user_id) : wp_get_current_user();
        $user_display_name = $current_user->display_name;
        if ($user_display_name != '') {
            return $user_display_name;
        }

        if ($current_user->first_name) {
            if ($current_user->last_name) {
                return $current_user->first_name.' '.$current_user->last_name;
            }

            return $current_user->first_name;
        }

        return esc_html__('Unnamed', 'cbxpoll');
    }//end method userDisplayName

    /**
     * Get user display name alternative if display_name value is empty
     *
     * @param $current_user
     * @param $user_display_name
     *
     * @return string
     * @since 2.0.0
     */
    public static function userDisplayNameAlt($current_user, $user_display_name = '')
    {
        if ($user_display_name != '') {
            return $user_display_name;
        }

        if ($current_user->first_name) {
            if ($current_user->last_name) {
                return $current_user->first_name.' '.$current_user->last_name;
            }

            return $current_user->first_name;
        }

        return esc_html__('Unnamed', 'cbxpoll');
    }//end method userDisplayNameAlt

    /**
     * Returns codeboxr news feeds using transient cache
     *
     * @return false|mixed|\SimplePie\Item[]|null
     */
    public static function codeboxr_news_feed()
    {
        $cache_key   = 'codeboxr_news_feed_cache';
        $cached_feed = get_transient($cache_key);

        $news = false;

        if (false === $cached_feed) {
            include_once ABSPATH.WPINC.'/feed.php'; // Ensure feed functions are available
            $feed = fetch_feed('https://codeboxr.com/feed?post_type=post');

            if (is_wp_error($feed)) {
                return false; // Return false if there's an error
            }

            $feed->init();

            $feed->set_output_encoding('UTF-8');                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              // this is the encoding parameter, and can be left unchanged in almost every case
            $feed->handle_content_type();                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     // this double-checks the encoding type
            $feed->set_cache_duration(21600);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               // 21,600 seconds is six hours
            $limit  = $feed->get_item_quantity(10);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          // fetches the 18 most recent RSS feed stories
            $items  = $feed->get_items(0, $limit);
            $blocks = array_slice($items, 0, 10);

            $news = [];
            foreach ($blocks as $block) {
                $url   = $block->get_permalink();
                $url   = PollHelper::url_utmy(esc_url($url));
                $title = $block->get_title();

                $news[] = ['url' => $url, 'title' => $title];
            }

            set_transient($cache_key, $news, HOUR_IN_SECONDS * 6); // Cache for 6 hours
        } else {
            $news = $cached_feed;
        }

        return $news;
    }//end method codeboxr_news_feed

    /**
     * Load mailer
     *
     * @since 2.0.0
     */
    public static function load_mailer()
    {
        cbxpoll_mailer();
    }//end method load_mailer
}//end class PollHelper