# OWJA! Image Proxy Bundle

This Bundle is Open Source and under MIT license.

With this Bundle you can add some Image Resizing and
Optimization Functionality to your Symfony Project.
After Setup and Configuration you can access the Images
on the same, or one or more other Servers, trough
this Installation.

#### Accessing the images
http://.../`type`/`height`x`width`/`imagepath`

Var | Value | Required | Info
--- | --- | --- | ---
type | **resize** or **crop** | yes | Resize will first resize the image to best fit and then crop to destination size.
height | integer | no | Destination height of Image
width | integer | no | Destination width of Image
imagepath | string | yes | The public path to the Image

Example to resize and crop the Image to fit into a 100x100 Pixel square:
`http://example.com/resize/100x100/img/someimage.jpg`

Example cropping to fit into a 100x100 Pixel square:
`http://example.com/resize/100x100/img/someimage.jpg`

Example to resize to 100 Pixel with and preserve original image Ratio: 
`http://example.com/resize/x100/img/someimage.jpg`

Example to resize to 100 Pixel height and preserve original image Ratio:
`http://example.com/resize/100x/img/someimage.jpg`

Example to do ony the optimizations :
`http://example.com/resize/x/img/someimage.jpg`

## Installation

```
$ composer require owja/image-proxy-bundle
```

Load Bundles in **app/AppKernel.php**
```
new Oneup\FlysystemBundle\OneupFlysystemBundle(),
new Owja\ImageProxyBundle\OwjaImageProxyBundle(),
```

Setup your **app/config/config.yml**
```
oneup_flysystem:
    adapters:
        image_cache_adapter:
            local:
                directory: "%kernel.root_dir%/../var/images"

    filesystems:
        image_cache_filesystem:
            adapter: image_cache_adapter
            alias: owja_image_proxy.cache

owja_image_proxy:
    sites:
        default:
            url: "http://example.com/"
```
Set "http://example.com/" to the URL representing the source of your images.

Setup your **app/config/routing.yml**
```
owja_image_proxy:
    resource: "@OwjaImageProxyBundle/Controller/"
    type:     annotation
    prefix:   /
```
## Configuration Details

```
owja_image_proxy:
    remote:
        token: null
        timeout: 10
    limits:
        height: 1080
        width: 1920
    temp_dir: "%kernel.root_dir%/../var/temp/"
    cache_service: owja_image_proxy.cache
    optimization: true
    default_site: default
    enable_sites: false
```

CVar | Default | Info
 --- | --- | ---
remote : token | *null* | Send by Header 'owja-image-proxy'
remote : timeout | 10 | Request timeout to get the source image
limits : height | 1080 | Maximum allowed height of requested Image
limits : width | 1920 | Maximum allowed width of requested Image
temp_dir | "%kernel.root_dir%/../var/temp/" | Temporary directory for image processing
cache_service | owja_image_proxy.cache | The name of the cache filesystem (oneup_flysystem)
optimization | *true* | Enable/disable image optimization
default_site | default | Code of the default site. Has to be configured under *sites*
enable_sites | *false* | Set to *true* to enable more than the default site

## Multiple Sites

Simply add some sites and set enable_sites to true

```
owja_image_proxy:
    enable_sites: true
    sites:
        default:
            url: "http://example.com/"
        othersite:
            url: "http://othersite.com/"
        whereever:
            url: "http://wherever.com/"
```

##### Accessing the sites
http://.../`site`/`type`/`height`x`width`/`imagepath`

`http://example.com/default/resize/100x100/images/someimage.jpg`
`http://example.com/othersite/resize/100x100/images/someimage.jpg`
`http://example.com/wherever/resize/100x100/images/someimage.jpg`


## Image Optimization

To enable image optimization you should install some optimizers. 

```
# Ubuntu 16.04 LTS
apt install gifsicle jpegoptim pngquant optipng
```

If you have installed some optimizers but want to disable
optimization you can do this at **app/config/config.yml**
```
owja_image_proxy:
    optimization: false
```
## Reporting & Collaboration

Issues and feature requests are tracked in out Github Issue Tracker.
Pull Requests to enhance the code to add features or to fix bugs are very welcome. ;-) 

## License

This bundle is under the MIT license. 