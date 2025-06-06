<?php

/**
 * Theme utils class
 */
class tagdiv_util {

	/**
	 * reads a theme option from wp
	 * @param $optionName
	 * @param string $default_value
	 * @return string|array
	 */
	static function get_option( $optionName, $default_value = '' ) {
		return tagdiv_options::get( $optionName, $default_value );
	}

	/**
	 * updates a theme option
	 * @param $optionName
	 * @param $newValue
	 */
	static function update_option( $optionName, $newValue ) {
		tagdiv_options::update( $optionName, $newValue );
	}

	/**
	 * @return bool returns true if the TagDiv Composer is installed
	 */
	static function tdc_is_installed() {
		if ( class_exists('tdc_state', false ) && function_exists( 'tdc_b64_decode' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * check if a theme plugin is active
	 *
	 * @param $plugin - the plugin td_config array
	 * @return bool - true if active, false otherwise
	 */
	static function is_active( $plugin ) {

		$plugin_key = str_replace( '-', '_', strtoupper( $plugin['slug'] ) );
		$td_plugins = tagdiv_global::get_td_plugins();

		// check if it's a theme plugin
		if ( array_key_exists( $plugin_key, $td_plugins ) ) {
			if ( class_exists( $plugin['td_class'], false ) ) {
				return true;
			} elseif ( $plugin['slug'] === 'td-mobile-plugin' ) {
				if ( ( defined('TD_MOBILE_PLUGIN') || has_action( 'admin_notices', 'td_mobile_msg' ) ) ) {
					return true;
				}
			} elseif ( $plugin['slug'] === 'amp' ) {
				if  ( self::is_amp_plugin_installed() ) {
					return true;
				}
			}
			return false;
		} elseif ( strpos($plugin_key, 'TD_DEMO_') !== false ) {
            if ( class_exists( $plugin['td_class'], false ) ) {
                return true;
            }
        }

		return false;
	}

	/**
	 * Checks if the default AMP WP plugin is installed
	 * @return bool true if AMP is installed( and it's not the old tagdiv amp plugin )
	 */
	static function is_amp_plugin_installed() {
		if ( defined('AMP__VERSION') && ! defined('TD_AMP') ) {
			return true;
		}

		return false;
	}

    static function is_base64( $string ) {
        if( $string == '' ) return false;

        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) return false;

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($string, true);
        if(false === $decoded) return false;

        // Encode the string again
        if(base64_encode($decoded) != $string) return false;

        return true;
    }


    static function remove_inactive_shortcodes($content) {
        global $shortcode_tags;

        // Regular expression to find all shortcodes in the content
        $pattern = '/\[(\/?)(\w+)([^\]]*)\]/';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $plugin_prefixes = [
            'td_block',
            'tdc_',
            'tdb_',
            'td_woo',
            'tds_',
            'tdn_',
        ];


        // Loop through all found shortcodes
        foreach ($matches as $shortcode) {
            // $shortcode[2] contains the name of the shortcode
            $shortcode_name = $shortcode[2];

            // Check if the shortcode name matches any of the plugin prefixes
            if (matches_plugin_prefix($shortcode_name, $plugin_prefixes)) {
                // Check if the shortcode is not registered (inactive)
                if (!isset($shortcode_tags[$shortcode_name])) {
                    // Build the full shortcode pattern to remove (self-closing and enclosing)
                    $full_pattern = '/\[' . preg_quote($shortcode_name, '/') . '[^\]]*\](?:.*?\[\/' . preg_quote($shortcode_name, '/') . '\])?/';
                    $content = preg_replace($full_pattern, '', $content);
                }
            }
        }
        return $content;
    }

}

if (!function_exists('matches_plugin_prefix')) {
    function matches_plugin_prefix($shortcode_name, $prefixes) {
        foreach ($prefixes as $prefix) {
            if (strpos($shortcode_name, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}

/**
 * mbstring support - if missing from host
 */
if ( !function_exists('mb_strlen') ) {
	function mb_strlen ( $string, $encoding = '' ) {
		return strlen( $string );
	}
}
if ( !function_exists('mb_strpos') ) {
	function mb_strpos( $haystack, $needle, $offset=0 ) {
		return strpos( $haystack, $needle, $offset );
	}
}
if ( !function_exists('mb_strrpos') ) {
	function mb_strrpos ( $haystack, $needle, $offset=0 ) {
		return strrpos( $haystack, $needle, $offset );
	}
}
if ( !function_exists('mb_strtolower') ) {
	function mb_strtolower( $string ) {
		return strtolower( $string );
	}
}
if ( !function_exists('mb_strtoupper') ) {
	function mb_strtoupper( $string ){
		return strtoupper( $string );
	}
}
if ( !function_exists('mb_substr') ) {
	function mb_substr( $string, $start, $length, $encoding = '' ) {
		return substr( $string, $start, $length );
	}
}
if ( !function_exists('mb_detect_encoding' ) ) {
	function mb_detect_encoding( $string, $enc=null, $ret=null ) {

		static $enclist = array(
			'UTF-8', 'ASCII',
			'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
			'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
			'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
			'Windows-1251', 'Windows-1252', 'Windows-1254',
		);

		$result = false;

		foreach ( $enclist as $enc_type ) {
			$sample = @iconv( $enc_type, $enc_type, $string );
			if ( md5( $sample ) == md5( $string ) ) {
				if ( $ret === NULL ) { $result = $enc_type; } else { $result = true; }
				break;
			}
		}

		return $result;
	}
}
