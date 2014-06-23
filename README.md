# XrowTwitterImportBundle

Delivers an importer for twitter.


## Install

1. In `src/` folder, create a `xrow/` folder.

2. Go to `src/xrow/` and clone this repository in there. In the end you should have `src/xrow/EzPublishTwitterImportBundle/`.

3. You can configure the baseurl, consumer_key, consumer_secret, token and token_secret in your twitter.conf.yml.
Examples:

```
// ezpublish/config/twitter.conf.yml
parameters:
  twitter.baseurl: https://api.twitter.com/1.1
 
  twitter.config:
    consumer_key: xxxxxxxxxxxxxx
    consumer_secret: xxxxxxxxxxxxxxxxxxxxxxx
    token:  xxxxxxxxxxxxxxxxxxxxxx
    token_secret: xxxxxxxxxxxxxxxxxxxxxxxx
```

4 Import `twitter.conf.yml` in `ezpublish/config/config.yml` by adding:
    
```
    imports:
         - { resource: twitter.conf.yml }
        
```

5. Register `EzPublishTwitterImportBundle` in `ezpublish/ezPublishKernel.php` by adding this line to `$bundles` in `registerBundles()` method:

```
     new xrow\EzPublishTwitterImportBundle\XrowTwitterImportBundle(),
```