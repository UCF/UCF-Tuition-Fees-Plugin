<?php
/**
 * Class that handles config options
 **/
if ( ! class_exists( 'UCF_Tuition_Fees_Config' ) ) {
    class UCF_Tuition_Fees_Config {
        public static
            $option_prefix   = 'ucf_tuition_fees_',
            $option_defaults = array(
				'base_feed_url'            => 'https://finacctg.fa.ucf.edu/sas/feed/feed.cfm',
				'degree_plan_code_name'    => 'degree_plan_code',
				'degree_subplan_code_name' => 'degree_subplan_code',
				'include_css'              => false,
				'cache_results'            => false,
				'transient_expiration'     => 3, // hours
				'degree_online_meta_field' => 'degree_online'
            );

        /**
		 * Creates options via the WP Options API that are utilized by the
		 * plugin. Intended to be run on plugin activation.
		 *
		 * @author Jim Barnes, Jo Dickson
		 * @since 1.0.0
		 **/
        public static function add_options() {
            $defaults = self::$option_defaults;

			add_option( self::$option_prefix . 'base_feed_url', $defaults['base_feed_url'] );
			add_option( self::$option_prefix . 'degree_plan_code_name', $defaults['degree_plan_code_name'] );
			add_option( self::$option_prefix . 'degree_subplan_code_name', $defaults['degree_subplan_code_name'] );
			add_option( self::$option_prefix . 'degree_online_meta_field', $defaults['degree_online_meta_field'] );
			add_option( self::$option_prefix . 'include_css', $defaults['include_css'] );
			add_option( self::$option_prefix . 'cache_results', $defaults['cache_results'] );
			add_option( self::$option_prefix . 'transient_expiration', $defaults['transient_expiration'] );
        }

        /**
		 * Deletes options via the WP Options API that are utilized by the
		 * plugin. Intented to be run on plugin deactivation.
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function delete_options() {
			delete_option( self::$option_prefix . 'base_feed_url' );
			delete_option( self::$option_prefix . 'degree_plan_code_name' );
			delete_option( self::$option_prefix . 'degree_subplan_code_name' );
			delete_option( self::$option_prefix . 'degree_online_meta_field' );
			delete_option( self::$option_prefix . 'include_css' );
			delete_option( self::$option_prefix . 'cache_results' );
			delete_option( self::$option_prefix . 'transient_expiration' );
		}

        /**
		 * Returns a list of default plugin options. Applies any overridden
		 * default values set within the options page.
		 *
		 * @author Jim Barnes, Jo Dickson
		 * @since 1.0.0
		 *
		 * @return array
		 **/
		public static function get_option_defaults() {
			$defaults = self::$option_defaults;
			$configurable_defaults = array(
				'base_feed_url'            => get_option( self::$option_prefix . 'base_feed_url' ),
				'degree_plan_code_name'    => get_option( self::$option_prefix . 'degree_plan_code_name' ),
				'degree_subplan_code_name' => get_option( self::$option_prefix . 'degree_subplan_code_name' ),
				'degree_online_meta_field' => get_option( self::$option_prefix . 'degree_online_meta_field' ),
				'include_css'              => get_option( self::$option_prefix . 'include_css' ),
				'cache_results'            => get_option( self::$option_prefix . 'cache_results' ),
				'transient_expiration'     => get_option( self::$option_prefix . 'transient_expiration' )
			);
			$configurable_defaults = self::format_options( $configurable_defaults );
			$default = array_merge( $defaults, $configurable_defaults );
			return $defaults;
		}

		/**
		 * Returns an array with plugin defaults applied.
		 *
		 * @author Jo Dickson
		 * @since 1.0.0
		 *
		 * @param array $list
		 * @param boolean $list_keys_only Modifies results to only return array key
		 *                                values present in $list.
		 * @return array
		 **/
		public static function apply_option_defaults( $list, $list_keys_only=false ) {
			$defaults = self::get_option_defaults();
			$options = array();
			if ( $list_keys_only ) {
				foreach( $list as $key => $val ) {
					$options[$key] = !empty( $val ) ? $val : $defaults[$key];
				}
			} else {
				$options = array_merge( $defaults, $list );
			}
			$options = self::format_options( $options );
			return $options;
		}

        /**
		 * Performs typecasting, sanitization, etc on an array of plugin options.
		 *
		 * @author Jim Barnes, Jo Dickson
		 * @since 1.0.0
		 *
		 * @param array $list
		 * @return array
		 **/
		public static function format_options( $list ) {
			foreach( $list as $key => $val ) {
				switch( $key ) {
					case 'include_css':
					case 'cache_results':
						$list[$key] = filter_var( $val, FILTER_VALIDATE_BOOLEAN );
						break;
					case 'transient_expiration':
						$list[$key] = floatval( $val );
						break;
					default:
						break;
				}
			}

			return $list;
		}

        /**
		 * Convenience method for returning an option from the WP Options API
		 * or a plugin option default.
		 * @author Jo Dickson
		 * @since 1.0.0
		 *
		 * @param $option_name
		 * @return mixed
		 **/
		public static function get_option_or_default( $option_name ) {
			// Handle $option_name passed in with or without self::$option_prefix applied:
			$option_name_no_prefix = str_replace( self::$option_prefix, '', $option_name );
			$option_name = self::$option_prefix . $option_name_no_prefix;
			$option = get_option( $option_name );
			$option_formatted = self::apply_option_defaults( array(
				$option_name_no_prefix => $option
			), true );
			return $option_formatted[$option_name_no_prefix];
		}

        /**
		 * Initializes setting registration with the Settings API.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function settings_init() {
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'base_feed_url' );
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'degree_plan_code_name' );
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'degree_subplan_code_name' );
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'degree_online_meta_field' );
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'include_css' );
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'cache_results' );
			register_setting( 'ucf_tuition_fees', self::$option_prefix . 'transient_expiration' );

			add_settings_section(
				'ucf_tuition_fees_general',
				'General Settings',
				'',
				'ucf_tuition_fees'
			);

			add_settings_field(
				self::$option_prefix . 'base_feed_url',
				'Base Tuition and Fees Feed URL',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'base_feed_url',
					'description' => 'The base url of the tuition and fees feed.',
					'type'        => 'text'
				)
			);

			add_settings_field(
				self::$option_prefix . 'degree_plan_code_name',
				'Degree Plan Code meta name',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'degree_plan_code_name',
					'description' => 'The name of the meta field that stores individual degree plan codes.',
					'type'        => 'text'
				)
			);

			add_settings_field(
				self::$option_prefix . 'degree_subplan_code_name',
				'Degree Subplan Code meta name',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'degree_subplan_code_name',
					'description' => 'The name of the meta field that stores individual degree subplan codes.',
					'type'        => 'text'
				)
			);

			add_settings_field(
				self::$option_prefix . 'degree_online_meta_field',
				'Degree Online meta name',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'degree_online_meta_field',
					'description' => 'The name of the meta field that determines if a degree is an online degree.',
					'type'        => 'text'
				)
			);

			add_settings_field(
				self::$option_prefix . 'include_css',
				'Include Default CSS',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'include_css',
					'description' => 'If checked, the default css file will be added to all pages.',
					'type'        => 'checkbox'
				)
			);

			add_settings_field(
				self::$option_prefix . 'cache_results',
				'Cache Feed Results',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'cache_results',
					'description' => 'If checked, the results from the tuition and fees feed will be cached as a transient.',
					'type'        => 'checkbox'
				)
			);

			add_settings_field(
				self::$option_prefix . 'transient_expiration',
				'Transient Timeout',
				array( 'UCF_Tuition_Fees_Config', 'display_settings_field' ),
				'ucf_tuition_fees',
				'ucf_tuition_fees_general',
				array(
					'label_for'   => self::$option_prefix . 'transient_expiration',
					'description' => 'The number of hours the result transients should be cached for.',
					'type'        => 'text'
				)
			);
		}

        /**
		 * Displays an individual setting's field markup.
		 *
		 * @author Jo Dickson
		 * @since 1.0.0
		 **/
		public static function display_settings_field( $args ) {
			$option_name   = $args['label_for'];
			$description   = $args['description'];
			$field_type    = $args['type'];
			$current_value = self::get_option_or_default( $option_name );
			$choices       = isset( $args['choices'] ) ? $args['choices'] : null;
			$markup        = '';
			switch ( $field_type ) {
				case 'checkbox':
					ob_start();
				?>
					<input type="checkbox" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" <?php echo ( $current_value == true ) ? 'checked' : ''; ?>>
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php
					$markup = ob_get_clean();
					break;
				case 'checkbox_multi':
					ob_start();
					foreach ( $choices as $value=>$text ) :
				?>
					<input type="checkbox" id="<?php echo $option_name . '_' . $value; ?>" name="<?php echo $option_name; ?>[<?php echo $value; ?>]" <?php echo ( array_key_exists( $value, $current_value ) ) ? 'checked' : ''; ?>>
					<span class="description" style="margin-right: 8px;">
						<?php echo $text; ?>
					</span>
				<?php
					endforeach;
					$markup = ob_get_clean();
					break;
				case 'number':
					ob_start();
				?>
					<input type="number" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" value="<?php echo $current_value; ?>">
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php
					$markup = ob_get_clean();
					break;
				case 'text':
				default:
					ob_start();
				?>
					<input type="text" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" value="<?php echo $current_value; ?>">
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php
					$markup = ob_get_clean();
					break;
			}
		?>

		<?php
			echo $markup;
		}

        /**
		 * Registers the settings page to display in the WordPress admin.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function add_options_page() {
			$page_title = 'UCF Tuition and Fees Settings';
			$menu_title = 'UCF Tuition and Fees';
			$capability = 'manage_options';
			$menu_slug  = 'ucf_tuition_fees';
			$callback   = array( 'UCF_Tuition_Fees_Config', 'options_page_html' );
			return add_options_page(
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				$callback
			);
		}
		/**
		 * Displays the plugin's settings page form.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function options_page_html() {
			ob_start();
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ucf_tuition_fees' );
				do_settings_sections( 'ucf_tuition_fees' );
				submit_button();
				?>
			</form>
		</div>
		<?php
			echo ob_get_clean();
		}

		/**
		 * Enqueues admin assets on appropriate pages
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $hook string | The current admin hook
		 **/
		public static function enqueue_admin_assets( $hook ) {
			if ( 'settings_page_ucf_tuition_fees' === $hook ) {
				wp_enqueue_script( 'ucf-tf-admin-js', TUITION_FEES__JS_URL . '/ucf-tf-admin.min.js', array( 'jquery' ), null, true );
			}
		}
    }
}
