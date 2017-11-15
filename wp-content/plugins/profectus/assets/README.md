# sass-master
Master SCSS file structure for WP web-dev projects

## Mixins

### Responsive

Use `@include breakpoint` inline, directly within an HTML selector.

```
@include breakpoint(000px){
	// Rule here.
}
```

### Resolution

Credit to @pierreburel.

You can set the resolution by using different notations and units : pixel-ratio (2, 2x or @2x), dppx (2dppx), dpi (192dpi), percentage (200%) or presets like retina (Apple) and xhdpi (Android).

**Example Usage:**

```
@include resolution(2dppx) {
    // Rule here
  }

  @include resolution(192dpi) {
    // Rule here
  }

  @include resolution(retina) {
  	// Rule here
  	}
```


## Grunt.js

Grunt processes currently include:
* Autoprefixing
* CSS minification

### Usage

Simply run `grunt` out of the theme directory via SSH.

*Only recommend running this close to launch or when exporting final code from staging*. Compile time is slow, so during development recommended usage is only for cross-browser checks or code testing.



## Changelog

### August 18, 2017

* Added search page styles: `pages/search.scss` and post template `pages/post.scss` pages.
* Added `.gitignore`
* **Removed responsive breakpoint files**. All `@media` queries should now be inserted inline using breakpoint includes/mixins. See above for usage.
* Added resolution mixins to target high-def screens. See above for usage.
* Added `lb_custom.js` and parent folder
* Added `Gruntfile.js` + corresponding `package.json`. See above for Grunt.js usage.
* General file / comment cleanup
