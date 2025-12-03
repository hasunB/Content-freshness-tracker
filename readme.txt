=== Fresh Reminder ===
Contributors: hasun Bandara 
Tags: fresh reminder, seo, post age, content management, content freshness  
Requires at least: 5.5  
Tested up to: 6.9  
Requires PHP: 7.4  
Stable tag: 1.1.3  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Tracks how fresh your WordPress posts are and highlights stale content in both frontend and admin dashboard.

== Description ==
**Fresh Reminder** helps you monitor how fresh your posts are and when they need updating.  
It automatically calculates the age of your posts based on the last modified date and marks them as:

- ✅ **Reviewed** – Manually confirmed as up to date.  
- ⚠️ **Unreviewed** – Not updated for a long time and requires attention.  

The plugin adds a dashboard page showing all your posts sorted by freshness, so you can easily identify which content needs updates.

Ideal for **bloggers, SEO specialists, and content managers** who want to maintain up-to-date content and improve their website’s search performance.

== Features ==
* Automatic content freshness indicator on posts.  
* Custom admin dashboard listing all posts with freshness status.  
* Filter posts by category, status, or keyword.  
* Built-in search for quick access to specific posts.  
* “Pin to CheckBucket” — temporarily save posts that need manual review.  
* CheckBucket section to manage pinned posts.  
* Plugin Settings Page to customize freshness thresholds (e.g., 15/60/120 days).  
* AJAX-powered dashboard for smooth interactions without page reloads.  
* Optimized caching for improved performance.  
* Lightweight and secure — no external API calls.  
* Works immediately after activation.  

== Screenshots ==
1. Frontend freshness indicator displayed below post content.  
2. Admin dashboard showing post freshness overview.  
3. CheckBucket section with pinned posts awaiting review.  
4. Plugin settings page for customizing thresholds.  

== Installation ==
1. Download the plugin ZIP file or clone from GitHub:  
   `git clone https://github.com/hasunB/fresh-reminder.git`  
2. Upload the folder `fresh-reminder` to your WordPress `/wp-content/plugins/` directory.  
3. Activate **Fresh Reminder** from your WordPress Admin → Plugins menu.  
4. Go to **Fresh Reminder** in the WordPress Admin sidebar to view all post freshness data.  

== Frequently Asked Questions ==

= Can I change the freshness thresholds? =
Yes! Starting from version **1.1.0**, you can customize thresholds from the plugin settings page.

= Does it affect website speed? =
No. It’s extremely lightweight and optimized with internal caching and AJAX for smooth performance.

= Will it work with custom post types? =
Currently supports `post`. Support for custom post types is planned for a future update.

= Is this plugin SEO-friendly? =
Yes! Keeping your content fresh can improve SEO and user engagement.

== Changelog ==

= 1.2.0 - Upcoming =
* Add daily cron job to email admin about stale posts.  
* Add admin notifications for posts nearing “stale” threshold.  
* Add translation support (`.pot` file).  
* Add Gutenberg sidebar block for real-time freshness info.  
* Add dropdown in search to filter by posts/products/author/keyword.  

= 1.1.1 - 2025-11-09 =
* WordPress Standard Code Update  
* Added manual library integration for plugin standards compliance.  
* Introduced `FR_Logger` class for structured logging (active only when `WP_DEBUG` is enabled).  
* Added Font Awesome v4 compatibility with local WOFF2 font file.  

= 1.1.0 - 2025-10-31 =
* **Major Feature Update**  
* Enhanced content freshness tracking accuracy.  
* Added status-based and category-based filtering.  
* Implemented search for posts by title or tag.  
* Introduced “Pin to CheckBucket” for manual review management.  
* Added CheckBucket dashboard for pinned posts.  
* Introduced settings page to customize freshness thresholds.  
* Improved dashboard with AJAX (no reloads).  
* Optimized caching for faster freshness calculations.  
* UI/UX and security improvements.  

= 1.0.0 - 2025-10-05 =
* **Initial Release**  
* Added automatic freshness indicator (Reviewed, Stale) on each post.  
* Introduced admin dashboard listing all posts with freshness status and last updated date.  
* Added plugin initialization, constants, and `ABSPATH` protection.  

== Upgrade Notice ==

= 1.2.0 =
Introduces cron jobs for email notifications, admin alerts, translation support, and Gutenberg block integration.

= 1.1.0 =
Adds filters, search, CheckBucket, and customizable thresholds for easier content management.

== Roadmap ==
* Integration with OpenAI API to suggest automatic post updates.  
* REST API endpoints for freshness data.  
* Admin analytics chart to visualize content freshness over time.  

== License ==
This plugin is free software; you can redistribute it and/or modify it under the terms of the **GNU General Public License version 2 or later**.

== Author ==
Developed by **Hasun Akash Bandara**  
GitHub: [https://github.com/hasunB](https://github.com/hasunB)  
Email: hasunbandara17@gmail.com  
