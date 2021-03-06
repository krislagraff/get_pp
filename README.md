#get_pp WordPress Shortcode Plugin

This plugin implements WordPress' [get pages](http://codex.wordpress.org/Function_Reference/get_pages) and [get posts](http://codex.wordpress.org/Function_Reference/get_posts) functions as a WordPress shortcode with almost no intervention. Everything can be overridden with filters to allow total flexibility for developers. __This plugin requires coding knowledge.__

After installing, you would simply add something like this to your content:

`[getpp child_of="top" func="get_pages"]` would list all pages in the tree

`[getpp func="get_posts" category="parent" template="summary"]`   
would list all posts in the same category using the summary template


Types of plugins this can replace:

* recent posts widget / shortcode
* archived posts widget / shortcode
* list pages widget / shortcode 
* list posts widget / shortcode
* etc.

##How To Use
Most of the documentation is on the WordPress Codex: [get pages](http://codex.wordpress.org/Function_Reference/get_pages) and [get posts](http://codex.wordpress.org/Function_Reference/get_posts)

###Arguments

1. You must have `func=` in the shortcode (either `get_pages` or `get_posts`).  This tells the plugin which function to use.
2. You can use `this`, `parent`, or `top` when appropriate.  More info below.
3. You can create your own templates and specify them with `template=`

###Dynamic replacement

The words this, parent, and top will be dynamically replaced with the appropriate item.  For the following, suppose we have the following page structure  

- 135
- - 136
- - 137
- - - 138
- - - - 139 - current item
- - 140 

####This

`this` will be replaced with the current item's `ID` or `catid` depending on the context.  

ex: `[getpp child_of="this" func="get_pages"]` would evaluate something like `get_pages('child_of=139');`

####Parent

`parent` will be replaced with the current item's parent `ID`.  If you want to go up multiple levels, you can specify it like this: `parent_parent_parent`  

ex:  
`[getpp child_of="parent" func="get_pages"]` would evaluate to  `get_pages('child_of=138');`  
`[getpp child_of="parent_parent" func="get_pages"]` would evaluate to `get_pages('child_of=137');`  
`[getpp child_of="parent_parent_parent" func="get_pages"]` would evaluate to `get_pages('child_of=135');`


####Top

`top` will replaced with the top level item's `ID` for the current tree  

ex: `[getpp child_of="top" func="get_pages"]` would evaluate to  `get_pages('child_of=135');`  

##Depth

You can specify the depth for hierarchical results.   Simply add `depth=0` to show no children.  `depth=1` for immediate children, and so on.

##Templates

The plugin currently only has a default template which lists out the posts.  You can add more templates easily.  Simply add `template="%yourtemplatename%"` to the shortcode.  The plugin will then look for a filter named `getpp_template_%yourtemplatename%`.  You can then create the filter in your `functions.php` and have it render however you like!`.  The default template assigns classes based on the [Bootstrap framework](http://twitter.github.com/bootstrap/).

## Changelog

###0.1
* Initial plugin

##Contributing
If you have a useful template to add, share it with a pull request!  
