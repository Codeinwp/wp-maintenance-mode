# WP Maintenance Mode

Adds a splash page to your site that lets visitors know your site is down for maintenance. Full access to the back- & front-end is optional. Works also with WordPress Multisite installs.

## Description
Adds a maintenance-page to your blog that lets visitors know your blog is down for maintenancetime. User with rights for theme-options get full access to the blog including the frontend.
Activate the plugin and your blog is in maintenance-mode, works and see the frontend, only registered users with enough rights. You can use a date with a countdown for informations the visitors or set a value and unit for infomrations.
Also you can add urls for exlude of maintenance mode.

Use the shortcode `[loginform]` for easy use a login form on the maintenance page.

### Adding Custom CSS
In plugin settings, choose the "Own CSS Style" in the CSS Style dropdown. Then enter the full URL to your stylesheet in the textbox underneath.

### Custom HTML
You can add your own html by dropping a `wp-maintenance-mode.php` file in the wp-content folder. It will automatically be used instead of the default html.
The default html Markup and my source for countdown and more find you in the `site.php` inside the plugin folder of this plugin.

### Custom Content
You can also add content via these hook:

 * `wm_head` - hook inside the head of the maintenance mode site
 * `wm_content` - hook over the content, after the div with id content
 * `wm_footer` - hook inside the footer


**Example:**
	
	function add_my_link() {
		echo '<a href="http://mylink.com/">My Link</a>
	}
	add_action( 'wm_footer', 'add_my_link' );

More hooks for meta data inside the head. The `<meta>` tag provides metadata about the HTML document. Metadata will not be displayed on the page, but will be machine parsable. You can change the data for 3 different meta data values:

 * `wm_meta_author` - Define the author of a page
 * `wm_meta_description` - Define a description of your web page
 * `wm_meta_keywords` - Define keywords for search engines

More hooks for other data, if the settings possibilities is not enough.

 * `wm_title` - Filter the title on splash page
 * `wm_header` - Filter for header string on splash page
 * `wm_heading` - Filter for the heading string

## Other Notes
### License
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

### Translations
The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the .pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Linux, Mac OS X, Windows).

### Contact & Feedback
The plugin is designed and developed by me ([Frank Bültge](http://bueltge.de))

Please let me know if you like the plugin or you hate it or whatever ... Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there. So please be patient with me and let me know of typos or grammatical farts. Thanks
