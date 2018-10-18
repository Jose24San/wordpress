=== Plugin Name ===
Contributors: smartypants
Donate link: http://smartypantsplugins.com/donate/
Tags: document management, records manager, customer file manager, document manager, project management, file sharing, Dropbox importer, Google Drive importer, enterprise document control, Distribution, Retrieval & storage, Versioning, Productivity
Requires at least: 4.0
Tested up to: 4.5.3
Stable tag: 2.6.2.9

Project & Document Manager. file sharing & management tool to upload, share, track, group, distribute & organize any type of document.

== Description ==

Project & document management plugin,  Businesses & Organization utilizing this application can maintain documents, records, files, videos and images. You can organize, manage client, student & supplier documents and accounts, control individual documents, and select specific distribution of documents all in an easy to manage online process. 

The plug-in also demonstrates how quickly a business can take hold of their interactions with clients, sales organization, vendors, and all in between.  With a straight-forward layout, access to template modifications and easy to manage features; clients can add and modify projects. The plug-in provides assurances that the user has complete control over the flow of information.

Works with WordPress Multi Site!

We now have an extensive documentation library
http://learn.smartypantsplugins.com/

Need support? Click here
http://support.smartypantsplugins.com/

We also now offer a premium version; please check out our website for more information: 
http://smartypantsplugins.com/sp-client-document-manager/

You can try a 30 day trial of the premium along with all the addons here
http://www.documanaged.com/register/


