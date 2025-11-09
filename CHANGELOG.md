# 🧾 Changelog
All notable changes to this project will be documented in this file.  

## [1.0.0] - 2025-10-05
### 🎉 Initial Release
- 🚀 First stable public release of **Fresh Reminder**.  
- Added automatic freshness indicator (🟢 Fresh, 🔴 Stale) on each post.  
- Introduced admin dashboard page showing all posts with freshness status and last updated date.  
- Included lightweight and performance-focused architecture.  
- Added plugin initialization, constants, and basic security checks (`ABSPATH` protection).  

---

## [1.1.0] - 2025-10-31
### ✨ Major Feature Update
- Added Content Freshness Tracking enhancements with improved accuracy.
- Introduced status-based filtering.
- Added category-based filtering for easy content management by topic.
- Implemented search functionality to quickly find posts by title or tag.
- Added “Pin to CheckBucket” feature — temporarily store posts needing manual review.
- Introduced CheckBucket dashboard section, allowing easy review and management of pinned posts.
- Added plugin Settings Page for customizing freshness thresholds (e.g., 15/60/120 days).
- Improved dashboard with AJAX for smoother updates and no page reloads.
- Optimized caching layer for faster freshness calculations.
- UI improvements for dashboard and widget consistency.
- Code refactoring and security improvements for better maintainability. 

---

## [1.1.1] - 2025-11-09
### ✨ Wordpress Standard Code update
- Added the manual libraries for worspress plugin standards.
- Created a new logging utility class FR_Logger to handle logging messages to a specified log file when WP_DEBUG is enabled.
- Add Font Awesome v4 compatibility WOFF2 font file.

---

## [1.2.0] - _Upcoming_
### ✨ Planned Enhancements 
- Add daily cron job to email admin about stale posts.  
- Add admin notifications for posts nearing “stale” threshold.  
- Add translation support (`.pot` file).  
- Add Gutenberg sidebar block for real-time freshness info.
- Add in search give a dropdown to search by options like posts/products/auther/ keyword

---

## [Future Roadmap]
- [ ] Integration with OpenAI API to suggest automatic post updates for stale content.  
- [ ] Option to auto-refresh content by fetching recent data sources.  
- [ ] REST API endpoints for freshness data.  
- [ ] Admin analytics chart for freshness over time.  

---

## 🧑‍💻 Contributors
- **Hasun Akash Bandara** — Creator & Lead Developer  
- Open for community contributions ❤️  

---

