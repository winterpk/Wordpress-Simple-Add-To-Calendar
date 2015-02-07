<?php
/**
 * @package Satc - Simple Add To Calendar Plugin
 */
/*
Plugin Name: Simple Add To Calendar
Plugin URI: http://alpinesbsolutions.com
Description: A simple shortcode implementation to create Add To Calendar link on any page
Version: 1.0.0
Author: Winterpk
Author URI: http://alpinesbsolutions.com
License: GPLv2 or later
Text Domain: satc
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Unauthorized access.';
	exit;
}

class satc {

	/**
	 * Array containing all fields expected by shortcode
	 * 
	 */
	var $required_fields = array(
		'theme',
		'start_date',
		'start_time',
		'end_date',
		'end_time',
		'name',
		'organizer',
		'location',
		'summary',
	);
	
	var $optional_fields = array(
		'filename',
		'timezone',
	);
	
	/**
	 * Array containing the valid/sanitized fields from shortcode
	 * 
	 */
	var $valid_fields = array();
	
	/**
	 * Wordpress shortcode function
	 * 
	 * @param	array 	Array of attributes from the shortcode
	 */
	function satc_shortcode($attrs, $description) {
		$satc = new satc;
		
		// Validate and Sanitize all fields
		$valid_fields = $satc->validate_shortcode_fields($attrs);
		
		// Enqueue scripts and styles when shortcode is present
		wp_enqueue_style('satc', plugins_url( 'satc', 'satc' ) . '/satc.css', array(), '1.0.0', 'all');
		wp_enqueue_script( 'satc', plugins_url( 'satc', 'satc' ) . '/satc.js', array(), '1.0.0', true );
		
		// Localize the ajax url
		$js_data = array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		);
		wp_localize_script( 'satc', 'satc', $js_data );
		
		// If string, then its an error.  Return the error.
		if (is_string($valid_fields)) {
			return $valid_fields;
		}
		
		// Convert Dates to proper timezone
		if (isset($valid_fields['timezone'])) {
			$start_date = new DateTime($satc->valid_fields['start_date'] . ' ' . $satc->valid_fields['start_time'], new DateTimeZone($valid_fields['timezone']));
			$end_date = new DateTime($satc->valid_fields['end_date'] . ' ' . $satc->valid_fields['end_time'], new DateTimeZone($valid_fields['timezone']));
		} else {
			$start_date = new DateTime($satc->valid_fields['start_date'] . ' ' . $satc->valid_fields['start_time'], new DateTimeZone(date_default_timezone_get()));
			$end_date = new DateTime($satc->valid_fields['end_date'] . ' ' . $satc->valid_fields['end_time'], new DateTimeZone(date_default_timezone_get()));
		}
		
		// Parse and format start date/time and end date/time
		$satc->valid_fields['start_date'] = $start_date->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis');
		$satc->valid_fields['end_date'] = $end_date->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis');
		//$satc->valid_fields['start_date'] = $start_date->format('Ymd\THis');
		//$satc->valid_fields['end_date'] = $end_date->format('Ymd\THis');
		// Replace breaks with \n for icalformat then striptags.
		// We also need to comment out the \n characters so vcalendar can parse it properly
		$breaks = array("<br />","<br>","<br/>");
		
		// Remove all new line characters
		$description = str_replace("\n", "", $description);
		$description = str_replace("\r", "", $description);
		
		// Convert breaks to LITERAL new line characters
    	$satc->valid_fields['description'] = $satc->escape_string(strip_tags(str_ireplace($breaks, "\\n", trim($description))));
		
		// Add a UID 
		$satc->valid_fields['uid'] = uniqid();
		
		// Create a vcal format current time
		$satc->valid_fields['now'] = date('Ymd\This'); 
		
		// Get rid of start and end times because we no longer need those
		unset($satc->valid_fields['start_time']);
		unset($satc->valid_fields['end_time']);
		
		// Check for optional link text
		if (isset($attrs['link_text']) && ! empty($attrs['link_text'])) {
			$link_text = sanitize_text_field(trim($attrs['link_text']));
			if (!empty($link_text)) {
				$return = $satc->_build_html($link_text);
			} else {
				$return = $satc->_build_html();
			}
		} else {
			$return = $satc->_build_html();
		}
		
		
		
		return $return;
	}
	
	/**
	 * Builds out the html for the frontend
	 * 
	 * @param	string	If this is a text link this field is required
	 */
	private function _build_html($link_text = false) {
		print_r($this->valid_fields);
		exit;
		$output = '<div class="dropdown satc-event" >';
		if ($link_text) {
			$output .= '<a data-target="#" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false" href="#" class="satc-element ' . $this->valid_fields["theme"] . '">' . $link_text . '</a>';
			
		} else {
			$output .= '<span data-target="#" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false" href="#" class="satc-element ' . $this->valid_fields["theme"] . '">&nbsp;</span>';
		}
		$output .= '<form id="satc-form">';
		foreach ($this->valid_fields as $field => $value) {
			$output .= '<input type="hidden" name="' . $field . '" value="' . $value . '" />';
		}
		$output .= '</form>';
		$output .= '<ul class="satc-dropdown-menu dropdown-menu" role="menu">';
		$output .= '<li><a onClick="satcOnClick(this)" href="#" data-format="iCal">Outlook</a></li>';
		$output .= '<li><a onClick="satcOnClick(this)" href="#" data-format="iCal">iCalendar</a></li>';
		$output .= "<li><a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".$this->valid_fields['name']."&dates=".$this->valid_fields['start_date']."/".$this->valid_fields['end_date']."&details=".$this->valid_fields['description']."&location=[location]&trp=false&sprop=&sprop=name:\" target=\"_blank\" rel=\"nofollow\">Google</a></li>";
		$output .= '';
		
		///<li><a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=[event-title]&dates=[start-custom format='Ymd\\THi00\\Z']/[end-custom format='Ymd\\THi00\\Z']&details=[description]&location=[location]&trp=false&sprop=&sprop=name:\" target=\"_blank\" rel=\"nofollow\">Google</a></li>
    	
    	$output .= '</ul>';
		$output .= '</div>';
		return $output;
	}
	
	/**
	 * Validation and sanitization function
	 * 
	 * @param	array 	Attributes from the shortcode
	 */
	function validate_shortcode_fields($fields) {
		$valid_fields = array();
		
		// Loop through $fields and check against $required_fields and $optional_fields array
		foreach ($fields as $field => $value) {
			if (in_array($field, $this->required_fields)) {
				
				// Sanitize, trim and add to valid_fields array if not empty
				$value = sanitize_text_field(trim($value));
				if ( ! empty($value)) {
					$valid_fields[$field] = sanitize_text_field(trim($value));	
				}
			}
			if (in_array($field, $this->optional_fields)) {
				
				// Sanitize, trim and add to valid_fields array if not empty
				$value = sanitize_text_field(trim($value));
				if ( ! empty($value)) {
					$valid_fields[$field] = sanitize_text_field(trim($value));	
				}
			}
		}
		
		// Loop through _fields array to ensure all fields exist in valid_fields array
		foreach ($this->required_fields as $field) {
			if ( ! array_key_exists($field, $valid_fields)) {
				
				return 'Error: invalid input for "' . $field . '" field';
			}
		}
		$this->valid_fields = $valid_fields;
		
		// If all checks pass, return valid_fields array
		return $valid_fields;
	}

	/**
	 * Escapes a string of characters for vcal vevent description
	 * 
	 */ 
	function escape_string($string) {
	  return preg_replace('/([\,;])/','\\\$1', $string);
	}
}

// Add satc ajax action 
add_action( 'wp_ajax_nopriv_satc', array('satc', 'satc_ajax') );

// Add shortcode
add_shortcode('satc', array('satc', 'satc_shortcode'));
