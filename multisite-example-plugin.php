<?php
/**
 * Plugin Name:       Multisite Example Plugin
 * Plugin URI:        https://dlxplugins.com
 * Description:       Sample Multisite Snippets.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            DLX Plugins
 * Author URI:        https://dlxplugins.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       multisite-example-plugin
 * Domain Path:       /languages
 * Network: false
 */

define( 'DLX_MULTISITE_EXAMPLES_TABLE_VERSION', '1.0.1' );

/**
 * Contains various recipes for Multisite.
 */
class DLX_Multisite_Examples {

	/**
	 * Holds the slug for the plugin.
	 *
	 * @var string $slug Slug for the plugin.
	 */
	private static $slug = 'multisite-example-plugin';

	/**
	 * Init all the things.
	 */
	public static function run() {

	}
	/**
	 * Checks if the plugin is on a multisite install.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $network_admin Check if in network admin.
	 *
	 * @return true if multisite, false if not.
	 */
	public static function is_multisite( $network_admin = false ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$is_network_admin = false;
		if ( $network_admin ) {
			if ( is_network_admin() ) {
				if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
					return true;
				}
			} else {
				return false;
			}
		}
		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks to see if an asset is activated or not.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path to the asset (e.g., akismet/akismet.php).
	 * @param string $type Type to check if it is activated or not.
	 *
	 * @return bool true if activated, false if not.
	 */
	public static function is_activated( $path, $type = 'plugin' ) {

		// Gets all active plugins on the current site.
		$active_plugins = self::is_multisite() ? get_site_option( 'active_sitewide_plugins' ) : get_option( 'active_plugins', array() );
		if ( in_array( $path, $active_plugins, true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Plugin setting link initialization.
	 */
	public static function init_plugin_setting_links() {
		if ( self::is_multisite() ) {
			add_filter( 'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ), 'DLX_Multisite_Examples::add_plugin_actions_links_multisite', 5 );
		} else {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'DLX_Multisite_Examples::add_plugin_actions_links', 5 );
		}
	}

	/**
	 * Add plugin actions to the Network Admin plugins screen.
	 *
	 * @param array $links Array of setting actions.
	 *
	 * @return array $links Updated array of setting actions.
	 */
	public static function add_plugin_actions_links_multisite( $links ) {
		// Assumes you are placing the admin menu under the Multisite settings menu item.
		$admin_uri = add_query_arg( array( 'page' => self::$slug ), network_admin_url( 'settings.php' ) );
		array_push( $links, sprintf( '<a href="%s">%s</a>', esc_url( $admin_uri ), esc_html__( 'Settings', 'multisite-example-pluging' ) ) );
		return $links;
	}

	/**
	 * Add plugin actions to single-site or subsite plugin's screen.
	 *
	 * @param array $links Array of setting actions.
	 *
	 * @return array $links Updated array of setting actions.
	 */
	public static function add_plugin_actions_links( $links ) {
		// Assume plugin is on single-site or a subsite.
		// Assumes settings screen is under Settings Options General on a single-site.
		$admin_uri = add_query_arg( array( 'page' => 'your-plugin-slug' ), admin_url( 'options-general.php' ) );
		array_push( $links, sprintf( '<a href="%s">%s</a>', esc_url( $admin_uri ), esc_html__( 'Settings', 'your-plugin-slug' ) ) );
		return $links;
	}

	/**
	 * Hook into content filter and display site links.
	 *
	 * @param string $content Post content.
	 *
	 * @return string Modified $content.
	 */
	public static function site_vs_get_site( $content ) {
		ob_start();
		?>
		<p>site_url: <?php echo esc_url( site_url() ); ?></p>
		<p>get_site_url: <?php echo esc_url( get_site_url( 1 ) ); ?></p>
		<?php
		return $content . ob_get_clean();
	}

	/**
	 * The difference between the admin conditionals on subsite and network.
	 *
	 */
	public static function is_admin_vs_is_network_admin() {
		if ( is_admin() && is_network_admin() ) {
			// die( 'is_admin and is_network_admin are true' );
		}
		if ( is_admin() && ! is_network_admin() ) {
			// die( 'is_admin is true, is_network_admin is not true' );
		}
		if ( is_network_admin() ) {
			// This should only trigger in the Network Admin area.
			// die( 'is_network_admin is true' );
		}
		// This should be true if you are on a subsite or Network Admin area.
		if ( is_admin() ) {
			// die( 'is_admin is true' );
		}
	}

	/**
	 * Hook into content filter and display site links.
	 *
	 * @param string $content Post content.
	 *
	 * @return string Modified $content.
	 */
	public static function home_vs_get_home( $content ) {
		ob_start();
		?>
		<p>home_url: <?php echo esc_url( home_url() ); ?></p>
		<p>get_home_url: <?php echo esc_url( get_home_url( 1 ) ); ?></p>
		<?php
		return $content . ob_get_clean();
	}

	/**
	 * Initialize plugin settings menu for Multisite or single site.
	 */
	public static function settings_init() {
		if ( self::is_multisite() ) {
			add_action( 'network_admin_menu', 'DLX_Multisite_Examples::register_admin_menu' );
		} else {
			add_action( 'admin_menu', 'DLX_Multisite_Examples::register_admin_menu' );
		}

		// self::create_table_subsites(); // be sure to up the table version to test.
		//self::create_table_network(); // be sure to up the table version to test.
	}

	/**
	 * Create our database table.
	 */
	public static function create_table_network() {
		global $wpdb;
		$tablename = $wpdb->base_prefix . 'multisite_example_network';

		$version = get_site_option( 'multisite_example_table_version', '0' );
		if ( version_compare( $version, DLX_MULTISITE_EXAMPLES_TABLE_VERSION ) < 0 ) {
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
			$sql = "CREATE TABLE {$tablename} (
							id BIGINT(20) NOT NULL AUTO_INCREMENT,
							user_id BIGINT(20) NOT NULL DEFAULT 0,
							slug text NOT NULL,
							label text NOT NULL,
							icon text NOT NULL,
							url text NOT NULL,
							date DATETIME NOT NULL,
							item_order BIGINT(20) NOT NULL DEFAULT 0,
							PRIMARY KEY  (id)
							) {$charset_collate};";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_site_option( 'multisite_example_table_version', DLX_MULTISITE_EXAMPLES_TABLE_VERSION );
		}
	}

	/**
	 * Create our database table.
	 */
	public static function create_table_subsites() {
		global $wpdb;
		$tablename = $wpdb->prefix . 'multisite_example';

		$version = get_option( 'multisite_example_table_version', '0' );
		if ( version_compare( $version, DLX_MULTISITE_EXAMPLES_TABLE_VERSION ) < 0 ) {
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
			$sql = "CREATE TABLE {$tablename} (
							id BIGINT(20) NOT NULL AUTO_INCREMENT,
							user_id BIGINT(20) NOT NULL DEFAULT 0,
							slug text NOT NULL,
							label text NOT NULL,
							icon text NOT NULL,
							url text NOT NULL,
							date DATETIME NOT NULL,
							item_order BIGINT(20) NOT NULL DEFAULT 0,
							PRIMARY KEY  (id)
							) {$charset_collate};";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( 'multisite_example_table_version', DLX_MULTISITE_EXAMPLES_TABLE_VERSION );
		}
	}

	/**
	 * Initialize the sub menu.
	 */
	public static function register_admin_menu() {
		$pagename     = self::is_multisite() ? 'settings.php' : 'tools.php';
		$capabilities = self::is_multisite() ? 'manage_network' : 'manage_options';

		add_submenu_page(
			$pagename,
			'Multisite Example Plugin',
			'MS Example',
			$capabilities,
			'multisite-example-plugin',
			'DLX_Multisite_Examples::settings_page'
		);
	}

	/**
	 * Output some admin panel code.
	 */
	public static function settings_page() {
		?>
		<div class="wrap">Hi, I am an admin placeholder.</div>
		<?php
	}
}



// Initialization.
add_action(
	'plugins_loaded',
	function() {
		add_action( 'init', 'DLX_Multisite_Examples::settings_init', 9 );
		// Init plugin setting/actions links.
		add_action( 'admin_init', 'DLX_Multisite_Examples::init_plugin_setting_links', 10 );

		// is_admin, is_network_admin test.
		add_action( 'admin_init', 'DLX_Multisite_Examples::is_admin_vs_is_network_admin', 10 );
		// Output site_url vs get_site_url.
		// add_filter( 'the_content', 'DLX_Multisite_Examples::site_vs_get_site' );
		// add_filter( 'the_content', 'DLX_Multisite_Examples::home_vs_get_home', 11 );
	}
);
