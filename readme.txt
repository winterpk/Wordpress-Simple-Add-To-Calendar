# Plugin Name
Contributors: winterpk
Donate link:
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a simple link for calendar events 

# Requirements
PHP >= 5.2
Apache >= 2.2.22

# Description 

This plugins add shortcode which allow simple creation of event links

The following tag is used to create a simple event link

Uses twitter bootstrap for dropdown menus and buttons (http://getbootstrap.com)

`[satc]`

It accepts several required parameters and a few optionl:

*	theme 				- Optional, used to switch between themes. Defaults to 'text'.  The following themes are supported: 
							"satc-image-cal1", "satc-image-cal2", "satc-image-cal3", "satc-btn-blue", "satc-btn-green", "satc-btn-ltblue", "satc-btn-orange", "satc-btn-red"
*	link_text 			- Optional, used when theme is set to 'text'.
*	start_date 			- Required, accepts a variety of formats.
*	start_time			- Required, accepts a variety of formats.
*	end_date			- Required, accepts a variety of formats.
*	end_time			- Required, accepts a variety of formats.
*	timezone			- Required, Timezone of the event. Timezone codes are found here: http://php.net/manual/en/timezones.php
*	event_name			- Required, Name of event.
*	organizer			- Required, Organizer of the event.
*	location			- Required, Location of the event.
*	filename			- Optional, name of the file.
*	description 		- Required, Description of the event. Converts breaks to new lines but accepts no other html.
 
# Example

	[satc theme="text" link_text="Click To Add To Calendar" start_date="2/6/2015" start_time="11:30am" end_date="2/6/2015" end_time="12:00pm" timezone="America/Denver" event_name="Test Event" organizer="winterpk" location="Brazil" filename="brazil.ics"]
	Description text goes here
	Description line 2
	[/satc]

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

# Screenshots


# Changelog 

## 1.3.2

* Fixed an issue with date/times
* Fixed a JS selector issue when wordpress inserts breaks automatically
* Fixed an issue with new lines not working for description on some Wordpress installs

## 1.3.1
 
* Fixed a fatal error on PHP 5.2 systems with the DateTime object

## 1.3.0

* Rebuilt the backend to create a .ics file
* Rebuilt the frontend to download the file normally

## 1.2.2

* Added three new themes, ltblue, orange and red btns
* Removed first break from description
* Removed summary
* Changed name to event name
* Used event name in ics file creation

## 1.2.1

* Fixed a bug with filename attribute
* Updated CSS

## 1.2.0
* Added Google Calender support
* Added serveral new theme options

## 1.0.0
* First release candidate
* Very basic functionality
