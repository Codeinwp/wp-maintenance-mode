# LightStart - Maintenance Mode, Coming Soon and Landing Page Builder #

**Contributors:** [themeisle](https://profiles.wordpress.org/themeisle/)  
**Plugin Name:** LightStart - Maintenance Mode, Coming Soon and Landing Page Builder  
**Plugin URI:** https://themeisle.com/  
**Author:** Themeisle  
**Author URI:** https://themeisle.com/  
**Tags:** maintenance mode, admin, administration, unavailable, coming soon, multisite, landing page, under construction, contact form, subscribe, countdown  
**Requires at least:** 3.5  
**Tested up to:** 6.2  
**Stable tag:** 2.6.7  
**Requires PHP:** 5.6  
**License:** GPL-2.0+  

Easy Drag & Drop Page Builder that adds a splash page to your site that it's perfect for a coming soon page, maintenance or landing page.

## Description ##

Add a maintenance page to your blog that lets visitors know your blog is down for maintenance, add a coming soon page for a new website or create a landing page for an existing site. User with admin rights gets full access to the blog including the front end.

Activate the plugin and your blog is in maintenance-mode, works and only registered users with enough rights can see the front end. You can use a date with a countdown timer for visitor information or set a value and unit for information.

Also works with WordPress Multisite installs (each blog from the network has its own maintenance settings).

### Features ###

* Fully customizable (change colors, texts and backgrounds).
* Subscription form (export emails to .csv file).
* Countdown timer (remaining time).
* Contact form (receive emails from visitors).
* Coming soon page;
* Landing page templates;
* WordPress multisite;
* Responsive design;
* Social media icons;
* Works with any WordPress theme;
* SEO options;
* Exclude URLs from maintenance;
* Bot functionality to collect the emails in a friendly and efficient way;
* GDPR Ready;

### Bugs, technical hints or contribute ###

Please give us feedback, contribute and file technical bugs on [GitHub Repo](https://github.com/andrianvaleanu/WP-Maintenance-Mode).

### Credits ###

Developed by [Themeisle](https://themeisle.com)

### What's Next ###

If you like this plugin, then consider checking out our other projects:

* <a href="https://optimole.com/">Optimole</a> - Optimole is your all-in-one image optimization solution for WordPress & beyond.
* <a href="https://wpshout.com/">WPShout</a> - In-Depth WordPress Tutorials for Developers
* <a href="https://revive.social/">Revive Social</a> - Revive Old Posts helps you keep your content alive and in front the audiences that matter.
* <a href="https://www.codeinwp.com/">CodeinWP</a> - CodeinWP stands for all-things-WordPress. From web design to freelancing and from development to business, your questions are covered.
* <a href="https://domainwheel.com">DomainWheel</a> - Free Short Website name generator, with the help of AI, for instant ideas.


Check-out <a href="https://themeisle.com/blog/" title="Themeisle blog">our blog</a> to learn from our <a href="https://themeisle.com/blog/category/wordpress/reviews/" title="WordPress Reviews">WordPress Reviews</a> and see other <a href="https://themeisle.com/blog/category/wordpress-plugins/" title="WordPress Plugins Comparisons">WordPress plugins</a>.

## Installation ##

1. Unpack the download package
2. Upload all files to the `/wp-content/plugins/` directory, include folders
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to `Settings` page, where you can change what settings you need (pay attention to **Exclude** option!)

## Screenshots ##

1. Maintenance Mode Example
2. Maintenance Mode Example #2
3. Bot Example
4. Dashboard General Settings
5. Dashboard Design Settings
6. Dashboard Modules Settings
7. Dashboard Bot Settings
8. Contact Form

## Frequently Asked Questions ##

### How to use plugin filters ###
Check out our [Snippet Library](https://github.com/codeinwp/Snippet-Library/).

### Cache Plugin Support ###
WP Maintenance Mode can be unstable due to the cache plugins; we recommend deactivating any cache plugin when maintenance mode is active. If you **really** want to use a cache plugin, make sure you delete the entire cache after each change.

### Exclude list ###
If you change your login url, please add the new slug (url: http://domain.com/newlogin, then you should add: newlogin) to Exclude list from plugin settings -> General Tab.

Notice: `wp-cron.php` is excluded by default.

## Changelog ##

##### [Version 2.6.7](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.6...v2.6.7) (2023-04-01)

- Bug fix: Addressed an unnecessary 'no maintenance page' error occurrence.




##### [Version 2.6.6](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.5...v2.6.6) (2023-03-31)

- Remove error notice for users that use an overriding custom template
- Update dependencies




##### [Version 2.6.5](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.4...v2.6.5) (2023-03-01)

Update dependencies




##### [Version 2.6.4](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.3...v2.6.4) (2023-02-24)

- Add the option to manage maintenance status on all sites from the network dashboard
- Update dependencies




##### [Version 2.6.3](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.2...v2.6.3) (2023-02-06)

* Update dependencies




##### [Version 2.6.2](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.1...v2.6.2) (2022-12-16)

* Add the option in the wizard to skip importing a template and installing Otter
* Improve the quality of the template screenshots
* Fix the template overriding issue




##### [Version 2.6.1](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.6.0...v2.6.1) (2022-11-03)

* Add a notice to announce the rebrand of the plugin




#### [Version 2.6.0](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.5.4...v2.6.0) (2022-11-02)

* Adds Landing pages templates
* Adds more Coming soon and Maintenance mode templates
* Rebrand the plugin into LightStart




##### [Version 2.5.4](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.5.3...v2.5.4) (2022-10-10)

* Fix subscribers entry export for legacy forms.
* Fix PHP notice showing up on edge cases new installs.




##### [Version 2.5.3](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.5.2...v2.5.3) (2022-09-28)

* Fix wrong template loaded when the current post template is empty.




##### [Version 2.5.2](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.5.1...v2.5.2) (2022-09-27)

* Fix maintenance mode issue for previously logged users [#321](https://github.com/Codeinwp/wp-maintenance-mode/issues/321)
* Allow comments into exclude textarea so that you can comment on the IP addresses for location, props [@joostdekeijzer](https://github.com/joostdekeijzer)
* Fix PHP notice errors on specific scenarios [#324](https://github.com/Codeinwp/wp-maintenance-mode/issues/324)
* Fix Otter for saving subscriber entry




##### [Version 2.5.1](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.5.0...v2.5.1) (2022-09-08)

- Fixes a bug which was causing error on some instances




#### [Version 2.5.0](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.4.7...v2.5.0) (2022-09-08)

- New Feature: Adds compatibility with Block Editor or any page builder for building the maintenance mode page.
- New Feature: Adds coming soon and maintenance mode starting templates.




##### [Version 2.4.7](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.4.6...v2.4.7) (2022-08-08)

##### Fixes
* Fix login form display
* Fix email collecting by the bot
* Improve accessibility of the maintenance page thanks to @SophieWeb




##### [Version 2.4.6](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.4.5...v2.4.6) (2022-06-15)

* Improve data sanitizations for custom css and contact module




##### [Version 2.4.5](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.4.4...v2.4.5) (2022-06-15)

* Harden security and improve release process




##### [Version 2.4.4](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.4.3...v2.4.4) (2022-02-10)

Update dependencies




##### [Version 2.4.3](https://github.com/Codeinwp/wp-maintenance-mode/compare/v2.4.2...v2.4.3) (2022-01-27)

- Change ownership to Themeisle




### 2.4.2 (18/01/2022) ###
* Misc: 900 000 Active Installs Celebrations!
* Misc: WordPress 5.9 compatibility
* Fix: jQuery UI CSS theme reference from jQuery CDN
* Misc: Text fixes in the dashboard

### 2.4.1 (20/07/2021) ###
* Misc: WordPress 5.8 compatibility

### 2.4.0 (13/05/2021) ###
* Design: add "Custom CSS" setting; Finally! :)
* Design: add "Footer links" color setting
* Design: add a list of available shortcodes under the "Text" editor
* Bot: make {visitor_name} placeholder work in all messages after the visitor types his name
* Misc: add [embed] shortcode for responsive video embeds; Compatible with YouTube, Vimeo, DailyMotion.
* Misc: make the exclude mechanism work with Cyrillic characters
* Misc: add `wpmm_maintenance_template` filter; It works the same way as the `wpmm_contact_template` filter, but for the maintenance template.
* Misc: now you can override the `contact` email template too; Check `/views/contact.php` for more details.
* Misc: better compatibility with translation plugins like Loco Translate
* Misc: the image uploaders (from the dashboard) are now translatable
* Misc: improve uninstall routine
* Misc: add `wpmm_delete_cache` action; It is called after each setting change.
* Misc: add support for cache plugins like WP Rocket, WP Fastest Cache, Endurance Page Cache, Swift Performance Lite, Cache Enabler, SG Optimizer, LiteSpeed Cache, Nginx Helper;
* Misc: remove `wpmm_count_where` helper function
* Misc: code improvements

### 2.3.0 (07/12/2020) ###
* Modules: add support for Google Analytics 4 measurement ID
* Design: enable media buttons on wp_editor (now you can add images from the editor)
* Bot: fix translation issue
* Misc: add filters for capabilities `wpmm_settings_capability`, `wpmm_subscribers_capability`, and `wpmm_all_actions_capability` (the last one can be used to override all capabilities)
* Misc: fix [loginform] shortcode redirect attribute
* Misc: a few CSS & Javascript improvements
* Misc: bump "Tested up to" version to 5.6

### 2.2.4 (20/05/2019) ###
* bump "Tested up to" to 5.2.0
* fix typo in Italian translation (it_IT)
* Bot: add a note about how you can export the list of subscribers [#195](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/195)
* Bot: add client-side sanitization to the input fields [#176](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/176)

### 2.2.3 (20/02/2019) ###
* bump "Tested up to" version to 5.1.0
* replace "wpmu_new_blog" action with "wp_initialize_site" action for WP 5.1.0 users because the first one is deprecated in the new version
* small improvement to "check_exclude" method from "WP_Maintenance_Mode" class

### 2.2.2 (27/11/2018) ###
* Google Analytics module: migrate from analytics.js to gtag.js + add ip anonymization [#178](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/178)
* GDPR module: accept links inside texareas + add policy link target [#188](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/188)
* add charset meta tag [#200](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/200)
* fix PHP Notice:  Undefined index: HTTP_USER_AGENT
* add plural and single form translation for subscribers number (settings page)

### Earlier versions ###
For the changelog of earlier versions, please refer to the [full changelog](http://plugins.svn.wordpress.org/wp-maintenance-mode/trunk/changelog.txt).
