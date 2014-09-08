=== About ===
name: Smap
website: http://www.ushahidi.com
description: Add Smap data to Ushahidi
version: 0.1
requires: 3.0
tested up to: 
author: Ushahidi Team
author website: http://www.ushahidi.com

== Description ==
Polls a remote SMAP site's API, and downloads reports from it into Ushahidi. 

== Installation ==
1. Copy the entire /smap/ directory into your /plugins/ directory.
2. Activate the plugin.

== Use ==
The SMAP plugin uses the same scheduler as the feeds and twitter. When the scheduler starts (which is every n minutes), the plugin checks the SMAP site, and downloads any new reports found on it. 

SMAP settings are:

* SMAP site title: "Test SMAP site"
* SMAP URL: the root directory of the SMAP site, e.g. "https://dev.smap.com.au/"
* SMAP username: the username supplied by the SMAP site (e.g. "ushahidi")
* SMAP password: the password supplied by the SMAP site. 

