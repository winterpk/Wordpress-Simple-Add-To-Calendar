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
		'event_name',
		'organizer',
		'location',
	);
	
	var $optional_fields = array(
		'filename',
		'timezone',
	);
	
	var $temp_path = 'satc';
	
	var $url_temp_path = null;
	
	/**
	 * Array containing the valid/sanitized fields from shortcode
	 * 
	 */
	var $valid_fields = array();
	
	function __construct() {
		// Set temp path
		$uploads_dir = wp_upload_dir();
		
		// Set temp path url
		$this->url_temp_path = $uploads_dir['baseurl'] . '/' . $this->temp_path;
		
		$path = $uploads_dir['basedir'] . '/' . $this->temp_path;
		$this->temp_path = $path;
		
		// Clean up old files
		$this->_clean_up_files();
		
		 
	}
	
	/**
	 * Activation hook fires on plugin activation
	 * 
	 */
	static function install() {
		$satc = new satc;
		try {
			
			// Create the temp path if it doesn't exist
			if (file_exists($satc->temp_path) === false) {
				mkdir($satc->temp_path);
			}
			
			if (file_exists($satc->temp_path . '.htaccess') === false) {
				
				// Create the .htaccess file to prevent access to all but .ics files
				$file_string = '
					Order Allow,Deny
					<FilesMatch "^.*\.ics$">
						Allow from all
					</FilesMatch>
				';
				$file_handle = fopen($satc->temp_path . '/.htaccess', 'w');	
				fwrite($file_handle, $file_string);
				fclose($file_handle);
			}
			
		} catch (ErrorException $e) {
			wp_die('Unable to write to' . $satc->temp_path);
		}
	}

	/**
	 * Uninstallation function
	 *  
	 */
	static function uninstall() {
		$satc = new satc;
		// Delete the temp folder
		unlink($satc->rrmdir($satc->temp_path));
	}
	
	/**
	 * Recursively remove a directory
	 * Pulled from http://php.net/rmdir one of the comments
	 * 
	 * @param	string	Directory path
	 * @return	void	
	 */
	function rrmdir($dir) { 
		if (is_dir($dir)) {
			$objects = scandir($dir); 
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
				} 
			} 
			reset($objects); 
			rmdir($dir); 
		} 
	} 
	
	/**
	 * Wordpress shortcode function
	 * 
	 * @param	array 	Array of attributes from the shortcode
	 */
	static function satc_shortcode($attrs, $description) {
		$satc = new satc;
		
		// Validate and Sanitize all fields
		$valid_fields = $satc->validate_shortcode_fields($attrs);
		
		// Enqueue scripts and styles when shortcode is present
		wp_enqueue_style('satc', plugins_url( 'satc', 'satc' ) . '/satc.css', array(), null, 'all');
		wp_enqueue_script( 'satc', plugins_url( 'satc', 'satc' ) . '/satc.js', array(), null, true );
		
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
		
		// Replace breaks with \n for icalformat then striptags.
		// We also need to comment out the \n characters so vcalendar can parse it properly
		$breaks = array("<br />","<br>","<br/>");
		
		// Decode all weird html entites
		$description = html_entity_decode($description);
		
		// Remove all new line characters
		$description = str_replace("\n", "", $description);
		$description = str_replace("\r", "", $description);
		
		// Strip out <p> tags and replace </p> with a break
		$description = str_replace('<p>', '', $description);
		$description = str_replace('</p>', '<br />', $description);
		
		// Rip out any breaks that appear at the beginning of string
		$description = preg_replace('/^<br.*\/>/Uis', '', $description, 1);
		
		// Convert breaks to LITERAL new line characters
    	$satc->valid_fields['description'] = strip_tags( str_ireplace($breaks, "\\n", addslashes( trim($description))));
		
		$satc->valid_fields['url_description'] = urlencode(strip_tags(str_ireplace($breaks, "\n", trim($description))));

		// Add a UID 
		$satc->valid_fields['uid'] = uniqid();
		
		// Create a vcal format current time
		$satc->valid_fields['now'] = date('Ymd\This'); 
		
		// Get rid of start and end times because we no longer need those
		unset($satc->valid_fields['start_time']);
		unset($satc->valid_fields['end_time']);
		
		// Create the ics file and set path as a valid field
		$ics_file_path = $satc->_create_ics_file();
		$satc->valid_fields['ics_path'] = $ics_file_path;
		
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
		$output = '<div class="dropdown satc-event" >';
		if ($link_text) {
			$output .= '<span data-target="#" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false" href="#" class="satc-element ' . $this->valid_fields["theme"] . '">' . $link_text . '</span>';
			
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
		$output .= "<li><a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".urlencode($this->valid_fields['event_name'])."&dates=".$this->valid_fields['start_date']."Z/".$this->valid_fields['end_date']."Z&details=".$this->valid_fields['url_description']."&location=".urlencode($this->valid_fields['location'])."&trp=false&sprop=&sprop=name:\" target=\"_blank\" rel=\"nofollow\">Google</a></li>";
		$output .= '';
    	$output .= '</ul>';
		$output .= '</div>';
		return $output;
	}
	
	/**
	 * This function deletes all files in the temp directory older then x hours
	 * 
	 * @param	int	Hours to retain files
	 * @return	void
	 */
	private function _clean_up_files($hours = 1) {
		$hours = (int)$hours;
		if (is_dir($this->temp_path)) {
			$files = glob($this->temp_path . "*");	
			$time = time();
			foreach ($files as $file) {
				if (is_file($file)) {
					if ($time - filemtime($file) >= ($hours * 60)) {
						unlink($file);
					}
				}
			}
		}
	}
	
	/**
	 * Creates the ics file for download
	 * Valid fields must already be set
	 * 
	 * @return 	mixed	Path to the ics file or false on failure
	 */
	private function _create_ics_file() {
		
		
$ics = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:alpinesbsolutions.com
BEGIN:VEVENT
UID:' . $this->valid_fields["uid"] . '
DTSTAMP;TZID=UTC:' . $this->valid_fields["now"] . '
DTSTART;TZID=UTC:' . $this->valid_fields["start_date"] . '
SEQUENCE:0
TRANSP:OPAQUE
DTEND;TZID=UTC:' . $this->valid_fields["end_date"] . '
LOCATION:' . $this->valid_fields["location"] . '
SUMMARY:' . $this->valid_fields["event_name"] . '
DESCRIPTION:' . $this->valid_fields["description"] . '
END:VEVENT
END:VCALENDAR';
		try {
			
			// Check for filename
			if ( ! empty($this->valid_fields['filename'])) {
				$filename = str_replace('.ics', '', $this->valid_fields['filename']) . '-' . $this->valid_fields['uid'] . '.ics';
				
			} else {
				$filename = $this->valid_fields['uid'] . '.ics';
			}
			$filepath = $this->temp_path . '/' . $filename;
			$urlpath = $this->url_temp_path . '/' . $filename;
			$file_handler = fopen($filepath, "w");			
			fwrite($file_handler, $ics);
			fclose($file_handler);
			return $urlpath;
		} catch(ErrorException $e) {
			return false;
		}
		// var vEvent = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:alpinesbsolutions.com\nBEGIN:VEVENT\nUID:"+eventDetails['uid']+"\nDTSTAMP;TZID=UTC:"+eventDetails['now']+"Z\nDTSTART;TZID=UTC:"+eventDetails['start_date']+"Z\n
		//SEQUENCE:0\nTRANSP:OPAQUE\nDTEND;TZID=UTC:"+eventDetails['end_date']+"Z\nLOCATION:"+eventDetails['location']+"\nSUMMARY:"+eventDetails['event_name']+"\nDESCRIPTION:"+eventDetails['description']+"\nEND:VEVENT\nEND:VCALENDAR";
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

// Add activation hook
register_activation_hook(__FILE__, array('satc', 'install'));

// Add deactivation hook
register_deactivation_hook(__FILE__, array('satc', 'uninstall'));
