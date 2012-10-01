# Wadoo

An XML/XSLT static site generator written in PHP.


## Introduction

The ultimate goal of Wadoo is to create a folder full of static files that you can deploy anywhere. 
These are the steps involved in generating a website:

1. Create a `data.xml` file that will be used as the source document for **all** the transformations.
2. Create a `sitemap.xml` file that defines all the files that have to be generated and the different 
stylesheets you want to use for the transformations.
3. Compile any (or all) the resources present in the sitemap and enjoy the new website.

That's Wadoo from a really high level perspective. It is not blog-aware (though it can certainly power a blog) 
nor it forces any kind of url structure. Just do what you want with it.


## Installation

Wadoo requires PHP 5.3 and [composer](http://getcomposer.org/) for managing external dependencies.

There's very little to do to start using wadoo, just run `composer install` inside the root folder and you're 
good to go. There's a `sample.htaccess` (that have to be renamed) inside `public/` where you have 
to change `RewriteBase` according to your installation path.


## Usage

Unlike most static site generators out there, Wadoo doesn't have a cli tool to interact with, nor it provides a 
development server that automatically compiles your stuff as soon as you save a file.

Wadoo requires a webserver to run but this doesn't mean you'll find cool looking buttons to click :)

You can invoke different actions through the `?action` GET parameter. For example, to compile the file `about.html` you'll call:

    index.php?action=compile&resource=about.html

That will (re)compile the file `about.html`. Of course the `index.php` part can be skipped. Take a look at 
the [Compilation tips and tricks](#compilation-tips) section for a simplified compilation workflow.

**NOTE**
Appending `&echo` to (almost) any action will cause Wadoo to print on the screen the result of the transformation 
and not writing it to disk. This is extremely useful for testing when you don't want to have the file constantly 
updated but just want to see what is going on in your browser.

The following are all the available actions explained.


### Merge data

    ?action=merge-data

All your data is stored inside `data.xml`. Depending on the project, this file can become quite large and a pain 
to update. That's why it can be generated automatically.

You can create a `data` folder and put all your stuff into it. There's no file naming convention to follow and 
you can go as deep as you wish with your folders. All the files found will be merged into a single, big file.

Given a folder structure like the following:

    data/
    -- portfolio/
    -- -- design.xml
    -- -- development.xml
    -- -- content-management.xml
    -- pages/
    -- -- about.xml
    -- -- home.xml
    -- blog/
    -- -- 2012/
    -- -- -- this-blog-has-moved.xml
    -- -- -- first-post.xml

Wadoo will generate something like this:

	<?xml version="1.0"?>
	<data>
	  <folder path="portfolio">
	    <file filename="design.xml">
	      [design.xml content]
	    </file>
	    <file filename="development.xml">
	      ...
	    </file>
	    <file filename="content-management.xml">
	      ...
	    </file>
	  </folder>
	  <folder path="pages">
	    <file filename="about.xml">
	      ...
	    </file>
	    <file filename="home.xml">
	      ...
	    </file>
	  </folder>
	  <folder path="blog">
		  <folder path="blog/2012">
			<file filename="this-blog-has-moved.xml">
			  ...
			</file>
			<file filename="first-post.xml">
			  ...
			</file>
		  </folder>
	  </folder>
	</data>

Remember that the folder structure you use in the `data` directory has nothing to do with the final url structure,
which is instead defined in the sitemap.

### Sitemap

    ?action=sitemap&template=sitemap.xsl

As for the data, you can write your sitemap by hand or have Wadoo to generate it. A sitemap has essentially this structure:

	<sitemap>
	  <resource uri="index.html" template="templates/index.xsl"/>
	  <resource uri="blog/index.html" template="templates/list.xsl"/>
	  <resource uri="blog/2012/bikes/index.html" template="templates/entry.xsl" handle="bikes"/>
	  <resource uri="blog/2012/wing/index.html" template="templates/entry.xsl" handle="wing"/>
	  <resource uri="blog/2012/bridge/index.html" template="templates/entry.xsl" handle="bridge"/>
	  <resource uri="blog/2012/road/index.html" template="templates/entry.xsl" handle="road"/>
	</sitemap>

Every `resource` node maps to a file that have to be compiled. The `@uri` attribute specifies the generated file 
name while `@template` is the stylesheet to be used during that specific transformation.
Any additional attribute will be provided as a parameter during the tranformation so that it can be easily 
accessed through `$param` syntax.

If you have dynamic content you clearly want to automate the sitemap generation, otherwise everytime you add i.e. 
a blog post you'd have to open the sitemap file and append the correct resource for that specific post.

When calling this action you have to provide an additional parameter `template` which will 
be the stylesheet used to generate the sitemap.


### Compile

    ?action=compile&resource=uri

This is the core of Wadoo. While you can (sort of) live without the other two actions, this one is 
required to compile the website. By default, the website will be compiled inside the `public` folder.

If the `resource` parameter isn't provided, Wadoo will compile the whole website (every resource 
present in `sitemap.xml`). Specifying the resource will make Wadoo compile 
just that single file. That's all there is to it, really.


### Executing more than one action at once

    ?action=merge-and-compile  (merge-data + compile)
    ?action=full-compile       (merge-data + sitemap + compile)

It can be useful at times to invoke more than one action with a single call. Let's say you're editing 
a post and you're checking how it is rendering on screen. If you change the content of the post you'd 
need to first re-merge `data.xml` and then compile the associated 
resource. You can do this all at once using `merge-and-compile`.

Now say the post you're editing has a `title/@handle` that you're using in your sitemap as the resource uri. 
If you don't recompile the sitemap you won't be able to see the updated post – that's when a `full-compile` can be handy.


### <a name="compilation-tips"></a> Compilation tips and tricks

It's fine to use `&echo` while compiling during development because it let you quickly see what you're doing. 
The problem is: all your generated html will contains links like `pages/works.html` but you can't follow them because 
what you're after is something along the lines of `?action=compile&resource=pages/works.html&echo` (any reference to 
assets file will be broken too, as soon as you add folders to the mix).

To overcome this limitation, there's a smart `.htaccess` file inside the `public` folder that will let you browse 
the website with `&echo` always enabled and the definitive links. Just make sure you're browsing the website from the
public folder (`http://localhost/path/to/wadoo/public`) or it won't work.

As a last note, I prefer using absolute links over relatives, especially with complex url structure. 
Wadoo provides a `$root` parameter that is the base url upon which you can build all your links.
Given that transformation parameters can be defined in the url and they will take precedence over 
default parameters, when you're ready to compile for production, you can simply override the `$root` 
parameter like this: `?action=compile&root=http://www.production.mywebsite.com`.

This trick works for any parameter, GET will always take precedence.


### Note to Symphony CMS users
A few words to those enlightened people using Symphony CMS.
I like to think of Wadoo as the Symphony of static site generators. It provides very little by default 
but yet, it is so powerful and so enjoyable to work with that you'll fall in love with it very shortly.

Of course it can't (and doesn't aim to) be a replacement for a cms, but I found it especially useful in 
the early stages of a project when you just need a tool to put together a prototype or the final template 
that will be then integrated into the cms.

You can think of Wadoo sitemap as a list of Symphony pages where the uri is the page url and the 
template is, well, the page template. It may also help to think of `data.xml` as a single big static XML 
datasource that is attached to every page, and is the only datasource available. There's no concept of utility, 
but of course they all work out of the box being just xslt templates.

It shouldn't be too hard to build small/medium websites with Wadoo, just choose the right tool for the right job ;)

