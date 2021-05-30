# JIT Image Manipulation

A simple way to manipulate images "just in time" via the URL. Supports caching, image quality settings and loading of offsite images.

## Installation

Information about [installing and updating extensions](http://getsymphony.com/learn/tasks/view/install-an-extension/) can be found in the [Symphony documentation](http://getsymphony.com/learn/).

## Updating

### 1.17

This release raises the minimum requirement to Apache 2.2+.

### 1.15

Since version `1.15`, JIT configuration has moved from `/manifest/` to the `/workspace/` folder. This change will automatically happen when you update the extension from the "System > Extensions" page.

Due to some `.htaccess` changes in Symphony 2.0.6+, it is recommended that you edit your core Symphony `.htaccess` to remove anything before 'extensions/' in the JIT rewrite. It should look like the following regardless of where you installed Symphony:

	### IMAGE RULES
	RewriteRule ^image\/(.+\.(jpg|gif|jpeg|png|bmp))$ extensions/jit_image_manipulation/lib/image.php?param=$1 [L,NC]

It is not absolutely necessary to do this, but may prevent problems with future releases.

## Usage

### Basics

The image manipulation is controlled via the URL, eg.:

	<img src="{$root}/image/2/80/80/5/fff{image/@path}/{image/filename}" />

The extension accepts four numeric settings and one text setting for the manipulation.

1. mode
2. width
3. height
4. reference position (for cropping only)
5. background color (for cropping only)

There are four possible modes:

- `0` none
- `1` resize
- `2` resize and crop (used in the example)
- `3` crop
- `4` resize to fit

If you're using mode `2` or `3` for image cropping you need to specify the reference position:

	+---+---+---+
	| 1 | 2 | 3 |
	+---+---+---+
	| 4 | 5 | 6 |
	+---+---+---+
	| 7 | 8 | 9 |
	+---+---+---+

If you're using mode `2` or `3` for image cropping, there is an optional fifth parameter for background color. This can accept shorthand or full hex colors.

- *For `.jpg` images, it is advised to use this if the crop size is larger than the original, otherwise the extra canvas will be black.*
- *For transparent `.png` or `.gif` images, supplying the background color will fill the image. This is why the setting is optional*

The extra fifth parameter makes the URL look like this:

	<img src="{$root}/image/2/80/80/5/fff{image/@path}/{image/filename}" />

- *If you wish to crop and maintain the aspect ratio of an image but only have one fixed dimension (that is, width or height), simply set the other dimension to 0*

### External sources & Trusted Sites

In order pull images from external sources, you must set up a white-list of trusted sites. To do this, go to "System > Preferences" and add rules to the "JIT Image Manipulation" rules textarea. To match anything use a single asterisk (`*`).

The URL then requires a sixth parameter, external, (where the fourth and fifth parameter may be optional), which is simply `1` or `0`. By default, this parameter is `0`, which means the image is located on the same domain as JIT. Setting it to `1` will allow JIT to process external images provided they are on the Trusted Sites list.

	<img src="{$root}/image/1/80/80/1/{full/path/to/image}" />
	                                ^ External parameter

### Recipes

Recipes are named rules for the JIT settings which help improve security and are more convenient. They can be edited on the preferences page in the JIT section and are saved in  `/workspace/jit-image-manipulation/recipes.php`. A recipe URL might look like:

	<img src="{$root}/image/thumbnail{image/@path}/{image/filename}" />

When JIT parses a URL like this, it will check the recipes file for a recipe with a handle of `thumbnail` and apply it's rules. You can completely disable dynamic JIT rules and choose to use recipes only which will prevent a malicious user from hammering your server with large or multiple JIT requests.

Recipes can be copied between installations and changes will be reflected by every image using this recipe.

## WebP please

JIT offers to generate WebP images on the fly from common image file formats like jpeg and png. [WebP images](https://developers.google.com/speed/webp/) are designed for the web, offering better quality and significantly smaller file sizes, making websites with images loading faster and more SEO friendly.

### Usage

Just add `.webp` at the end of JIT URL requests described above, e.g.:

	".../{image/filename}.webp"

so the final URL will look something like:

	".../image.jpg.webp"

### More info

* As of now, JIT WebP generation is not supported for external images.
* It is recommended to have JIT images caching enabled, which is `on` by default (see `'image' => 'cache'` in `/manifest/config.php`; `1` - enabled, `0` - disabled). Generated WebP images are then stored along with other JIT images in the `/manifest/cache` folder.
* See [WebP browser support](https://caniuse.com/?search=webp).
