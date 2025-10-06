=== Fresh Reminder ===
Contributors: hasunB
Tags: fresh reminder, seo, post age, stale content, content management
Requires at least: 5.5
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tracks how fresh your WordPress posts are and highlights stale content in both frontend and admin dashboard.

== Description ==
**Fresh Reminder** helps you monitor how fresh your posts are and when they need updating.  
It automatically calculates the age of your posts based on the last modified date, and marks them as:

- **Reviewd** (Already Reviewed)    
- **Stale** (not updated for given months)   

In addition, the plugin adds a **dashboard page** showing all your posts sorted by freshness, so you can quickly see which ones need attention.

Ideal for bloggers, SEO specialists, and content managers who want to maintain up-to-date content and improve search engine performance.

== Features ==
* Automatic content freshness indicator on posts.
* Custom admin dashboard listing all posts with freshness status.
* Visual indicators for quick scanning.
* Lightweight and fast — no external API calls.
* Works immediately after activation.

== Screenshots ==
1. Frontend freshness indicator displayed below post content.
2. Admin dashboard showing post freshness overview.

== Installation ==
1. Download the plugin ZIP file or clone from GitHub:
   `git clone https://github.com/hasunB/fresh-reminder.git`
2. Upload the folder `fresh-reminder` to your WordPress `/wp-content/plugins/` directory.
3. Activate **Fresh Reminder** from your WordPress Admin → Plugins menu.
4. Visit **Fresh Reminder** in the WordPress Admin sidebar to view all post freshness data.

== Frequently Asked Questions ==

= Can I change the freshness thresholds? =
Not yet, but the next update will include settings for customizing the "fresh", "getting old", and "stale" limits.

= Does it affect website speed? =
No. It’s extremely lightweight and only runs simple date calculations without adding any database load.

= Will it work with custom post types? =
Currently supports `post`. Future versions will support custom post types.

= Is this plugin SEO-friendly? =
Yes! Keeping your content fresh can indirectly improve SEO rankings.

== Changelog ==
= v1.0.0 =
* First stable release.
* Added automatic freshness tracker for posts.
* Added admin dashboard listing all posts with freshness statuses.
* Added freshness indicator display on frontend post pages.

== Upgrade Notice ==
= v1.0.0 =
Initial release — adds core freshness tracking and dashboard interface.

== Roadmap ==
* Add customizable thresholds in plugin settings.
* Add stale post email reminders.
* Add translation (.pot) file.
* Add Gutenberg sidebar widget.
* Add OpenAI-powered suggestions for stale post updates.

== License ==
This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 or later.  

== Author ==
Developed by **Hasun Akash Bandara**  
GitHub: [https://github.com/hasunB](https://github.com/hasunB)  
Email: hasunbandara17@gmail.com
