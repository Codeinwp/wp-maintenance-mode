# WP Maintenance Mode

Adds a splash page to your site that lets visitors know your site is down for maintenance. Full access to the back- & front-end is optional. Works also with WordPress Multisite installs.

## Description
Adds a maintenance-page to your blog that lets visitors know your blog is down for maintenancetime. User with rights for theme-options get full access to the blog including the frontend.
Activate the plugin and your blog is in maintenance-mode, works and see the frontend, only registered users with enough rights. You can use a date with a countdown for informations the visitors or set a value and unit for infomrations.
Also you can add urls for exlude of maintenance mode.

You can add your own html and stylesheet and add the url of this style to the options of the plugin. Write your style to this markup and upload to the webspace; after add the url include http:// to the settings of this plugin and change th theme to `"Own Theme"`:

	
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" id="wartungsmodus" >
	
	<head>
		<title>Blogname - Maintenance Mode</title>
	</head>
	
	<body>
		
		<div id="header">
			<p>WP Dev</p>
		</div>
		
		<div id="content">
		
			<h1>Maintenance Mode</h1>
			<p>Sorry for the inconvenience.<br />Our website is currently undergoing scheduled maintenance.<br /><strong>Please try back in 231 weeks.</strong><br />Thank you for your understanding.</p>
			<div class="admin"><a href="http://example.com/wp-admin/">Admin-Login</a></div>
		</div>
		
		<div id="footer">
			<p><a href="http://bueltge.de/">Plugin by: <img src="http://bueltge.de/favicon.ico" alt="bueltge.de" width="16" height="16" /></a></p>
		</div>
		
	</body>
	</html>
	

Also you can add content via hook:

 * `wm_head` - hook inside the head of the maintenance mode site
 * `wm_content` - hook over the content, after the div with id content
 * `wm_footer` - hook inside the footer


**Example:**
	
	function add_my_link() {
		echo '<a href="http://mylink.com/">My Link</a>
	}
	add_action( 'wm_footer', 'add_my_link' );

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
