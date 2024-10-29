<?php
/*
Plugin Name: AccessAlly™ LMS Migration from WP Courseware®
Description: This AccessAlly™ LMS Migration from WP Courseware® plugin will convert your existing WP Courseware® courses into AccessAlly courses, so you don't lose your content when you disable WP Courseware®.
Plugin URI: https://accessally.com/
Author URI: https://accessally.com/about/
Author: AccessAlly
Tags: lms, lms migration, WP Courseware® migration, accessally migration, export WP Courseware, migrate lms, switch lms, export lms, import lms, access ally, accessally
Tested up to: 5.5.3
Requires at least: 4.7.0
Requires PHP: 5.6
Version: 1.0.1
Stable tag: 1.0.1
License: Artistic License 2.0
 */

if (!class_exists('AccessAlly_WpCoursewareConversion')) {
	class AccessAlly_WpCoursewareConversion {
		/// CONSTANTS
		const VERSION = '1.0.0';
		const SETTING_KEY = '_accessally_wp_courseware_conversion';
		private static $PLUGIN_URI = '';

		public static function init() {
			self::$PLUGIN_URI = plugin_dir_url(__FILE__);
			if (is_admin()) {
				add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_administrative_resources'));
				add_action('admin_menu', array(__CLASS__, 'add_menu_pages'));
			}
			add_action('wp_ajax_accessally_wp_courseware_convert', array(__CLASS__, 'convert_callback'));
			add_action('wp_ajax_accessally_wp_courseware_revert', array(__CLASS__, 'revert_callback'));

			register_activation_hook(__FILE__, array(__CLASS__, 'do_activation_actions'));
			register_deactivation_hook(__FILE__, array(__CLASS__, 'do_deactivation_actions'));
		}
		public static function do_activation_actions() {
			delete_transient(self::SETTING_KEY);
			wp_cache_flush();
		}
		public static function do_deactivation_actions() {
			delete_transient(self::SETTING_KEY);
			wp_cache_flush();
		}
		public static function enqueue_administrative_resources($hook){
			if (strpos($hook, self::SETTING_KEY) !== false) {
				wp_enqueue_style('accessally-wp-courseware-convert-backend', self::$PLUGIN_URI . 'backend/settings.css', false, self::VERSION);
				wp_enqueue_script('accessally-wp-courseware-convert-backend', self::$PLUGIN_URI . 'backend/settings.js', array('jquery'), self::VERSION);

				// do not include the http or https protocol in the ajax url
				$admin_url = preg_replace("/^http:/i", "", admin_url('admin-ajax.php'));
				$admin_url = preg_replace("/^https:/i", "", $admin_url);

				wp_localize_script('accessally-wp-courseware-convert-backend', 'accessally_wp_courseware_convert_object',
					array('ajax_url' => $admin_url,
						'nonce' => wp_create_nonce('accessally-wp-courseware-convert')
						));
			}
		}
		public static function add_menu_pages() {
			// Add the top-level admin menu
			$capability = 'manage_options';
			$menu_slug = self::SETTING_KEY;
			$results = add_menu_page('AccessAlly WP Courseware Conversion', 'AccessAlly WP Courseware Conversion', $capability, $menu_slug, array(__CLASS__, 'show_settings'), self::$PLUGIN_URI . 'backend/icon.png');
		}
		public static function show_settings() {
			if (!current_user_can('manage_options')) {
				wp_die('You do not have sufficient permissions to access this page.');
			}
			if (!self::is_accessally_active()) {
				wp_die('AccessAlly is not activated or outdated. Please install the latest version of AccessAlly before using the conversion tool.');
			}
			$operation_code = self::generate_setting_display();
			include (dirname(__FILE__) . '/backend/settings-display.php');
		}

		// <editor-fold defaultstate="collapsed" desc="utility function for checking AccessAlly dependencies">
		private static function is_accessally_active() {
			if (!class_exists('AccessAlly') || !class_exists('AccessAllySettingLicense') || !AccessAllySettingLicense::$accessally_enabled ||
				!class_exists('AccessAllyWizardProduct') || !method_exists('AccessAllyWizardProduct', 'merge_default_settings') ||
				!class_exists('AccessAllyWizardDrip') || !method_exists('AccessAllyWizardDrip', 'merge_default_settings')) {
				return false;
			}
			return true;
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="retrieve database info">
		const WP_COURSEWARE_UNIT_SLUG = 'course_unit';
		private static $default_settings = array('wizard' => array());
		private static function get_wp_courseware_courses() {
			global $wpdb;
			$wpcw_course_table_name = $wpdb->prefix . 'wpcw_courses';
			$wpcw_module_table_name = $wpdb->prefix . 'wpcw_modules';
			$wpcw_unit_table_name = $wpdb->prefix . 'wpcw_units_meta';

			$unit_rows = $wpdb->get_results("SELECT unit_id, parent_module_id, parent_course_id, unit_order FROM $wpcw_unit_table_name", OBJECT_K);

			$unit_meta = array();
			foreach ($unit_rows as $unit_row) {
				$unit_meta[$unit_row->unit_id] = $unit_row;
			}

			$all_custom_posts = $wpdb->get_results("SELECT ID, post_type, post_title FROM $wpdb->posts WHERE post_type = '" . self::WP_COURSEWARE_UNIT_SLUG. "'", OBJECT_K);
			$custom_post_mapping = array();
			foreach ($all_custom_posts as $wpcw_post) {
				if (isset($unit_meta[$wpcw_post->ID])) {
					$custom_post_mapping[$wpcw_post->ID] = array('post' => $wpcw_post, 'meta' => $unit_meta[$wpcw_post->ID]);
				}
			}

			$course_rows = $wpdb->get_results("SELECT course_id, course_title FROM $wpcw_course_table_name", OBJECT_K);
			$courses = array();
			foreach ($course_rows as $course_row) {
				$courses[$course_row->course_id] = array('raw' => $course_row, 'modules' => array(), 'module-order' => array());
			}

			$module_rows = $wpdb->get_results("SELECT module_id, parent_course_id, module_title, module_order FROM $wpcw_module_table_name", OBJECT_K);
			foreach ($module_rows as $module_row) {
				$parent_course_id = $module_row->parent_course_id;
				if (isset($courses[$parent_course_id])) {
					$courses[$parent_course_id]['modules'][$module_row->module_id] = array('raw' => $module_row, 'units' => array(), 'unit-order' => array());
					$courses[$parent_course_id]['module-order'][$module_row->module_id] = $module_row->module_order;
				}
			}
			foreach ($custom_post_mapping as $post_id => $post_details) {
				$parent_course_id = $post_details['meta']->parent_course_id;
				$parent_module_id = $post_details['meta']->parent_module_id;
				if (isset($courses[$parent_course_id])) {
					$parent_modules = $courses[$parent_course_id]['modules'];
					if (isset($parent_modules[$parent_module_id])) {
						$courses[$parent_course_id]['modules'][$parent_module_id]['units'][$post_id] = $post_details;
						$courses[$parent_course_id]['modules'][$parent_module_id]['unit-order'][$post_id] = $post_details['meta']->unit_order;
					}
				}
			}
			foreach ($courses as $course_id => $course_details) {
				asort($courses[$course_id]['module-order']);
				foreach ($course_details['modules'] as $module_id => $module_details) {
					asort($courses[$course_id]['modules'][$module_id]['unit-order']);
				}
			}
			return $courses;
		}
		public static function get_settings() {
			$setting = get_option(self::SETTING_KEY, false);
			if (!is_array($setting)) {
				$setting = self::$default_settings;
			} else {
				$setting = wp_parse_args($setting, self::$default_settings);
			}
			if (!isset($setting['wizard']) || !is_array($setting['wizard'])) {
				$setting['wizard'] = array();
			}

			return $setting;
		}
		public static function set_settings($settings) {
			$settings = wp_parse_args($settings, self::$default_settings);
			$successfully_added = add_option(self::SETTING_KEY, $settings, '', 'no');
			if (!$successfully_added) {
				update_option(self::SETTING_KEY, $settings);
			}
			return $settings;
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="generate display code (used for initial display and ajax call back)">
		private static function generate_wp_courseware_module_display($module_details) {
			$module_db_entry = $module_details['raw'];
			$code = '- Module: ' . esc_html($module_db_entry->module_title);
			$code .= '<ul>';

			foreach ($module_details['unit-order'] as $unit_id => $unit_order) {
				if (isset($module_details['units'][$unit_id])) {
					$unit_details = $module_details['units'][$unit_id];
					$unit_post = $unit_details['post'];
					$code .= '<li>';
					$code .= '- Unit: ' . esc_html($unit_post->post_title);
					$code .= '</li>';
				}
			}
			$code .= '</ul>';
			return $code;
		}
		private static function generate_wp_courseware_course_display($code, $course_details) {
			$course_db_entry = $course_details['raw'];

			$code = str_replace('{{id}}', esc_html($course_db_entry->course_id), $code);
			$course_edit_link = admin_url('admin.php?page=WPCW_showPage_ModifyCourse&course_id=' . $course_db_entry->course_id);
			$code = str_replace('{{edit-link}}',esc_attr($course_edit_link), $code);
			$code = str_replace('{{name}}', esc_html($course_db_entry->course_title), $code);

			$details = '<ul>';
			foreach ($course_details['module-order'] as $module_id => $module_order) {
				if (isset($course_details['modules'][$module_id])) {
					$module_details = $course_details['modules'][$module_id];
					$details .= '<li>';
					$details .= self::generate_wp_courseware_module_display($module_details);
					$details .= '</li>';
				}
			}
			$details .= '</ul>';

			$code = str_replace('{{details}}', $details, $code);

			return $code;
		}
		private static function generate_converted_course_display($row_code, $course_id, $wizard_course, $wizard_url_base) {
			$row_code = str_replace('{{name}}', esc_html($wizard_course['name']), $row_code);
			if (empty($wizard_course['type'])) {
				$row_code = str_replace('{{edit-link}}', '#', $row_code);
				$row_code = str_replace('{{show-edit}}', 'style="display:none"', $row_code);
			} else {
				$row_code = str_replace('{{edit-link}}', esc_attr($wizard_url_base . '&show-' . $wizard_course['type'] . '=' . $wizard_course['option-key']), $row_code);
				$row_code = str_replace('{{show-edit}}', '', $row_code);
			}
			$row_code = str_replace('{{course-id}}', esc_html($course_id), $row_code);
			return $row_code;
		}
		public static function generate_setting_display() {
			$code = file_get_contents(dirname(__FILE__) . '/backend/settings-template.php');

			$converted_posts = self::get_settings();
			$wp_courseware_courses = self::get_wp_courseware_courses();
			$wp_courseware_code = '';
			$wp_courseware_template = file_get_contents(dirname(__FILE__) . '/backend/convert-template.php');
			foreach ($wp_courseware_courses as $course_id => $course_details) {
				if (!isset($converted_posts['wizard'][$course_id])) {	// do not show already converted courses
					$wp_courseware_code .= self::generate_wp_courseware_course_display($wp_courseware_template, $course_details);
				}
			}
			$code = str_replace('{{wp-courseware-courses}}', $wp_courseware_code, $code);

			$existing_courses = '';
			if (!empty($converted_posts['wizard'])) {
				$existing_row_template = file_get_contents(dirname(__FILE__) . '/backend/existing-template.php');
				$wizard_url_base = admin_url('admin.php?page=' . AccessAllyWizardShared::SETTING_KEY_WIZARD);
				foreach ($converted_posts['wizard'] as $course_id => $wizard_course) {
					$existing_courses .= self::generate_converted_course_display($existing_row_template, $course_id, $wizard_course, $wizard_url_base);
				}
			}

			$code = str_replace('{{existing-courses}}', $existing_courses, $code);

			if (!empty($existing_courses)) {
				$code = str_replace('{{show-existing}}', '', $code);
			} else {
				$code = str_replace('{{show-existing}}', 'style="display:none"', $code);
			}

			return $code;
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="Create AccessAlly standalone course structure">
		private static $page_setting_template = array('type' => 'page', 'name' => '', 'is-changed' => 'no', 'page-template-select' => '0', 'checked-existing' => 'no',
			'status' => 'new', 'post-edit-link' => '#', 'post-id' => 0,
			'success-message' => '', 'error-message' => '');
		private static function create_new_accessally_wizard_page_with_title($page_title, $module_id = 0) {
			$result_page = self::$page_setting_template;
			$result_page['name'] = $page_title;
			$result_page['is-changed'] = 'yes';
			$result_page['checked-existing'] = 'no';
			$result_page['module'] = $module_id;
			return $result_page;
		}
		private static function create_accessally_wizard_page_from_raw_db($db_entry, $module_id = 0) {
			$result_page = self::$page_setting_template;
			$result_page['name'] = $db_entry->post_title;
			$result_page['is-changed'] = 'yes';
			$result_page['page-template-select'] = $db_entry->ID;
			$result_page['checked-existing'] = 'yes';
			$result_page['module'] = $module_id;
			return $result_page;
		}
		private static function create_accessally_standalone_course($course_details) {
			$wizard_data = AccessAllyWizardProduct::$default_product_settings;
			$wizard_data['name'] = 'WP Courseware Course: ' . $course_details['raw']->course_title;

			$api_settings = AccessAllySettingSetup::get_api_settings();
			$wizard_data['system'] = $api_settings['system'];

			$wizard_data['pages'][0] = self::create_new_accessally_wizard_page_with_title($course_details['raw']->course_title, 0);

			if (!empty($course_details['modules'])) {
				foreach ($course_details['module-order'] as $module_id => $module_order) {
					if (isset($course_details['modules'][$module_id])) {
						$module_details = $course_details['modules'][$module_id];

						foreach ($module_details['unit-order'] as $unit_id => $unit_order) {
							if (isset($module_details['units'][$unit_id])) {
								$unit_details = $module_details['units'][$unit_id];
								$wizard_data['pages'] []= self::create_accessally_wizard_page_from_raw_db($unit_details['post'], 0);
							}
						}
					}
				}
			}

			$wizard_data = AccessAllyWizardProduct::merge_default_settings($wizard_data);

			$wizard_data = AccessAllyUtilities::set_incrementing_settings(AccessAllyWizardProduct::SETTING_KEY_WIZARD_PRODUCT,
				AccessAllyWizardProduct::SETTING_KEY_WIZARD_PRODUCT_NUMBER, $wizard_data, AccessAllyWizardProduct::$default_product_settings, true, false);
			return $wizard_data;
		}
		private static function create_accessally_stage_release_course($course_details) {
			$wizard_data = AccessAllyWizardDrip::$default_drip_settings;
			$wizard_data['name'] = 'WP Courseware Course: ' . $course_details['raw']->course_title;

			$api_settings = AccessAllySettingSetup::get_api_settings();
			$wizard_data['system'] = $api_settings['system'];

			$wizard_data['pages'][0] = self::create_new_accessally_wizard_page_with_title($course_details['raw']->course_title, 0);

			$module_count = 0;
			if (!empty($course_details['modules'])) {
				foreach ($course_details['module-order'] as $module_id => $module_order) {
					if (isset($course_details['modules'][$module_id])) {
						$module_details = $course_details['modules'][$module_id];
						++$module_count;
						$module_wizard_data = AccessAllyWizardDrip::$default_module_settings;
						$module_wizard_data['name'] = $module_details['raw']->module_title;
						$wizard_data['modules'][$module_count] = $module_wizard_data;

						foreach ($module_details['unit-order'] as $unit_id => $unit_order) {
							if (isset($module_details['units'][$unit_id])) {
								$unit_details = $module_details['units'][$unit_id];
								$wizard_data['pages'] []= self::create_accessally_wizard_page_from_raw_db($unit_details['post'], $module_count);
							}
						}
					}
				}
			}

			$wizard_data = AccessAllyWizardDrip::merge_default_settings($wizard_data);
			$wizard_data = AccessAllyUtilities::set_incrementing_settings(AccessAllyWizardDrip::SETTING_KEY_WIZARD_DRIP,
				AccessAllyWizardDrip::SETTING_KEY_WIZARD_DRIP_NUMBER, $wizard_data, AccessAllyWizardDrip::$default_drip_settings, true, false);
			return $wizard_data;
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="database post type update">
		private static function get_custom_post_to_convert($course_details) {
			$result_array = array();
			if (!empty($course_details['modules'])) {
				foreach ($course_details['module-order'] as $module_id => $module_order) {
					if (isset($course_details['modules'][$module_id])) {
						$module_details = $course_details['modules'][$module_id];

						foreach ($module_details['unit-order'] as $unit_id => $unit_order) {
							if (isset($module_details['units'][$unit_id])) {
								$unit_details = $module_details['units'][$unit_id];
								$result_array []= $unit_details['post']->ID;
							}
						}
					}
				}
			}
			return $result_array;
		}
		private static function raw_database_update($post_ids, $target_type) {
			if (empty($post_ids)) {
				return 0;
			}
			global $wpdb;

			$query = $wpdb->prepare("UPDATE {$wpdb->posts} SET post_type = %s WHERE ID in (" . implode(',', $post_ids) . ")", $target_type);
			$update_result = $wpdb->query($query);
			if (false === $update_result && $wpdb->last_error) {
				throw new Exception($wpdb->last_error);
			}
			return $update_result;
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="Ajax callbacks: convert / revert WP courseware to page">
		public static function convert_callback() {
			$result = array('status' => 'error', 'message' => 'Unknown error. Please refresh the page and try again.');
			try {
				if (!self::is_accessally_active()) {
					throw new Exception('AccessAlly is not activated or outdated. Please install the latest version of AccessAlly before using the conversion tool.');
				}
				if (!isset($_REQUEST['id']) || !isset($_REQUEST['op']) || !isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'accessally-wp-courseware-convert')) {
					throw new Exception('The page is outdated. Please refresh and try again.');
				}
				$course_id = sanitize_text_field($_REQUEST['id']);
				$operation = sanitize_text_field($_REQUEST['op']);
				if ('alone' !== $operation && 'stage' !== $operation && 'wp' !== $operation) {
					throw new Exception('Invalid convert operation. Please refresh and try again.');
				}
				$wp_courseware_courses = self::get_wp_courseware_courses();

				if (!isset($wp_courseware_courses[$course_id])) {
					throw new Exception('The WP Courseware course doesn\'t exist. Please refresh and try again.');
				}
				$course_details = $wp_courseware_courses[$course_id];

				$course_db_entry = $course_details['raw'];
				$course_name = $course_db_entry->course_title;

				$conversion_data = array('name' => $course_name);	// assign default value if the course is converted without creating a wizard course
				if ('stage' === $operation) {
					$created_course = self::create_accessally_stage_release_course($course_details);
					$conversion_data = array('type' => 'stage', 'option-key' => $created_course['option-key'], 'name' => $created_course['name']);
				} elseif ('alone' === $operation) {
					$created_course = self::create_accessally_standalone_course($course_details);
					$conversion_data = array('type' => 'alone', 'option-key' => $created_course['option-key'], 'name' => $created_course['name']);
				}
				$pages_to_convert = self::get_custom_post_to_convert($course_details);

				self::raw_database_update($pages_to_convert, 'page');

				$conversion_data['converted'] = $pages_to_convert;

				$conversion_history = self::get_settings();
				$conversion_history['wizard'][$course_id] = $conversion_data;
				self::set_settings($conversion_history);

				$code = self::generate_setting_display();
				$result = array('status' => 'success', 'message' => 'The WP Courseware Course has been converted.', 'code' => $code);
			} catch (Exception $e) {
				$result['status'] = 'error';
				$result['message'] = $e->getMessage() . ' Please refresh the page and try again.';
			}
			echo json_encode($result);
			die();
		}
		public static function revert_callback() {
			$result = array('status' => 'error', 'message' => 'Unknown error. Please refresh the page and try again.');
			try {
				if (!self::is_accessally_active()) {
					throw new Exception('AccessAlly is not activated or outdated. Please install the latest version of AccessAlly before using the conversion tool.');
				}
				if (!isset($_REQUEST['id']) || !isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'accessally-wp-courseware-convert')) {
					throw new Exception('The page is outdated. Please refresh and try again.');
				}
				$course_id = sanitize_text_field($_REQUEST['id']);
				$conversion_history = self::get_settings();
				if (!isset($conversion_history['wizard'][$course_id])) {
					throw new Exception('Invalid course. Please refresh and try again.');
				}
				$converted_data = $conversion_history['wizard'][$course_id];
				$converted_pages = $converted_data['converted'];
				self::raw_database_update($converted_pages, self::WP_COURSEWARE_UNIT_SLUG);

				unset($conversion_history['wizard'][$course_id]);
				self::set_settings($conversion_history);

				$code = self::generate_setting_display();
				$result = array('status' => 'success', 'message' => 'Reverting pages to WP Courseware format completed.', 'code' => $code);
			} catch (Exception $e) {
				$result['status'] = 'error';
				$result['message'] = $e->getMessage() . ' Please refresh the page and try again.';
			}
			echo json_encode($result);
			die();
		}
		// </editor-fold>
	}
	AccessAlly_WpCoursewareConversion::init();
}
