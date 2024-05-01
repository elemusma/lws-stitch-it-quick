=== Sortable Dashboard To-Do List ===
Contributors: Jeffinho2016,jfgmedia
Tags: dashboard, to-do, task, list, admin
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.4
Requires PHP: 7.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a to-do list to the WordPress dashboard.

== Description ==

The plugin adds a sortable to-do list to your WP dashboard. This can be useful for developers, or even for content writers.

Its features:
<ul>
<li>
To-do list item creation, edition and deletion via ajax. No page reload.
</li>
<li>
To-do items are timestamped. You'll never forget when they were created, or when you last edited them.
</li>
<li>
Option to display the to-do list on the website (for the current logged-in user only).
</li>
<li>
Website list can be collapsed and expanded. But website items can currently NOT be edited or sorted.
</li>
<li>
Website list remembers its last display state (showed or collapsed)
</li>
<li>
Website list remembers the size, position and state of opened to-do items
</li>
<li>
Possibility to decide to not show some to-do items on the website.
</li>
<li>
The list is individual. Each user has their own list.
</li>
<li>
For multisite, it's one list per user and per site.
</li>
</ul>

== Installation ==

1. Visit the Plugins page within your dashboard and select "Add New"
2. Search for "Sortable Dashboard To-Do List"
3. Click "Install"

== Screenshots ==

1. The To-Do List dashboard widget
2. The website list, in its collapsed (left) and expanded (right) forms
3. The website list, with a bunch of tasks opened for consultation

== Upgrade Notice ==
Not available at the moment

== Frequently Asked Questions ==

= Will this plugin slow down my site? =

It will have no impact on site speed whatsoever. The plugin only launches for users that have the ability to edit posts.

== Changelog ==

= 1.0.4 =
* Fix: improved display of &lt;ul&gt; and &lt;ol&gt; lists

= 1.0.3 =
* Fix: Some translatable strings were not on the correct text domain

= 1.0.2 =
* Added an uninstall hook to remove all plugin traces from database on uninstall

= 1.0.1 =
* Added JFG Media as author and contributor

= 1.0 =
* Initial Release