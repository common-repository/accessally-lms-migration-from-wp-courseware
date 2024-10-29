=== AccessAlly™ LMS Migration from WP Courseware® ===

Contributors: accessally
Plugin Name: AccessAlly™ LMS Migration from WP Courseware®
Plugin URI: https://accessally.com/
Donate link: https://accessally.com/
Author URI: https://accessally.com/about/
Author: AccessAlly
Tags: lms, lms migration, WP Courseware® migration, accessally migration, export WP Courseware, migrate lms, switch lms, export lms, import lms, access ally, accessally
Tested up to: 5.5.3
Requires at least: 4.7.0
Requires PHP: 5.6
Version: 1.0.1
Stable tag: 1.0.1
License: Artistic License 2.0

This AccessAlly™ LMS Migration from WP Courseware® plugin will convert your existing WP Courseware® courses into AccessAlly courses, so you don't lose your content when you disable WP Courseware®.

== Description ==

**LMS Migration Plugin From WP Courseware® to AccessAlly™**

We created this LMS migration plugin to help anyone who currently has courses set up in WP Courseware® and wants to import them into AccessAlly's Course Wizard. 

The plugin will convert modules and lessons into corresponding ones in AccessAlly, and you won't lose your materials when you disable your WP Courseware plugin. 

You can also undo your changes, if you wanted to make changes in your WP Courseware courses before switching to AccessAlly courses. 

It's important to note that this plugin does not import things like quizzes, assignments, certificates, etc. These will need to be re-created inside of AccessAlly. 

This plugin also does not import or convert existing student data into AccessAlly's format.


###  How the LMS Migration Process Works ###

Find out how the process works for migrating from one learning management system to another.

* **Install the migration plugin on your WP Courseware site**
You'll need to have an active license with WP Courseware and [AccessAlly](https://accessally.com/). From there, simply install the WordPress plugin and activate it. Then navigate to the converter page.

* **Choose Which Courses to Migrate**
You can migrate all courses, or pick and choose which courses you'd like to convert into AccessAlly courses. You'll also see any "unassigned" lessons or content that were created in WP Courseware but that aren't part of a course. You can convert those too.

* **Convert to Standalone or Stage Release Courses**
You decide how you want your courses to be permissioned in AccessAlly: as a standalone course, where everything is immediately unlocked. Or as a stage released course, where each module can be unlocked over time or on a schedule. You can also convert to regular WordPress pages.

* **Edit Courses**
Once the LMS migration is complete, you can navigate through AccessAlly's Course Wizard to add course icons, create tags in your CRM, and test that everything is working. You'll also need to re-create any quizzes, certificates, and assignments. 

* **Disable Plugins**
If you're happy with how everything looks in AccessAlly you can disable both the LMS migration plugin and WP Courseware plugins. But before you do, make sure to follow the [member migration steps](https://kb.accessally.com/tutorials/migration-wizard-plugin-download/) to make sure your members can access the new version of your courses.


### Differences Between WP Courseware and AccessAlly Courses ###

WP Courseware includes a 3-tier course format: Courses, Lessons, and Topics.

With AccessAlly courses, the focus is not on the tiers (since you use regular WordPress pages, you get to decide how “deep” the course goes). Rather, the focus is on the access permission tags, and whether you want to release all course content at once, or “drip” it out slowly over time. 

This plugin was not developed by the WP Courseware® team, and is maintained by the AccessAlly team. Any questions should be directed to AccessAlly.


= Getting Started: =

**<a href="https://kb.accessally.com/tutorials/migration-guide/" target="_blank">1. LMS Migration Guide:</a>** When migrating platforms, you'll want to zoom out and look at all of the impacts of a move. It's possible there are other things you need to update or migrate, including payment systems or permissions. This guide will walk you through identifying these items, and how to make the move.

**<a href="https://kb.accessally.com/tutorials/wp-courseware-migration/" target="_blank">2. Step-by-step WP Courseware Migration:</a>** Follow the step-by-step WP Courseware migration tutorial with screenshots to ensure the smoothest transition to AccessAlly.

**<a href="https://accessally.com/contact/" target="_blank">3. Get In Touch:</a>** If you run into any issues or you'd like to ask any questions before undertaking a migration from WP Courseware to AccessAlly, you can contact the AccessAlly team and we'll be happy to help.

== Installation ==
=== From within WordPress ===

1. Visit 'Plugins > Add New'
2. Search for 'AccessAlly™ LMS Migration from WP Courseware®'
3. Install the plugin once it appears
4. Activate it from your Plugins page.
5. Go to "after activation" below.

=== After activation ===

1. You should see the AccessAlly WP Courseware Conversion plugin.
2. Click through and decide which courses you want to migrate
3. Follow the [steps in the tutorial](https://kb.accessally.com/tutorials/wp-courseware-migration/)

== Frequently Asked Questions ==

= Will this plugin convert WP Courseware's custom post types? =
Yes, with this plugin you'll be able to convert the WP Courseware Custom Post Types into regular WordPress pages, and add the appropriate structure and permissions within the AccessAlly course structure. 

= Will this convert WP Courseware quizzes, assignments, and certificates into AccessAlly ones? =
No, this plugin is solely to convert page content. It will not turn WP Courseware quizzes, assignments, certificates or other LMS specific content into the equivalent AccessAlly ones. You'll need to re-create your quizzes and certificates, and set up new private notes for assignments.

= Will my students automatically be converted into AccessAlly students? =
No, this plugin only handles converting WP Courseware course content including modules, lessons, and topics into AccessAlly course equivalents. In order to convert your WordPress users from WP Courseware ones to AccessAlly ones, you'll want to [follow the member migration tutorial](https://kb.accessally.com/tutorials/migration-wizard-plugin-download/). 

= Will I need to send new passwords to members? =
It's quite likely when switching [WordPress membership management plugin](https://accessally.com/features/membership-management/) that you'll need to send students a new password to login to your site. Depending on what system you're using in conjunction with WP Courseware or WP Courseware itself, you can set up a welcome email sequence in your email service. More details in the full [migration guide](https://kb.accessally.com/tutorials/migration-guide/).

= Do I need another membership plugin to work with AccessAlly? =
No. AccessAlly includes everything you need to run your courses, including the membership management and payment functionality on top of the LMS features.

= Is it possible to revert from AccessAlly courses back to WP Courseware courses? =
Yes, if you started with a WP Courseware course and used this plugin you'll be able to convert an AccessAlly course back into a WP Courseware course. However, you can't use this plugin to convert a new AccessAlly course into a WP Courseware course. 


== Screenshots ==

1. See existing WP Courseware courses
2. Decide what type of conversion you want to perform.
3. Simple LMS migration with the click of the convert button.
4. Once the conversion is complete, you can revert or click to edit the course directly in AccessAlly's course wizard.
5. Just follow the steps through the AccessAlly Course Wizard to finish setting up your course, and make any updates to your course organization.
6. You'll be able to edit and save your new posts and make changes from there.


== Changelog ==

= 1.0.1 =
* Show unassigned WP Courseware lessons/ topics, so they can all be converted to regular WordPress pages.

= 1.0.0 =
* This is the first version of the AccessAlly™ LMS Migration from WP Courseware® plugin, and it includes the functionality to convert WP Courseware Custom Post types into AccessAlly courses and WordPress pages.

