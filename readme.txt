=== EELV Share Post ===
Contributors: bastho, n4thaniel, ecolosites
Donate link: 
Tags: Post,share,embed,posts,links,multisites,SEO
Requires at least: 3.1
Tested up to: 4.3
Stable tag: /trunk
License: CC BY-NC 3.0
License URI: http://creativecommons.org/licenses/by-nc/3.0/

Share a post link from a blog to another blog on the same WP multisite network and include the post content !

== Description ==

Just share the short-link to display the original post. no more duplicate content, just sharing !

== Installation ==

1. Upload `eelv-share-post` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress network admin

== Frequently asked questions ==

= Can I share on every blogs ? =

no, the plugin just transfrom short-links into post preview. You have to be administrator, author, editor... to post on a blog.

== Screenshots ==

http://ecolosites.eelv.fr/files/2012/11/share.png
http://ecolosites.eelv.fr/files/2012/11/share2.png

== Changelog ==

= 0.4.3 =
* Fix : extends the_excerpt filter to get_the_excerpt to match more themes

= 0.4.2 =
* Fix : Add a network wide restriction

= 0.4.1 =
* Fix : Replace deprecated capability

= 0.4.0 =
* Add : improve sharing action : no more page refresh (Requires jQuery)
* Fix : PHP Warning:  array_key_exists()

= 0.3.0 =
* Add : manage sharing on edit page, select categories on each blog

= 0.2.3 =
* Fix : CSS fix and enhancement

= 0.2.2 =
* Add : new network-admin GUI for domain mapping
* Fix : multi-domain-mapping working properly

= 0.2.1 =
* Fix : domain names with "-" or "." causing js bug

= 0.2.0 =
* Add : support for multi-domain-mapping
* Add : options for preview length
* Add : options for displaying youtube, dailymotion or twitter links
* fix : performances optimisation

= 0.1.5 =
* fix : do not forget anymore the last site in the sharing list

= 0.1.4 =
* add : icon for extra-link in excerpt
* fix : only one thumbnail, if !has_post_thumbnail()

= 0.1.3 =
* fix : performances optimisation

= 0.1.2 =
* fix : performances optimisation

= 0.1 =
* plugin creation

== Upgrade notice ==

No particular informations