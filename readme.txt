=== Plugin Name ===
Contributors: xevidos: Isaac Brown
Requires at least: 2.9.1
Tested up to: 5.0.3
Stable tag: 1.5.2
Tags: google, google web albums, google private, google plugin, google albums, google wordpress, picasa, picasa web albums, picasa private, picasa cache, picasa plugin, picasa wordpress



TELAALBUMS puts your Google albums on your site in your language!

== Description ==

[TELAALBUMS](https://telaaedifex.com/albums/) is a revitalized version of the PWA+PHP.  Development of PWA+PHP has seemed to died down and there seem to be no followable ties to its developer.  Therefore I have started working to update it to be compatible with the newest versions of PHP and other languages.  I hope to add many new features and remove some old features that no longer work or have been depreciated.

[TELAALBUMS](https://telaaedifex.com/albums/) is a lightweight solution for displaying your public and private (if you choose) Google Photo/Picasa Albums within WordPress Wordpress site.  The plugin, currently developed by Isaac Brown and previously developed by smccandl, provides a guided installer that helps you generate your oauth token and set display options for your albums.

[TELAALBUMS](https://telaaedifex.com/albums/) extends the capabilities of Picasa and Google albums, allowing you to [group albums by keywords](https://telaaedifex.com/utils/support/kb/faq.php?id=13) in the title. Using this capability, you can create several photo pages in WordPress and show different groups of albums on each page. You can even allow users to download full-size copies of your images.

[TELAALBUMS's](https://telaaedifex.com/albums/) configuration options allow you to customize the look and feel of your albums, including thumbnail and image size, images per page, caption settings and display language, without modifying any code. The included CSS file can also be tweaked to your liking for an exact match with your existing website. The div-based layout is fluid and adjusts automatically to fit your theme.
Check out [the demo](https://telaaedifex.com/albums/demos/) to see the code in action.

[](http://coderisk.com/wp/plugin/telaalbums/RIPS-plrwvVky_c)

== Installation ==

1. Extract the archive within the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Open the Settings section and click on TELAALBUMS
1. Complete the setup (token generation and options)
1. Create a new page with contents `[telaalbums]` or `[telaalbums album="album_name"]` or `[telaalbums album="random_photo"]` or `[telaalbums filter="keyword"]` or `[telaalbums tag="sometag"]`
1. Check out the [full guide](https://telaaedifex.com/albums) and [report bugs](https://telaaedifex.com/albums/contact/) as necessary.
1. [View TELAALBUMS's Site](https://telaaedifex.com/albums) for news about issues and new releases.

== Frequently Asked Questions ==

= How do I filter albums? =

See [the wiki entry on filtering](https://telaaedifex.com/utils/support/kb/faq.php?id=13).

= TELAALBUMS is not working with Highslide, why? =

Install [Auto Highslide](http://wordpress.org/extend/plugins/auto-highslide/), it works as expected with TELAALBUMS.

= When I hover over an album, the detail overlay is either too big or too small. Why? =

The CSS file that ships with TELAALBUMS assumes an album thumbnail of 160px.  If you change that size via the settings page, you may need to adjust the CSS settings by changing the 90% and 100% values on the a.overlay:hover line.

== Screenshots ==

1. Main gallery view, description on mouse over 
2. View of images in album
3. Full-size image in a Shadowbox
4. Settings page
5. Hover display option 
6. Overlay display option

== To Do List ==
This list can also be found on our website mentioned in the description.


== Changelog ==
= 1.5.1 =
* Huge update
* Changed over to Google Photos API due to Google shutting down their Picassa Web API
* Improved performance and refactored
* Updated to be compatible with Gutenburg and Wordpress 5.0
* Blocks for Gutenburg are coming in a later update.

= 1.5.0 =
* Changed wording of setting to avoid confusion.
* Fixed issue where some shorcode attributes were not recognized.

= 1.4.9 =
* Fixed issue where albums below limit would not show due to else issue.
* Updated readme with new link.

= 1.4.8 =
* Fixed issue where too many albums blocked request.

= 1.4.7 =
* Second Pagination patch.

= 1.4.6 =
* This should fix the error where single album photos would not show

= 1.4.5 =
* Adds the option to specify headings.
* Fixes google's mess up where spaces are now pluses, (all pluses will now be spaces)
* Fixes the issue where a description would span all photos.

= 1.4.4 =
* Fixes Broken settings link on installed plugins page.

= 1.4.3 =
* Hopefully this will fix the empty until reload error (Same as before)

= 1.4.2 =
* Hopefully this will fix the empty until reload error

= 1.4.1 =
* Changed images per page and photos per page to per_page tag
* Fixed filter tag
* Fixed hide_albums tag
* Fixed pre 1.4 per_page undefined variable issue.

= 1.4 =
* Huge recode!
* Switched over to class methods! Yay!
* [Added in a multiple user feature](https://telaaedifex.com/albums/large-1-4-update/)
* [Added in a slideshow feature](https://telaaedifex.com/albums/demo-slideshow/)
* Fixed multi-slideshow crash
* [Fixed reoccurring albums glitch](https://telaaedifex.com/albums/large-1-4-update/)
* Fixed tag conflicting with plugin name
* Removed some redundant features that did not work

= 1.3.5 =
* Fixed new single album breaking layout of themes

= 1.3.4 =
* Fixed Spelling errors
* Added Slideshow option

= 1.3.3 =
* Added JQuery Slideshow options
* Updated uninstall script

= 1.3.2 =
* Removed Developer Mode until recode is finished
* Updated file access policy to wordpress' standards
* Added a message asking to setup the plugin if it is not configured and a shortcode is already active
* Added an option to show errors
* Fixed include and require links
* Updated to wordpress' policies

= 1.3.1 =
* Fixed Albums per page and Images Per page undefined issue

= 1.3 =
* Completely rewrote the way plugins settings are handled
* Changed The name and all functions, shortcodes, options, and it was very difficult.....

= 1.2.7.1 =
* Changed all TRUE options to 1
* Changed all FALSE options to 0
* Fixed issue where settings would randomly reset especially if you cleared caches. ... It came back....

= 1.2.7 =
* HUGE UPDATE!!!
* Sorted all the settings
* Made a settings Menu
* Made graphics for photos, albums, and settings more pleasing!!!
* Removed some broken, depreciated, and useless settings
* Fixed RSS and support links!
* Added Shadows for Albums and Photos
* Added options to turn off or on all css effects.
* Published a website for the plugin since the origional plugin website is no longer up
* FIXED ABOUT 50 ERRORS, THAT WAS ALL OF THEM, IT TOOK A WEEK AND A HALF!!!!! ...Help me.... just kidding

= 1.2.6 =
* Fixed some css glitches
* Fixed Fatal error when enabling download mode

= 1.2.5 =
* Accidentally set developer mode to default and left it on for everyone ... Whoops ... Thats fixed now.

= 1.2.4 =
* Added a developer mode.  See the developer page on the website for more information on this version.

= 1.2.3 =
* Worked on updating for PHP 7 and higher ... Hopefully it's finished now...
* Replaced some split functions with preg_split and others with explode
* Edited CSS and made more modern

= 1.2.1=
* Fixed some undefined glitches

= 1.2 =
* Worked on updating for PHP 7 and higher
* Fixed some undefined glitches

= 1.1.1 =
* Fixed glitches with new animations on certain themes
* Added an option to turn off new animations.

= 1.1 =
* Added animations to the albums on hover
* Changed Album and photo css
* Added more available sizes for Albums and Photos

= 1.0 =
* Isaac Brown takes over management of the project
* Added custom updater.

= 0.9.14 = 
* Fixed re-auth bug introduced in 0.9.13

= 0.9.13 =
* Check if public only is false before calling oauth2 token refresh

= 0.9.12 =
* Disabled debug mode

= 0.9.11 =
* Fixed typo affecting private albums in showAlbumContents.php

= 0.9.10 =
* Added code to suppress notices from PHP

= 0.9.9 =
* Replaced AuthSub private album authentication with OAuth2.

= 0.9.8 =
* Fixed pagination bug in WP 4.0
* Replaced deprecated split function calls with explode in showAlbumContents.php

= 0.9.7 =
* New: Hide G+ (or any) albums by name via shortcode, e.g. [telaalbums hide_albums="Auto Backup,Another One"]
* Bug fix: News item titles on settings page
* Tested up to WP v3.9.1

= 0.9.6 =
* Bug fix: Issue 145, Fixed caption now shown
* Bug fix: Issue 170, Fixed pagination to support WP 3.4+
* Updated settings page with current pro settings and purchase options

= 0.9.5 =
* Bug fix: Issue 140, Fixes single quote problem for alt attribute.
* Bug fix: Issue 124, Better integration with wptouch
* Bug fix: Issue 122, one_random now supports thumbnail_size override

= 0.9.4 =
* Added News & Announcements to settings page to communicate updates, known issues, and other important information.
* New ability to override the username within shortcode to display any user's public album and albums for different users on each page.
* Bug fix: Issue 98, API Bug causes first image in album to display full size
* Bug fix: Issue 86, Caching broken for some installs
* Bug fix: Issue 83, 404 error when clicking either thumbnail or text link.
* Bug fix: Issue 73, Album title and 'back to album' link not displayed.
* Bug fix: Issue 72, Setting 'Albums Per Page' not saved.
* Bug fix: Issue 78, Paginated Albums Won't Open.
* Bug fix: Issue 79, Drop Box Always Shown

= 0.9.3 =
* Added ability to paginate the albums page for users with large number of albums
* Added ability to override image size, images per page and thumbnail size within the shortcode to allow for individual albums to have specific settings.
* Fixed <a href='http://code.google.com/p/albums/issues/detail?id=63'>Issue 63</a>.

= 0.9.2 =
* Bug fix: fixed issue where username is displayed as title when using tags

= 0.9.1 = 
* Bug fix: removed debug URL echo from the top the album page. User can also delete line: echo $file from dumpAlbumList.php.

= 0.9 =
* Added new shortcode option, 'tag', to display photos matching tags in all albums or in a specific album when paired with 'album' option.
* Added 'Settings' link underneath the plugin name on the Plugins page for easier access to the settings after install
* Updated the settings page target so that the user is returned to the settings page after clicking Update button
* Fixed problem with pagination so that the Pages div is not displayed unless there are multiple pages of results

= 0.8 =
* Added new display mode 'Custom Style' with no hardcoded CSS style in the code. Users have complete control of the look and feel via style.css.
* Renamed "Show Photo Caption" option on settings page to "Display Style"
* Fixed display bug for captions with apostrophes.

= 0.7 =
* Display tweaks for enhanced compatibility with WPtouch iPhone Theme in mobile browsers
* Fixed display bug with Overlay caption mode in Firefox
* Fixed Language variable issue in album cover shortcode when used in sidebar
* Fixed several layout issues for better theme compatibility
* Moved some remaining CSS in PHP files to CSS file for easier customization
* Pro version: Added links to full image in recent comments block
* Pro version: Fixed bug with comment image display

= 0.6 =
* Slick new settings page with WP look and feel
* Option to upgrade to Pro Version for comments, caching and new shortcodes
* Fixed broken v0.5 Hide Video Option
* Added ability to regenerate oauth token w/o remove re-add

= 0.5 =
* New option allows for use of uncropped thumbnails
* New "Overlay" display option, using CSS from [MyPHPDropBoxGallery](http://wiki.dropbox.com/DropboxAddons/MyPHPDropBoxGallery)
* New option to specify [date format](http://php.net/manual/en/function.date.php) on album page
* Updated images per page option to allow any integer value

= 0.4 =
* New option allows for different size album and photo thumbnails
* New options to set trim length for descriptions and captions via settings panel
* Re-designed install panel with settings in groups
* Forced "always" caption mode for IE6 users when using "hover" caption mode

= 0.3 =
* Beta version - added filter functionality to shortcode and caption display options in gallery view

= 0.2 =
* Alpha version - initial release.

= 0.9 =
* New 'tag' option in shortcode, easier access to settings via plugins page, improved settings page behavior, and pagination bug fix.

= 0.8 =
* New "Custom Style" display mode for easy look and feel tweaking. Caption display bug fix.

= 0.7 =
* Improved WPtouch compatibility, various display-related bug fixes

= 0.6 =
* New settings page, fixed broken Hide Video option, new Pro version upgrade available

= 0.5 =
* Support for uncropped thumbnails, new display option, new flexible date format, ability to hide videos

= 0.4 =
* New config option for setting the album thumbnail size, redesigned settings page and ability to set trim lengths on settings page

= 0.3 =
* Upgrade to use filter in shortcode and for caption options in gallery view

= 0.2 =
* Initial release.