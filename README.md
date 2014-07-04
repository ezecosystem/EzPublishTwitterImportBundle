# XrowTwitterImportBundle

Delivers an importer for twitter.


## Installation

1. To install XrowTwitterImportBundle wiht Composer just add the following to your `composer.json` file:

    ```
    // composer.json
    {
        //...
        "require":{
            // ...
            "xrow/ezpublish-twitterimport-bundle": "dev-master"
         }
    }
    ```
    
    Then, you can install the new dependencies by running Composer's `update` command from the directory where your `composer.json` file is located:
    
    ```
    $ php composer.phar update
    ```
    
    Now, Composer will automatically download all required files, and install them for you.
2. You can configure the baseurl, consumer_key, consumer_secret, token and token_secret in your twitter.conf.yml.
Examples:

    ```
    // ezpublish/config/twitter.conf.yml
    parameters:
      twitter.baseurl: https://api.twitter.com/1.1
 
      twitter.config:
        consumer_key: xxxxxxxxxxxxxx
        consumer_secret: xxxxxxxxxxxxxxxxxxxxxxx
        token:  xxxxxxxxxxxxxxxxxxxxxx
        token_secret: xxxxxxxxxxxxxxxxxxxxxxx
    ```

3. Import `twitter.conf.yml` in `ezpublish/config/ezpublish.yml` by adding:
    
    ```
    imports:
         - { resource: twitter.conf.yml }
    parameters:
        import.user.config:
            import_user: xxxx(username)<-- If not given or empty, then the username defaults to 'admin'
    ```

4. Register `EzPublishTwitterImportBundle` in `ezpublish/ezPublishKernel.php` by adding this line to `$bundles` in `registerBundles()` method:

    ```
     new xrow\EzPublishTwitterImportBundle\XrowTwitterImportBundle(),
    ```