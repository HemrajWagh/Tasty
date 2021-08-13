=== Album and Image Gallery with Lightbox - Flagallery Photo Portfolio ===
Contributors: pasyuk
Donate link: https://codeasily.com/donate/
Tags: best gallery plugin, gallery, image gallery, photo gallery, slider
Requires at least: 4.6
Tested up to: 5.6
Stable tag: trunk

Gallery Portfolio, Photo Gallery, Photo Albums with Multi Galleries and Lightbox plugin.

== Description ==

Gallery Grand Flagallery - powerfull media and image gallery plugin. Easy interface for handling photos and image galleries. You can create a beautiful video gallery from YouTube, Vimeo.

**FREE Skins:**

* [Amron](https://mypgc.co/portfolio-item/amron/)
* [AlbumWiz](https://mypgc.co/portfolio-item/albumwiz/) - skin will allow you to easily organize your galleries into an album. For each gallery in the album 1 image would show. When you click on the cover of the gallery, the contents of the gallery will be shown in the Lightbox.
* [AlbumNavigator](https://mypgc.co/portfolio-item/albumnavigator/) - AlbumNavigator will allow you to easily organize your galleries into an album. For each gallery in the album 1 image would show. When you click on the cover of the gallery, the contents of the gallery will be shown in the Gallery Modal.
* [Horizon](https://mypgc.co/portfolio-item/horizon/) - horizontally oriented gallery with Album support (Album Filter). Convenient presentation of YouTube, Vimeo playlists.
* [Paginator](https://mypgc.co/portfolio-item/paginator/) - for those who need pagination in the gallery.
* [Phantom](https://mypgc.co/portfolio-item/phantom/)
* [PhotoMania](https://mypgc.co/portfolio-item/photomania/)
* NivoSlider

**PREMIUM Skins:**

* [FlaSlider](https://mypgc.co/portfolio-item/flaslider/)
* [Grid](https://mypgc.co/portfolio-item/cicerone/)
* [Masonry Grid](https://mypgc.co/portfolio/cicerone-masonry/)
* [Justified Grid](https://mypgc.co/portfolio/cicerone-justified/)
* [WoowSlider](https://mypgc.co/portfolio-item/woowslider/)
* [Desire](https://mypgc.co/portfolio-item/desire/)
* [Cubik](https://mypgc.co/portfolio-item/cubik/)
* [AlbumsSwitcher](https://mypgc.co/portfolio-item/albums-switcher/)
* [PhotoCluster](https://mypgc.co/portfolio-item/photocluster/)
* [Mosaic](https://mypgc.co/portfolio-item/mosaic/)
* [PhotoBox](https://mypgc.co/portfolio-item/photobox/)


With this gallery plugin you can easy upload images, create photo gallery, group pictures in photo slideshow and add descriptions for each image - Grand Flagallery is the smart choice when showing the best of your product or describing in brief any event. Grand Flagallery can easily beautify your site with **photo gallery or nice slideshow widgets**. SEO optimized, compatibility with all major browsers.

**[How to add gallery to your Wordpress site](https://mypgc.co/how-to/)** - full HowTo guide.

https://www.youtube.com/watch?v=JQDD-gFiLrA

For more information read **[Review, Tutorials, FAQ](https://mypgc.co/ "Grand Flagallery Home Page")** and see demos created with "Grand Flagallery" WordPress Plugin.

'Custom Links' feature support in gallery skins. [See PhotoMania Skin demo](https://mypgc.co/portfolio-item/photomania/).

* [Portfolio of Photo Gallery Skins](https://mypgc.co/demo/)

GRAND Pages - Full Window Gallery Template. You can display your image gallery in full window separate page (gallery template).
[View Demo 1](https://mypgc.co/flagallery/photomania-grand-page/), [View Demo 2](https://mypgc.co/flagallery/mosaic-grand-page/)

You have the opportunity to create image gallery, as separate pages. It looks very cool when you open image gallery, and gallery images occupies the entire page... such a large and beautiful. Wow! And there is a button with which you can go back to the previous page. You can also specify the name of the button and link.

See "Views" of each photo in the gallery and allow visitors to "Like" photos.

* iPhone, iPad, Android and Desktop friendly photo gallery, SEO optimized.
* Full-screen mobile friendly gallery slideshow with touch controls.
* Widgets, Video (YouTube, Vimeo)
* **Gutenberg ready!**

Designed to work for WordPress 5 (Gutenberg Block) and also the standard Gallery Shortcode.

== Changelog ==

= v6.0.2 - 17.11.2020 =
* Fixed few PHP Notices

= v6.0.1 - 28.10.2020 =
* Fixed compatibilty with jQuery Migrate Helper plugin
* Fixed presets selectbox in Gutenberg

= v6.0.0 - 15.10.2020 =
* Updated Gutenberg block with abiblity to edit Skin presets and gallery preview
* Removed Music Box, Video Box, Banner Box
* Added options to show above pages in admin
* Fixed CSS in admin panel

= v5.8.0 - v5.9.1 =
* Fixed PHP Notice
* Fixed Edit Thumbnail
* Fixed draft galleries not showed in admin
* Added noscript with images for search engine optimization
* Fixed Widget for GRAND Pages
* Added Shortcode generator on Manage Galleries page
* Added option to remove ads
* replaced WoowBox with WoowGallery plugin
* Add .jpeg file format to Plupload uploader
* Improvement: create tables if not exist
* Fix deprecated function
* Make default gallery sort by ID in the admin panel
* Updated skins with slideshow bugfix (Paginator, Amron, AlbumWiz, Horizon, AlbumNavigator)

= Upgrade Notice =
* After plugin update go to Skins page and update skins with 'Update skins' button.
* If you use Facebook template and copied it in the root directory, then after each plugin update click 'Copy facebook.php file to root directory'.
* If Grand Flagallery displays an error message after upgrade, go to FlAGallery Overview page and press 'Reset settings'.
* Have some troubles with plugin? Try first reseting settings, deactivate and reactivate plugin.

== Installation ==

1. Upload the files to 'wp-content/plugins/flash-album-gallery'.
2. Activate the plugin.
3. Be sure that after activation 'wp-content/plugins/flagallery-skins' folder (chmod 755) created successfully. If not, create it manually and install skins through admin Skins page or via ftp.
3. Add a gallery and upload some images (the main gallery folder must have write permission).
4. Go to your post/page an enter the tag '[flagallery gid=X]', where X - gallery IDs separated by comma. Easy way is click FlAGallery button on the Editor panel.
5. If you would like to use additional Skins (only a option), go to <a href="http://photogallerycreator.com/grand-flagallery/" title="Skins">Skins</a>, download the skin and upload the file through Skins page in Wordpress admin panel.

See more tags in the FAQ section

That's it ... Have fun!

== Frequently Asked Questions ==

= Read as startup : =
Home page: https://mypgc.co/

= The gallery didn't work, but everything is installed and activated. =

Make sure you have the following in your template. (It's in the original WP header.php template, but if you're creating your own, you may have forgotten to include it):

<?php wp_head(); ?>

That line would go in between your <HEAD> </HEAD> tags

= When I try to activate the plugin I get the message : "Plugin could not be activated because it triggered a fatal error." =

This problem could happened if you have a low memory_limit in your php environment and a lot of plugins installed. For a simple test deactivate all other plugins and try then to activate Grand Flagallery again. Please check also if you have a minimum memory_limit of 16Mbyte (as much as possible).

= I get the message "Fatal error: Allowed memory size of xxx bytes exhausted" or get the "Error: Exceed Memory limit.". What does this means? =

This problem could happened if you have a low memory_limit in your php environment or you have a very large image (resolution, not size). The memory limit sets the maximum amount of memory in bytes that a script is allowed to allocate. You can either lower the resolution of your images or increase the PHP Memory limit (via ini_set, php.ini or htaccess). If you didn't know how to do that, please contact your web hoster.

= When I open the archive/category page of my site it will show the post, but without gallery. But if I open the post, it will shows the gallery perfectly =

It's because your theme use some function different from <?php the_content('Read more'); ?> to display post excerpt in index.php or archive.php or category.php. If you insert "more" (page breack) tag in post before shortcode, then flash will be only on individual page.

= How to create a categories in album? I only have one category, which is the name of the gallery. =

You can do it easily with FlAGallery button on editor panel, when you edit post. In trhe popup window hold down 'Ctrl' button and choose galleries left mouse button. Or just write gallery IDs separated by comma in "gid" attribute: [flagallery gid=7,3,5,2]

To display ALL galleries as categories: [flagallery gid=all]

= How can I set it to auto slideshow when open the page? =

Download and activate skin with auto slideshow
https://mypgc.co/portfolio/

= How do I set a specific category as the first one that is displayed? =

if you have three galleries and shortcode like: [flagallery gid=3,1,2]
first gallery will be with ID=3, then ID=1, and then ID=2
if you have: [flagallery gid=all orderby=title order=ASC exclude=1]
it will display all galleries except ID=1 sorted by title of gallery.

= I get this error code: ERROR: IMG_5879.JPG : Invalid upload. Error Code : 1. =

Error Code: 1. This mean that the uploaded file exceeds the upload_max_filesize directive in php.ini.
Check PHP Max Upload Size on Overview page.

= When i install this plugin and go to add a gallary name, it gives an error that 'directory wp-content/flagallery doesnot exists' =

Create it manually with chmod 0755.

= Live Demos: =

https://mypgc.co/demo/
