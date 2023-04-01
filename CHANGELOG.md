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

= 2.4.2 (18/01/2022) =
* Misc: 900 000 Active Installs Celebrations!
* Misc: WordPress 5.9 compatibility
* Fix: jQuery UI CSS theme reference from jQuery CDN
* Misc: Text fixes in the dashboard

= 2.4.1 (20/07/2021) =
* Misc: WordPress 5.8 compatibility

= 2.4.0 (13/05/2021) =
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

= 2.3.0 (07/12/2020) =
* Modules: add support for Google Analytics 4 measurement ID
* Design: enable media buttons on wp_editor (now you can add images from the editor)
* Bot: fix translation issue
* Misc: add filters for capabilities `wpmm_settings_capability`, `wpmm_subscribers_capability`, and `wpmm_all_actions_capability` (the last one can be used to override all capabilities)
* Misc: fix [loginform] shortcode redirect attribute
* Misc: a few CSS & Javascript improvements
* Misc: bump "Tested up to" version to 5.6

= 2.2.4 (20/05/2019) =
* bump "Tested up to" to 5.2.0
* fix typo in Italian translation (it_IT)
* Bot: add a note about how you can export the list of subscribers [#195](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/195)
* Bot: add client-side sanitization to the input fields [#176](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/176)

= 2.2.3 (20/02/2019) =
* bump "Tested up to" version to 5.1.0
* replace "wpmu_new_blog" action with "wp_initialize_site" action for WP 5.1.0 users because the first one is deprecated in the new version
* small improvement to "check_exclude" method from "WP_Maintenance_Mode" class

= 2.2.2 (27/11/2018) =
* Google Analytics module: migrate from analytics.js to gtag.js + add ip anonymization [#178](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/178)
* GDPR module: accept links inside texareas + add policy link target [#188](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/188)
* add charset meta tag [#200](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/200)
* fix PHP Notice:  Undefined index: HTTP_USER_AGENT
* add plural and single form translation for subscribers number (settings page)

= 2.2.1 (13/07/2018) =
* replace create_function; solves [#186](https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/186) issue and [ticket](https://wordpress.org/support/topic/create_function-deprecated-in-php-7-2-8/) (thanks @ [George Jipa](https://github.com/georgejipa))
* solve "set options on first activation" bug [ticket](https://wordpress.org/support/topic/notice-undefined-index-general-in-srv-www-mysite-test-current/) (thanks @ [George Jipa](https://github.com/georgejipa))
* make (frontend) privacy labels clickable (thanks @ [George Jipa](https://github.com/georgejipa))
* added array key check to avoid notice (thanks @ [Smiggel](https://github.com/Smiggel))
* added instagram icon (thanks @ [bozdoz](https://github.com/bozdoz))

= 2.2 (25/05/2018) =
* added GDPR feature

= 2.1.2 (04/03/2018) =
* fixed a bug that was breaking the plugin after updating from 2.0.9 to 2.1.1

= 2.1.1 (01/03/2018) =
* fixed a visual bug with wrap container
* added internationalization support for bot fixed strings
* fixed path for loading data.js required for the bot (thanks @ [George Jipa](https://github.com/georgejipa))
* added `wpmm_before_scripts` hook, fires just before loading the scripts
* moved data.js to uploads directory (thanks @ [George Jipa](https://github.com/georgejipa))

= 2.1 (27/02/2018) =
* added bot feature
* css fixes
* new css transitions for buttons
* fixed https problem (thanks @ [George Jipa](https://github.com/georgejipa))
* updated translations, help us! :)

= 2.0.9 (29/11/2016) = 
* new hook (`wpmm_after_body`) in maintenance mode template (thanks @ [Karolína Vyskočilová](https://github.com/vyskoczilova))
* pt_PT (portuguese) language update (thanks @ [Pedro Mendonça](https://github.com/pedro-mendonca))
* maintenance mode template can also be loaded from theme/child-theme folder (thanks @ [Florian Tiar](https://github.com/Mahjouba91) and [Lachlan Heywood](https://github.com/lachieh))
* new hooks for contact form (if you want to add new fields): `wpmm_contact_form_start`, `wpmm_contact_form_before_message`, `wpmm_contact_form_after_message`, `wpmm_contact_form_end`
* new hook for contact form validation (if you want to validate new fields): `wpmm_contact_validation`
* new hooks for contact form template (if you want to display new fields): `wpmm_contact_template_start`, `wpmm_contact_template_before_message`, `wpmm_contact_template_after_message`, `wpmm_contact_template_end`
* some javascript improvements
* small css fix for contact form (thanks @ [frontenddev](https://wordpress.org/support/topic/please-fix-modal-window-of-contact-form/))

= 2.0.8 (09/09/2016) = 
* add wp_scripts() function (in helpers.php) to maintain backward compatibility (for those with WP < 4.2.0)
* css fix for subscribe button on maintenance page
* fix multisite administrator access issue
* pt_PT (portuguese) language update (thanks @ Pedro Mendonça)
* new hooks for Contact module: `wpmm_contact_template`, `wpmm_contact_subject`, `wpmm_contact_headers`
* jQuery (google cdn) path fix when SCRIPT_DEBUG is true

= 2.0.7 (06/07/2016) =
* reset_settings _wpnonce check (thanks # Wordfence)
* modules > google analytics code sanitization (thanks @ Wordfence)
* move sidebar banners from our servers to plugin folder... as WordPress staff requested
* Subscribe button error on Mobile version (thanks @ Hostílio Thumbo)
* replace $wp_scripts global with wp_scripts() function
* de_DE language file update (thanks @ tt22tt)

= 2.0.6 (20/06/2016) =
* notifications update
* languages update

= 2.0.5 (17/06/2016) =
* roles (array) fix

= 2.0.4 (17/06/2016) =
* fixed issue: responsive subscribe form
* fixed issue: jQuery was loaded from a different folder on some WP installations
* fixed issue: errors after update (strstr on empty strings because of saving empty lines on exclude list)
* fixed issue: if "Redirection" from "General" tab is active, also redirects ajax calls
* fixed issue: settings page title was wrong placed
* "contact" feature update - nice email template + reply-to email header
* refactoring for some methods
* all assets are now minified
* rewrite count db records function (used on subscribers count)
* compatible with https://github.com/afragen/github-updater
* compatible with wp-cli http://wp-cli.org/
* improved responsivity
* improved roles access; now you can set multiple roles (editor, author, subscriber, contributor) and administrator will always have access to backend and frontend
* it_IT translation by benedettogit (https://github.com/benedettogit)
* updated all language files (need help for 100% translation)


= 2.0.3 (07/10/2014) =
* WP_Super_Cache issue was fixed
* fixed "Subscribe" button issue on Safari mobile
* fixed color of subscribe-success message (same color as subscribe_text)
* "Social networks" module edits: settings for links target + a new social network: linkedin
* new module "Google Analytics"
* loginform shortcode reintroduced
* dashboard link on maintenance page reintroduced
* the content editor accepts new css inline properties: min-height, max-height, min-width, max-width. Use them wisely! :)
* Settings & sidebar view + old translation files edited
* Update from old version 1.x to 2.x issue was fixed
* Translate on activation issue was fixed
* de_DE translation by Frank Bültge (http://bueltge.github.io/)
* pt_PT translation (100% translated) by Pedro Mendonça (http://www.pedromendonca.pt)
* ru_RU translation (100% translated) by affectiosus (https://github.com/affectiosus)
* nl_NL translation by dhunink (https://github.com/dhunink)
* es_ES translation (100% translated) by Erick Ruiz de Chavez (http://erickruizdechavez.com/)
* fr_FR translation by Florian TIAR (https://github.com/Mahjouba91)
* pt_BR translation by Jonatas Araújo (http://www.designworld.com.br/)
* sv_SE translation by Andréas Lundgren (http://adevade.com/)

= 2.0.2 (04/09/2014) =
* Removed "Author Link" option from General
* Countdown - save details fix

= 2.0.1 (02/09/2014) =
* Reintroduced some deprecated actions from old version (but still available in next 4 releases, after that will be removed) and replaced with new ones:
- `wm_head` -> `wpmm_head`
- `wm_footer` -> `wpmm_footer`
* Multisite settings link fix
* WP_Maintenance_Mode: init (array checking for custom_css arrays, move delete cache part into a helper, etc.), add_subscriber, send_contact, redirect fixes & optimizations 
* WP_Maintenance_Mode_Admin: save_plugin_settings fixes, delete_cache (new method)
* Settings & Maintenance views fixes
* Readme.txt changes

= 2.0.0 (01/09/2014) =
* Changed design and functionality, new features
* Changed multisite behavior: now you can activate maintenance individually (each blog from the network has it's own maintenance settings) 
* Removed actions: `wm_header`, `wm_footer`, `wm_content`
* Removed filters: `wm_header` 
* Removed [loginform] shortcode
* Some filters are deprecated (but still available in next 4 releases, after that will be removed) and replaced with new ones:
- `wm_heading` -> `wpmm_heading`, 
- `wp_maintenance_mode_status_code` -> `wpmm_status_code`
- `wm_title` -> `wpmm_meta_title`
- `wm_meta_author` -> `wpmm_meta_author`
- `wm_meta_description` -> `wpmm_meta_description`
- `wm_meta_keywords` -> `wpmm_meta_keywords`
* Added new filters:
- `wpmm_backtime` - can be used to change the backtime from page header
- `wpmm_meta_robots` - can be used to change `Robots Meta Tag` option (from General)
- `wpmm_text` - can be used to change `Text` option (from Design > Content)
- `wpmm_scripts` - can be used to embed new javascript files
- `wpmm_styles` - can be used to embed new css files
- `wpmm_search_bots` - if you have `Bypass for Search Bots` option (from General) activated, it can be used to add new bots (useragents)
* Removed themes and now we have a "Design" & "Modules" tabs, where the look and functionality of the maintenance page can be changed as you need

= 07/07/2014 =
* Switch to new owner, contributor

= 1.8.11 (07/25/2013) =
* Fixes for php notices in strict mode
* Alternative for check url, if curl is not installed

= 1.8.10 (07/18/2013) =
* Add check for urls, Performance topics
* Change default setting of 'Support Link' to false
* Fix network settings php notices

= 1.8.9 (06/20/2013) =
* Allow empty header, title, heading string
* Small code changes
* Add Support function
* Remove preview, will include later in a new release with extra settings page

= 1.8.8 (06/05/2013) =
* Fix path to localized flash content
* Fix preview function
* Add ukrainian translation
* Add czech translation
* Fix exclude function for IP
* Security fix for save status via Ajax

= 1.8.7 (04/07/2013) =
* Add RTL support for splash page
* Add Filter Hook `wp_maintenance_mode_status_code` Status Code; default is 503
* Add support for custom splash page; leave a file with this name `wp-maintenance-mode.php`  in the wp-content; the plugin use this file
  The plugin checks in `WP_CONTENT_DIR . '/wp-maintenance-mode.php'`
* Small minor changes
* Add filter for more date on splash page

= 1.8.6 (02/22/2013) =
* Remove log inside console for JS
* Add support for time inside the countdown
* Add filter hook `wm_meta_author`for the meta data author
* Add filter hook `wm_meta_description` for custom description
* Add filter hook `wm_meta_keywords`for custom meta keys

= 1.8.5 (01/24/2013) =
* Added new settings for hide, view notices about the active maintenance mode
* Changes on source, codex
* Fix PHP Notices [Support Thread](http://wordpress.org/support/topic/error-message-in-settings-1)
* Change default settings, added ajax
* Fix Preview function
* Fix uninstall in WPMU
* Small updates on styles for login form

= 1.8.4 (12/06/2012) =
* Fix for include JS in frontend to use countdown
* Small mini fix for a php notice
* Add charset on spalsh page for strange databases
* Enhanced default exclude addresses
* Add shortcode `[loginform]` for easy use a login form in splash page
* Test with WordPress 3.5
 
= 1.8.3 =
* Fix for the forgotten update of JS-files; slow SVN :(
* Minor Fixes

= 1.8.2 =
* Add different access for Frontend and Backend
* Add Rewrite after Login for Frontend Access
* Different small changes
* Test for WP 3.5

= 1.8.1 =
* Add option for value of robots meta tag
* Add option for optional admin login

= 1.8.0 =
* Include all scripts in backend via function
* Update datepicker and countdown js
* Supported IP as exclude for see the frontend
* Add support for flush cache of WP Super Cache and W3 Total Cache plugins
* Fix for changes in WP 3.3 Multisite

= 1.7.1 (12/05/2011) =
* fix for WP smaller 3.2* on Network

= 1.7.0 (12/02/2011) =
* add functionalities to use in WP Multisite
* remove message in header, current is not fixed the ticked in core and the message on Admin Bar an Notice is enough
* check on WP 3.3RC1

= 1.6.10 (08/30/2011) =
* add hint in Admin Bar, if active
* small changes for WP Codex

= 1.6.9 (06/13/2011) =
* Small fix for empty string on custom design

= 1.6.8 (04/05/2011) =
* Small changes on check for datepicker
* Fix for Design monster

= 1.6.7 (01/05/2011) =
* Bugfix: new check for files for different themes; hope this fix the server errors
* Bugfix: fix add default settings
* Maintenance: different changes on the syntax
* Feature: add check for Super Admin on WP Multisite; has always the rights for access
* Feature: now it is possible to exclude feed from maintenance mode
* Maintenance: check with 3.0.4 and 3.1-RC2
* Maintenance: update language file: .pot, de_DE
* Bugfix: JavaScript error on Bulk Actions on plugins fixed
* Maintenance: fix all notice, if set no values

= 1.6.6. (10/09/2010) =
* Maintenance: many changes on the code; $locale and hook in side frontend
* Maintenance: change attribute_escaped to esc_attr with custom method for WP smaller 2.8
* Maintenance: Update german language files
* Feature: Shortcodes is now possible in the "Text" option
* Feature: no cache header rewrite

= 1.6.5 (09/16/2010) =
* add new design "Chemistry" by [elmastudio.de](http://www.elmastudio.de/ "elmastudio.de")
* changes for include methods of class for preview
* changes the possibility for include of language specific flash files

= 1.6.4 (09/13/2010) =
* add preview functions
* bugfix for list in wp-admin/plugins.php
* remove datepicker.regional - dont work fine
* different small changes
* new language file .pot
* add flash file and change on plugin for style "Animate" for spanish language

= 1.6.3 (07/27/2010) =
* bugfix to include stylesheet on maintenance mode message

= 1.6.2 (07/08/2010) =
* add functions for hint in the new UI of WP 3.0
* add more WP Codex standard source
* fix strings in the language and languages files
* add datetimepicker-de

= 1.6.1 (06/18/2010) =
* fix a problem with https://; see [Ticket #13941](http://core.trac.wordpress.org/ticket/13941)

= 1.6 (05/17/2010) =
* bugfix for exclude sites

= 1.5.9 (05/07/2010) =
* change different points
* add possibility to wotk with MySQLDumper

= 1.5.8 (21/03/2010)=
* fix exclude error
* add textareas for heading and header fields

= 1.5.7 (03/18/2010) =
* block admin-area via role
* add message for registered users with not enough rights
* add message on login-page
* different changes

= 1.5.6 (02/25/2010) =
* changes on css, site.php and different syntax on the plugin

= 1.5.5 (02/23/2010) =
* SORRY, small bug for the url to jQuery

= 1.5.4 (02/23/2010) =
* add time for countdown
* changes for WP 3.0
* changes on rights to see frontend

= 1.5.3 (01/05/2010) =
* Fix for JavaScript with WordPress 2.9
* Add new custom fields for fronted: title, header, heading
* Fix for setting userrole to see frontend
* Change language files

= 1.5.2 (01/04/2010) =
* add user-role setting
* corrected the de_DE language file

= 1.5.1 (10/04/2009) =
* add small fix
* add language files (en_ES, ro_RO)

= 1.5.0 (09/28/2009) =
* add countdown
* change options
* change default options
* add field for own address to excerpt of the maintenance mode
* etc.

= 1.4.9 (07/09/2009) =
* also ready for WordPress 2.6
* add romanian language files
* add italian language file by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 1.4.8 (03/09/2009) =
* add design "Damask" by [Fabian Letscher](http://fabianletscher.de/ "Fabian Letscher")
* add design "Lego" by [Alex Frison](http://www.afrison.com/ "Alex Frison")

= 1.4.7 (26/08/2009) =
* change doc-type to utf-8 without BOM

= v1.4.6 (24/08/2009) =
* add design "Animate (Flash)" by [Sebastian Schmiedel](http://www.cayou-media.de/ "Sebastian Schmiedel")
* add new hook for add content `wm_content` to include flash on content
* add french language files

= v1.4.5 (19/08/2009) =
* fix html string in text on frontend
* add design "Paint" by [Marvin Labod](http://bugeyes.de/ "Marvin Labod")
* add turkish language files

= v1.4.4 (18/08/2009) =
* add design "Chastely" by [Florian Andreas Vogelmaier](http://fv-web.de/ "Florian Andreas Vogelmaier")
* add design "Only Typo" by [Robert Pfotenhauer](http://krautsuppe.de/ "Robert Pfotenhauer")

= v1.4.3 (13/08/2009) =
* add option for the Text
* add option for active maintenance mode
* add design "The FF Error" by [Thomas Meschke](http://www.lokalnetz.com/ "Thomas Meschke")
* add design "Monster" by [Sebastian Sebald](http://www.backseatsurfer.de "Sebastian Sebald")

= v1.4.2 (10/08/2009) =
* add design "The Sun" by [Nicki Steiger](http://mynicki.net/ "Nicki Steiger")
* now it is possible to add own css and add in settings the url to the css-file

= v1.4.1 (07/08/2009) =
* small html-fix

= v1.4 (06/08/2009) =
* completely new code
* options menu
* new designs by [David Hellmann](http://www.davidhellmann.com/ "David Hellmann")
