# Lists of Links

Author:      Tim Reed <br>
Tags:        links, affiliate, amazon, associates, shortcode, wordpress, plugin <br>
Requires:    WordPress 6.0+ (tested on 6.9), PHP 7.4 <br>
License:     GPL-3.0-or-later <br>
License URI: https://www.gnu.org/licenses/gpl-3.0.html <br>

## Description

Lists of Links is a WordPress plugin that lets you maintain a database of links and display them on any page using the `[lists_of_links]`, `[lists_of_links_grid]`, or  `[lists_of_links_table]` shortcodes.

## Features

- Save affiliate links with category, title, URL, a one-line blurb, and tags
- Admin interface inside WP Admin to create new links, read all or some, update existing ones, and delete old ones.
- Configure category heading tag (H1–H6) and list style (ul/ol/dl/p, grid, or table)
- Output is sorted by the leftmost columns and links data is organized by category
- Filter links by category or tag
- Choose which columns to display
- Output inherits all styles from your theme
- No link cloaking to comply with Amazon Associates ToS
- It's private and collects no information about your account, site, links, or anything else aside from the link-related data you input
- In the settings page, avoid site demonetization by identifying URLs to include a 'noindex' directive

## Usage

Add the shortcode `[lists_of_links]` to any page or post to display your links using the heading and list styles configured in Settings.

Add `[lists_of_links_table]` to display your links in table format.

Add `[lists_of_links_grid]` to display your links in a bordered grid format.

Manage your affiliate link data:
- **View all link data** in **Lists of Links> All Links**. Click the inline **Edit** button to change the row's data or the **Delete** button to delete the row. 
- **Create a new row** by clicking **Links of Lists> Add New** and adding the category, Link title, affiliate URL, a short descriptive blurb about the product, and comma-separated tags.
- **Save your changes** in Add New by clicking **Save Link**, or save and navigate by clicking **Save & Previous** or **Save & Next**.
- **Navigate without saving** by clicking **Previous** or **Next**
- When called by a shortcode, capitalization of link data displayed as it was when you entered it. **The plug in does no capitalization correction or reformatting** at display time.

Manage how Lists of Links displays data in **Lists of Links> Settings**:
- **Set the paragraph style** that will be used to format Category when called by a shortcode
- **Set the list style** to display links. Available styles are bullets, numbers, definition, and paragraphs.
- **Identify any URL paths** that should not be indexed by search engines. This is to avoid low value content demonetization of pages that include Lists of Links shortcodes. 

All three shortcodes support these optional attributes:

**category** — filters output to a single category. Example: `[lists_of_links category="Health"]`

**tags** — filters output to links that have at least one of the specified tags (OR, not AND, matching). Accepts one or more comma-separated tag words. Tags are matched case-insensitive whole-word. Example: `[lists_of_links tags="fitness"]` or `[lists_of_links tags="health,fitness"]`. Tags are never shown in shortcode output.

**columns** — controls which columns are displayed and their order. Accepted values are `category`, `title`, and `description`, as a comma-separated list. The output is sorted by the leftmost column first, then remaining columns in order. Default is `columns="category,title,description"`. Example: `[lists_of_links_grid category="Health" columns="title,description"]` displays only title and description, sorted by title, for links in the Health category.

## Installation

### Option A: Via WordPress Admin

1. Download `lists-of-links.zip` from the [Releases page](https://github.com/timothyreed1/lists-of-links/releases)
2. In **WP Admin** go to **Plugins> Add New> Upload Plugin**
3. Choose the zip file and click **Install Now**
4. Click **Activate Plugin**

### Option B: Via FTP or file manager

1. Unzip `lists-of-links.zip`
2. Upload the `lists-of-links` folder to `/wp-content/plugins/`
3. Activate via **WP Admin> Plugins**

### After activation

- Open **Lists of Links** in the admin menu to add and manage your links
- Open **Lists of Links> Settings** to configure display options
- Add `[lists_of_links]` to any page or post

## AI Disclosure

Although I designed, coded, and tested this software myself, I used Claude Code to assist during development. I read and understood every line of code.

## Frequently Asked Questions

*Is this compatible with Amazon Associates?*

**Yes. The plugin outputs plain anchor tags with no cloaking, which is required by Amazon's ToS.**

*Can I change the heading level for categories?*

**Yes. Go to Lists of Links > Settings and choose any heading level from H1 to H6.**

*Can I change the list style?*

**Yes. Go to Lists of Links> Settings and choose between bulleted list, numbered list, definition list, or plain paragraphs. You can also use a table or grid layout by using the shortcodes `[lists_of_links_table]` or `[lists_of_links_grid]`

*Where is my link data stored?*

**Data you enter is stored in a plugin-specific table called `lists_of_links` plus whatever table name prefix your site uses.**

*If I remove the plugin, what happens to its data?*

**Removing the plugin from your site will leave the `lists_of_links` table behind, which you can drop manually or find another use for.**
