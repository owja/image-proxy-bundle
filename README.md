# OWJA! Image Proxy Bundle

[![Latest Stable Version](https://poser.pugx.org/owja/image-proxy-bundle/v/stable)](https://packagist.org/packages/owja/image-proxy-bundle?format=flat-square) [![Latest Unstable Version](https://poser.pugx.org/owja/image-proxy-bundle/v/unstable)](https://packagist.org/packages/owja/image-proxy-bundle?format=flat-square) [![License](https://poser.pugx.org/owja/image-proxy-bundle/license)](https://packagist.org/packages/owja/image-proxy-bundle?format=flat-square)

This Bundle is Open Source and under MIT license.

With this Bundle you can add some Image Resizing and
Optimization Functionality to your Symfony Project.
After Setup and Configuration you can access the Images
on the same, or one or more other Servers, trough
this Installation.

### Accessing the images

#### Preset Mode

`enable_presets` must be set to `true` to use this. Default is `true`.

http://.../:`preset`/`imagepath`

Var | Value | Required | Info
--- | --- | --- | ---
preset | string | yes | The preset code 
imagepath | string | yes | The public path to the Image


Example to get image "img/someimage.jpg" processed by preset "fullhd":
``` http://example.com/:fullhd/img/someimage.jpg ```

#### Dynamic Mode

`enable_dynamic` must be set to `true` to use this. Default is `false`.

**IMPORTANT! Dynamic mode should not used in production environment!** 

http://.../`type`/`height`x`width`/`imagepath`

Var | Value | Required | Info
--- | --- | --- | ---
type | **resize** or **crop** | yes | Resize will first resize the image to best fit and then crop to destination size.
height | integer | no | Destination height of Image
width | integer | no | Destination width of Image
imagepath | string | yes | The public path to the Image

#### Examples

Resize and crop the Image to fit into a 100x100 Pixel square

``` http://example.com/resize/100x100/img/someimage.jpg ```

Cropping to fit into a 100x100 Pixel square

``` http://example.com/resize/100x100/img/someimage.jpg ```

Resize to 100 Pixel with and preserve original image Ratio

``` http://example.com/resize/x100/img/someimage.jpg ```

Resize to 100 Pixel height and preserve original image Ratio

``` http://example.com/resize/100x/img/someimage.jpg ```

Optimizations only:

``` http://example.com/resize/x/img/someimage.jpg ```



## Installation

```
$ composer require owja/image-proxy-bundle
```

Load Bundles in **app/AppKernel.php**

```PHP
new Oneup\FlysystemBundle\OneupFlysystemBundle(),
new Owja\ImageProxyBundle\OwjaImageProxyBundle(),
```

Setup your **app/config/config.yml**

```YML
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

```YML
owja_image_proxy:
    resource: "@OwjaImageProxyBundle/Controller/"
    type:     annotation
    prefix:   /
```

Create the Directory for temporary Files which gets created while processing the Images:
```
var/temp
```

## Configuration Details

```YML
owja_image_proxy:
    remote:
        token: null
        timeout: 10
    limits:
        height: 1080
        width: 1920
    temp_dir: "%kernel.root_dir%/../var/temp/"
    cache_service: "owja_image_proxy.cache"
    optimization: true
    default_site: default
    enable_sites: false
    enable_dynamic: false
    enable_presets: true
```

CVar | Default | Info
 --- | --- | ---
remote : token | *null* | Send by Header 'owja-image-proxy'
remote : timeout | 10 | Request timeout to get the source image
limits : height | 1080 | Maximum allowed height of requested Image
limits : width | 1920 | Maximum allowed width of requested Image
temp_dir | "%kernel.root_dir%/../var/temp/" | Temporary directory for image processing
cache_service | "owja_image_proxy.cache" | The name of the cache filesystem (oneup_flysystem)
optimization | *true* | Enable/disable image optimization
default_site | "default" | Code of the default site. Has to be configured under *sites*
enable_sites | *false* | Set to *true* to enable more than the default site
enable_dynamic | *false* | Set to *true* to enable dynamic mode
enable_presets | *true* | Set to *true* to enable processing predefined presets


## Multiple Sites

Simply add some sites and set enable_sites to true

```YML
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

### Accessing the images by sites

Same as explained above ("Accessing the images"), but with site parameter

#### Dynamic Mode

http://.../`site`/`type`/`width`x`height`/`imagepath`

```
http://example.com/default/resize/100x100/images/someimage.jpg
http://example.com/othersite/resize/100x100/images/someimage.jpg
http://example.com/wherever/resize/100x100/images/someimage.jpg
```

#### Preset Mode

http://.../`site`:`preset`/`imagepath`

```
http://example.com/default:fullhd/images/someimage.jpg
http://example.com/othersite:fullhd/images/someimage.jpg
http://example.com/wherever:fullhd/images/someimage.jpg
```

## Presets Configuration

Global Presets:

```YML
owja_image_proxy:
    enable_presets: true
    presets:
        fullhd:
            width: 1920
            height: 1080
        banner:
            width: 1600
        profile:
            height: 50
            width: 50
        cuthd:
            width: 1280
            height: 720
            type: crop            
```

Per Site Presets:

```YML
owja_image_proxy:
    enable_presets: true
    sites:
        default:
            url: "http://example.com/"
            presets:
                fullhd:
                    width: 1920
                    height: 1080
        whereever:
            url: "http://whereever.com/"
            presets:
                banner:
                    width: 1600
                    height: 200
        
```

## Image Optimization

To enable image optimization you should install some optimizers. 

```
# Ubuntu 16.04 LTS
apt install gifsicle jpegoptim pngquant optipng
```

If you have installed some optimizers but want to disable
optimization you can do this at **app/config/config.yml**

```YML
owja_image_proxy:
    optimization: false
```

## Reporting & Collaboration

Issues and feature requests are tracked in this Github Issue Tracker.
Pull Requests to enhance the code to add features or to fix bugs are very welcome. ;-) 

## License

This bundle is under the MIT license. 