**[Click here to try out a demo](http://smartypantsplugins.com/client-upload-demo/ "Click here to try out a demo")**

Login with:  user: test   password: test

**Industries Served**

* Healthcare, Banking & Finance, Legal, Education ,Consulting, Research firms Government, Architecture, Printers, Photographers, Manufacturing, Chemical, Distributors, Web Developers, Virtual offices, Media to name a few
 
* The client Document manager now includes unlimited folders and folder nesting!

**Overall Features**

* Enhanced file and document security
* Unlimited sub folders and folder nesting
* Ability to choose and upload multiple files
* Delete uploaded documents
* Automatically zip multiple files
* Custom Forms
* Search by file name
* Ability to allow deleting documents and files 
* Renaming of projects
* Disable user uploads (View Only) 
* Disable user deleting of files
* Ability to translate plugin to multiple languages using the .po files.
* File Logging
* Responsive

**Client / Customers**

* Clients upload files and Documents online to their own personal page
* Clients can create or add to existing projects

**Administrator Side Features**

* Complete control on who can access specific files
* Turn off ability for Clients to upload documents instantly.
* Notification via email when a client uploads a file
* Add files and documents to client page and projects
* Download file archive of a user
* Custom Naming of files
* Create multiple upload locations
* 50 latest uploads on main plugin page
* Force downloads of file 
* Delete confirmation with custom notification
* Thank you confirmation with custom notification
* Add staff, supplier's, vendors, sub-contractors or partner's so you can distribute the files to other people
* Attach file or send the file as a link
* Projects allow you or the users to create projects to store files in
* Allow the user to create projects
* Ability to add files to any user
* Download all the files of a project in a single zip file
* Add multiple admin emails to receive files
* Advanced admin file manager
* Assign custom capabilities to user roles
* File Logging

**Premium Features**

* Mobile ready with a updated responsive layout
* Add custom fields to your client upload form, Sort the fields, view them in the file view page or in admin
* Search by tags
* Change text for category (ex: Status)
* Custom Email Notifications
* Auto deletion of files based on a time you set.
* Thumbnail view mode for a windows explorer type look and feel.
* Automatically create thumbnails of pdfs and psds (must have image magick installed on server)
* File versioning system, don't lose old versions 
* Upload multiple files
* File progress bar
* Allow users to collaborate on files by creating groups
* Switch between list view and thumbnail view
* Assign a file or files to a category


**Premium  Add-on Features**

* Standalone Dashboard for your customers - Great for Branding
* Integrate wordpress roles and buddypress groups. When you share a project with a group everyone from that group has access to those files.
* Batch operations - batch delete and move files to different folders. Download files as a zip archive keeping directory structure intact.
* Dropbox integration to allow your users to import files directly from their dropbox
* Google Drive Importer, supports multiple files
* One Drive Importer
* Share projects with buddypress and wordpress roles!
* AES Encryption to secure file data
* Text alert notifications
* Form manager which allows you to build forms to have your users input
* Remote linking to link to external files not part of the document manager
* Local Importer
* Tasks and Reminders for files
 
**Categories**

* Add Categories
* Manage Categories allow an admin to designate categories for the user to select, for example a print company could use categories as statuses (Mockup, Draft and Final)


**Folders**

* Allow a user to create folders
* Collaborate with other users with groups
* Assign a file or files to a project
* Manage Folders
	
**Clients**

* Client can view all categories set by admin

Full Support Available through email or Skype. 
Add-on packs available for more features!


**Current Languages**

* English
* French
* German
* Italian
* Polish
* Spanish

**Demo**

[youtube http://www.youtube.com/watch?v=cEhzmhx9jt8]


== Installation ==

* Upload the plugin to your plugins folder
* Activate the plugin
* Create a new page and enter the shortcode [sp-client-document-manager]  
* Go to the plugin admin page and click settings to configure the plugin  (VERY IMPORTANT!)  
* If you're using the premium version please upload the zip archive in the settings area. 
 
= Short Codes = 
x = configurable area


**[sp-client-document-manager uid="x"]** 

*uid = user id of a user will display that users files

This shortcode displays the uploader
 
**[cdm-link file="x" date="1" real="1"]**
* This links to a specific file

* file = required, this is the file id. You can find the file id in admin under files or by clicking on a file. The ID is listed next to the date.
* date = (set to 1) optional, show the date of a file
* real = (set to 1) optional, generate the real url for the file, the link tags are not generated and only the url is returned. This is good for custom links and image url's

examples:

* [cdm-link file="53" date="1"]
* Will generate a link with the file name and date

'< img src="[cdm-link file="53" real="1"]" width="100">'

Will generate a full url for use in an image

**[cdm-project project="x" date="1" order="x" direction="x" limit="x" ]**

This shortlink will display a unordered list of files, it is a basic html ul so you can use css to display it however you want.

* project = required, this is the project id which you can get in admin under the projects tab.
* date = optional, put's the date of the file next to the file name
* order = (name,date,id,file) optional, use one of the fields to order the list by
* direction  = (asc,desc) optional, Only to be used with order, use asc for ascending order or desc for decending order
* limit = optional, use to limit the amount of results shown.

examples:

* [cdm-project project="1" date="1" ]
* [cdm-project project="1" date="1" order="name" direction="asc" limit="10" ]

= User Role Capabilities = 
If you use "User Role Editor" plugin and want to assign CDM capabilities to another role then please use the following custom captabilities. All are automatically set for administrator

* sp_cdm = You need this role to view the plugin, this is a very minimal role. You can view files, edit and delete.
* sp_cdm_settings = edit settings as well as enable any premium plugin features (in the future we will break premium features into their own roles, just getting started here)
* sp_cdm_vendors = Show vendors tab
* sp_cdm_projects = Show projects tab
* sp_cdm_uploader = Use the uploader (add files)

**[cdm_public_view]**

This is a shortcode for premium members only, it displays the file list to the public. This shortcode lists all the files from all users.

= Premium Users = 

*Premium users must have free + premium version installed. The premium extends the free version.

== Frequently Asked Questions ==

= How come I'm getting a 404 error? =

This could be one of two reasons, either you did not install theme my login or you're running wordpress in a directory in which you can go to settings and set the directory for wordpress.

= Why am I just getting a spinning circle and no content on my uploader? = 

This is usually because you are using a theme that converts new lines into paragraphs. To fix this wrap the short code in raw tags. Example: [raw][sp-client-document-manager] [/raw]

= Is there a conflict with another plugin? =

Sometimes plugins have conflicts, if you are experiencing any abnormal problems there could be a javascript error. Please download and install firebug to find the issue.

= I get an imagemagick error when creating thumbnails of pdf and psd's = 

Imagemagick is a 3rd party plugin you are responsible for, it needs to be downloaded and installed on your server but more importantly, it needs to be compiled into php. Your server admin should be able to handle that, we do not support imagemagick installations.

= I'm using the premium version but not seeing the client document uploader tab in wordpress =

Premium users must have free + premium version installed. The premium extends the free version. Once you install the free version you will see the tab, from there put in your serial code.
= Do you offer capabilities for user roles? = 

* sp_cdm = You need this role to view the plugin, this is a very minimal role. You can view files, edit and delete.
* sp_cdm_settings = edit settings as well as enable any premium plugin features (in the future we will break premium features into their own roles, just getting started here)
* sp_cdm_vendors = Show vendors tab
* sp_cdm_projects = Show projects tab
* sp_cdm_uploader = Use the uploader (add files)

= Why am I getting a permission denied error when activating the premium or trial version? = 

The premium version relies on common functions to operate, please activate the FREE version to fix this error. You must have both FREE and Premium plugins installed and activated.

== Screenshots ==

1. This is the client view
2. This is the file view which also shows the premium revision system
3. This is the admin page view
4. Admin file uploader to upload a file for a user
5. Settings page
6. Form builder to add custom forms (premium)
7. Group manager to allow multiple user manage the same files (premium)



== Changelog ==

= 2.6.2.7 =

* Unlimited sub folders and folder nesting now included with the free version!
* Fixed an issue when editing a document, the saved document would lose its folder.

= 2.6.2.6 =

* Vulernability fix thanks @Tripwire VERT

= 2.6.2.5 = 

* Fixed the admin email

= 2.6.2.4 = 

* Security fix: .htaccess file now protects upload directory on misconfigured servers. Reported by: Guy Theuws  Thanks man!
* Added the ability to disable the protection for non sensitive situations

= 2.6.2.3 = 

* Security exploit fix
* Fixed some email issues

= 2.6.1.4 =

* Security fix
* Multiple files download fix
* Added an event logger which will be implemented plugin wide coming soon.
* Fallback if no file name if given


= 2.6.0.0 = 

* Security fix DOWNLOAD

= 2.5.9.5 =

* Uploads were not inserting due to 4.4, fixed.

= 2.5.8.9 =

* Tabs now rememeber !

= 2.5.8.9 =

* Fixed the view file modal on admin

= 2.5.8.6 =

* Multiple bug fixes
* Fixed the admin uploader when using form fields
* Added quick links to the admin bar

= 2.5.8.1 =

* Fixed a bug that was crashing some installations
* Fixed a security hole in download archive 

= 2.5.7.8 =

* Added a log out button

= 2.5.7.5 =

* Changing ownership of a folder moves the supporting files and folders to the new owner

= 2.5.7.3 = 

* Security fix, please update

= 2.5.7.3 = 

* Fixed download folder archive
* Download folder archive now includes sub folders
* Archives are removed from folder twice daily

= 2.5.7.2 = 

* Fixed some bugs
* Added a bunch of new hooks

= 2.5.6.3 = 

* Bug fix from latest update

= 2.5.6 =

* Major update to the admin uploader, now uses thumbnail mode and reposonsive mode, removed redundent code.

= 2.5.5 =

* Fixed issue with font and ssl
* Fixed issue with projects not loading when notes is enabled

= 2.5.4 =

* Fixed exploit in ajax (credit: rh3792@naver.com)

= 2.5.1 =

* Important patch
* Added the ability to log who deletes files, shown under settings->user logs
* Fixed a bug where spam bots could delete files

= 2.4.4 =

*Important security fix

= 2.4.2 =

* Fix to readfile(); in downloads

= 2.4.1 =

* Mime type addition of microsoft 2013 documents

= 2.4.0 =

* Added the ability to put files in draft mode for premium
* Added the ability to set file retention times for folders.

= 2.3.9 = 

* Added the ability to link directly to the view file console from emails

= 2.3.8 =

* Fixed langugages issues, added Polish and Czech

= 2.3.6 = 

* Fixed download archive in folder view

= 2.3.3 =

* Fixed an error with admin uploader where the folder id was being saved as a cookie and loading that folder for different clients.

= 2.3.2 =

* Fixed an error where folders were being added to the wrong user when using the admin uploader
* Fixed an error which displayed files when accessing ajax files

= 2.3.1 =

* Fixed an error with files not being removed when deleting a folder

= 2.3.0 =

* Fix the deleting of files in admin area, previously was not removing file from server.

= 2.2.9 =

* Made javascript translatable by using localize script
* Removed 2 extra div ends that were breaking some designs.
* Added new terms to the translation files, if you are using a different language please update the .po file and send us the updated version to support@smartypantsplugins.com to be included in future releases

= 2.2.7 =

* Fixed a slashes issue in the email

= 2.2.4 =

* Major release - Document manager is now responsive in premium mode
* Community version also has responsive modals
* Huge code rewrites so if you are going to update premium please update all your smarty plugins or you will recieve errors

= 2.2.0 = 

* Made settings page allot nicer to look at with a tabbed interface
* Fixed Vendor Emails
* Added WP Editor to email section
* Add a custom vendor email to email section
== Upgrade Notice ==

= 2.1.9 =

* More UI enhancements
* Added capabilities to roles
* Updated instructions
* Added link to folder admin while in folder
* Bug fixes

== Upgrade Notice ==

Fixed some language issues