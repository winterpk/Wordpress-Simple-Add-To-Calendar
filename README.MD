# Plugin Name
Contributors: winterpk
Donate link: 
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a simple link for calendar events 

# Description 

This plugins add shortcode which allow simple creation of event links

The following tag is used to create a simple event link

`[satc]`

It accepts several required parameters and a few optionl:

*   start_date 			- Required, accepts a variety of formats.
*   start_time			- Required, accepts a variety of formats.
*	end_date			- Required, accepts a variety of formats.
*	end_time			- Required, accepts a variety of formats.
*	organizer			- Required, Organizer of the event.
*	name				- Required, Name of event.
*	description 		- Required, Description of the event. Converts breaks to new lines but accepts no other html.
*   timezone			- Required, Timezone of the event.
*	event_summary		- Required, Summary of the event
*	filename			- Optional, name of the file.
*	theme 				- Optional, used to switch between themes. Defaults to 'text'.  
*   link_text 			- Optional, used when theme is set to 'text'.
 
# Example
`[satc theme="text" link_text="Click To Add To Calendar" start_date="2/6/2015" start_time="11:30am" end_date="2/6/2015" end_time="12:00pm" event_name="Test Event" event_description="This is a test event" event_timezone="America/Denver"]`

# Installation

This section describes how to install the plugin and get it working.

1. Upload `satc` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
2. Place `[satc]` shortcode in your templates

# Frequently Asked Questions 

## What does this do? 

Read the description.
 
## Why is there only 1 theme?

I'm still working on the other themes

## What formats does it support?

For now only Outlook and iCalendar

## Screenshots


# Changelog 

## 1.0.0
* First release candidate
* Very basic functionality
