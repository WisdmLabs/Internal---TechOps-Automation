<?php


/*  ----------------------------------------------------------------------------
    the custom compiler
 */
class td_css_compiler {
    var $raw_css;
    var $settings; //array

    var $css_sections; //array

	private static $registered_atts = array();

    function __construct($raw_css) {
        $this->raw_css = $raw_css;

        /**
         * @since 27.3.2017 Also load the css from the fake api. Plugins can put custom raw css
         */


        $this->raw_css .= td_api_css_generator::get_all();


//	    print_r(debug_backtrace());
//	    die;
    }


    function load_setting($name, $append_to_value = '') {
        //echo 'rara1';
        $current_customizer_value = td_util::get_option('tds_' . $name);
        if (!empty($current_customizer_value)) {
            $current_customizer_value.= $append_to_value;
        }
        $this->load_setting_raw($name, $current_customizer_value);
    }

    function load_setting_raw($full_name, $value) {

    	if ( ! td_util::tdc_is_live_editor_iframe() && 0 === strpos( $full_name, 'style_' ) ) {
		    if ( in_array( $full_name, self::$registered_atts ) ) {
			    return;
		    } else {
			    self::$registered_atts[] = $full_name;
		    }
	    }
		// # css values are removed!
	    if ($value !== '#') {
			$this->settings[$full_name] = $value;
	    }
        //print_r($this->settings) ;
    }

    static function resetRegisteredAtts() {
    	self::$registered_atts = [];
    }




    function load_setting_array($arr) {

        $remove_empty = array_filter($arr[key($arr)]);
        if (empty($remove_empty)) {
            return;
        }

        $this->settings[key($arr)] = $arr[key($arr)];
    }

    function split_into_sections() {
        //remove <style> wrap
        $this->raw_css = str_replace('<style>', '', $this->raw_css);
        $this->raw_css = str_replace('</style>', '', $this->raw_css);

        //explode the sections
        $css_splits = explode('/*', $this->raw_css);
        foreach ($css_splits as $css_split) {
            $css_split_parts = explode('*/', $css_split);
            if (!empty($css_split_parts[0]) and !empty($css_split_parts[1])) {
                $this->css_sections[trim($css_split_parts[0])] = $css_split_parts[1];
            }
        }
    }


    function compile_sections() {
        if (!empty($this->css_sections) and !empty($this->settings)) {
            foreach ($this->css_sections as $section_name => &$section_css) {
                foreach ($this->settings as $setting_name => $setting_value) {

                    if (is_array($setting_value)) {
                        //we have a property -> falue array :)
                        $css_property_value_buffer = '';
                        foreach ($setting_value as $css_property => $css_value) {
                            if (!empty($css_value)) {
                                $css_property_value_buffer .= str_replace('_', '-', $css_property) . ':' . $css_value . ';' . "\n\t";
                            }
                        }

                        //write the values to the sections css by ref
                        //$section_css = str_replace('@' . $setting_name, $css_property_value_buffer, $section_css);
                        $section_css = preg_replace('/@' . $setting_name . '\b/', $css_property_value_buffer, $section_css);
                    } else {
                        //$section_css = str_replace('@' . $setting_name, $setting_value, $section_css);
                        $section_css = preg_replace('/@' . $setting_name . '\b/', $setting_value, $section_css);
                    }

                }
            }
        }
    }


    function compress_sections( $section_css ) {

        $new_section_css = '';
        $new_section_props = [];

        // clear all internal medias
        $clean_section_css = preg_replace('/@media[^{]+\{[\s\S]+?}\s*}/', '', $section_css);

        preg_match_all('/([^{]+)\{([^}]+)\}/U', $clean_section_css, $matches);

        if (!empty($matches) && is_array($matches) && 3 === count($matches)) {

            foreach( $matches[1] as $index => $css_prop ){

                $found = false;
                foreach ($new_section_props as &$new_section_prop ) {
                    if ($new_section_prop['key'] === trim($css_prop)) {
                        $new_section_prop['val'][] = $matches[2][$index];
                        $found = true;
                        break;
				    }
			    }

			    if (!$found) {
			        $new_section_props[] = array(
			            'key' => trim($css_prop),
			            'val' => [$matches[2][$index]],
				    );
			    }
		    }
	    }

	    foreach ($new_section_props as $section_prop) {
	        $new_section_css .= $section_prop['key'] . '{';
            foreach ( $section_prop['val'] as $val ) {
                $new_section_css .= $val;
            }
            $new_section_css .= '}';
	    }

	    if (!empty($new_section_css)) {

	    	// extract content of internal media
            preg_match_all('/@media[^{]+\{[\s\S]+?}\s*}/', $section_css, $matches);

            if (!empty($matches) && is_array($matches)) {

            	foreach ($matches as $match ) {
		            if ( ! empty( $match[ 0 ] ) ) {
		            	foreach ($match as $media ) {
		            		$new_section_css .= $media;
			            }
		            }
	            }
            }

	        return $new_section_css;
	    }
	    return $section_css;
    }




    function compile_css() {

        $this->split_into_sections();
        $this->compile_sections();

        $buffy = '';

        foreach ($this->css_sections as $section_name => $section_css) {
        	if (!empty($this->settings[str_replace('@', '', $section_name)])) {
		        $buffy .= $section_css;
	        }
        }

        // gather all variables which need to be stored in the root DOM element
        if( preg_match_all( '#:root {(.*)}#Usmi', $buffy, $root_variables_matches ) ) {
            $variables = '';
            $str_to_replace = array();

            for( $i = 0; $i < count( $root_variables_matches[0] ); $i++ ) {
                $variables .= $root_variables_matches[1][$i];
                $str_to_replace[] = $root_variables_matches[0][$i];
            }

            $buffy = str_replace( $str_to_replace, '', $buffy );
            $buffy .= ":root{" . td_resources_optimize::css_minifier($variables) . "}";
        }

        $buffy = trim($buffy);


        return $buffy;

    }
}
