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
 * Network: true
 */

namespace DLXPlugins\multisite;

/**
 * Contains various recipes for Multisite.
 */
class Multisite_Examples {

	/**
	 * Holds the slug for the plugin.
	 *
	 * @var string $slug Slug for the plugin.
	 */
	private static $slug = '#multisite-example-plugin';
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
	 * @param string $path Path to the asset.
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
			add_filter( 'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ), array( static::class, 'add_plugin_actions_links_multisite' ), 5 );
		} else {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( static::class, 'add_plugin_actions_links' ), 5 );
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
		$admin_uri = add_query_arg( array( 'page' => $slug ), network_admin_url( 'settings.php' ) );
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
}

// Initialization.
add_action(
	'plugins_loaded',
	function() {
		add_aciton( 'admin_init', array( 'Multisite_Examples', 'init_plugin_setting_links' ) );
	}
);
