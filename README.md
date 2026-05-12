# Lists of Links

Author:      Tim Reed <br>
Tags:        links, affiliate, amazon, associates, shortcode, wordpress, plugin <br>
Requires:    WordPress 6.0+ (tested on 6.9), PHP 7.4 <br>
License:     GPL-3.0-or-later <br>
License URI: https://www.gnu.org/licenses/gpl-3.0.html <br>

## Description

Lists of Links is a WordPress plugin that lets you maintain a database of links and display them on any page using the `[lists_of_links]` shortcode.

## Features

- Store links with category, title, URL, and a one-line blurb
- Admin CRUD interface inside WP Admin
- Configure category heading tag (H1–H6) and list style (ul/ol/dl/p)
- Sort categories and items within categories alphabetically
- Output inherits all styles from your theme
- No link cloaking to comply with Amazon Associates ToS
- It's private and collects no information about your account, site, links, or anything else aside from the link-related data you input. 

## Usage

Add the shortcode `[lists_of_links]` to any page or post to retrieve and expand your link data.

## Installation

**Option A — Via WordPress Admin**
1. Download `lists-of-links.zip` from github>
2. In WP Admin go to **Plugins > Add New > Upload Plugin**
3. Choose the zip file and click **Install Now**
4. Click **Activate Plugin**
**Option B — Via FTP or file manager**
1. Unzip `lists-of-links.zip`
2. Upload the `lists-of-links` folder to `/wp-content/plugins/`
3. Activate via **WP Admin > Plugins**
**After activation**
- Open **Lists of Links** in the admin menu to add and manage your links
- Open **Lists of Links > Settings** to configure display options
- Add `[lists_of_links]` to any page or post

1. Clone or download this repo
2. Upload the `lists-of-links` folder to `/wp-content/plugins/`
3. Activate via WP Admin > Plugins
4. In the admin page, open **Lists of Links** to add and manage your links
5. Open **Lists of Links > Settings** to configure how data is displayed and update link content

## Frequently Asked Questions

*Is this compatible with Amazon Associates?*

**Yes. The plugin outputs plain anchor tags with no cloaking, which is required by Amazon's ToS.**

*Can I change the heading level for categories?*

**Yes. Go to Lists of Links > Settings and choose any heading level from H1 to H6.**

*Can I change the list style?*

**Yes. Go to Lists of Links > Settings and choose between bulleted list, numbered list, definition list, or plain paragraphs.**

*Where is page data stored?*

**Data you enter is stored in a plugin-specific table called `lists_of_links` plus whatever table name prefix your site uses. 

*If I remove the plugin, what happens to its data?*

*Removing the plugin from your site will leave the `list_of_links` table behind which you can drop manually*
