# simple-thimble
A PHP library that converts all resources to data-uris if they're supported. Making the web 1,000s of %s faster, one resource at a time.

![Simple Thimble](https://github.com/aadel112/WP-Simple-Thimble/blob/master/assets/icon-128x128.png)

## Purpose
The basic purpose of this library is to provide methods to convert an enitre html content string into the same equivalent string, with all resources being converted to data-uris for browsers that support them. For browsers that don't, the html should fall back to normal. IE should only handle images. Every other browser should handle all resources, css, js, and images.

## Goal
The goal of inlining everything with data-uris is to limit requests per page load. Most web servers only open 2 connections simultaneously, which means that most content is render-blocking.

## Inspiration
I do part-time freelance work, and page load speed optimization is something I've taken up. It occured to me by running gtmetrix tests, along with others, that the vast majority of the bad marks would be taken care of with this one simple idea.

## Support
IE 7 and lower have no support, IE 8 will only work with images. All other known browsers should be fine.

## Contributing
Additional help with the code is always appreciated, otherwise, you can [contribute by donating](https://www.paypal.me/aadel112/5). Even $5 would be greatly appreciated. 
