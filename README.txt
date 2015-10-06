=== DaTask ===
Contributors: Mte90
Tags: task, task managemtn, activity, learning
Requires at least: 4.2
Tested up to: 4.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Task Management system inspired to Mozilla OneAndDone project

== Description ==

DaTask is a tool for help people that join a project (with all the instructions) but not know how to start.  
Allows to easily browse tasks and show in their profile the task done or in progress.  
Case Example:    

* Quality Assurance for your project
* Steps to release a new project 
* Tasks for new collaborators on a project (ticket, patch, ecc)

Check the wiki: [https://github.com/Mte90/DaTask/wiki](https://github.com/Mte90/DaTask/wiki)  
Demo site: [http://datask.mte90.net/](http://datask.mte90.net/)

Ajax Search based on [Search & Filter via AJAX](https://github.com/qstudio/q-ajax-filter)

* Bootstrap class names in frontend
* Template customizable (`datask` folder in your theme)
* Frontend Login options
* Ajax based
* Widgets avalaible
* New post type with 4 taxonomy: Team, Area, Difficulty and Estimated minute
* Support for [WP REST API v1](https://github.com/Mte90/DaTask/wiki/API-Rest-V1)

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'datask'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `datask.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `datask.zip`
2. Extract the `datask` directory to your computer
3. Upload the `datask` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= Shortcode =

Insert the shortcode for search box:  

Parameter filter_type can have two values for show the select or the list for choose the taxonomy: filter_type="select" or filter_type="list"

`[datask-search posts_per_page="10" show_count=1]`

Insert shortcode to show task in progress:

`[datask-progress]`

= Why not use the original project? =

Check the wiki: [https://github.com/Mte90/DaTask/wiki](https://github.com/Mte90/DaTask/wiki) 

== Screenshots ==

1. The search box 
2. A task page
3. Profile

== Changelog ==

= 1.0 =
* First Release
