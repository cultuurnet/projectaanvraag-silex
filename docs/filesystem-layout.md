# File system layout

## Directories

### app

The app is where the whole Silex application is initialized and configured.
All [service providers](http://silex.sensiolabs.org/doc/providers.html#service-providers) and [controller providers](http://silex.sensiolabs.org/doc/providers.html#controller-providers) should be registered here.

These are not unit tested and are very specific to how services and routing are defined in the Silex microframework.

### bin

The bin directory provides a console script. This allows you to run silex commands in your terminal.

### docs

All documentation should be stored in this folder.

### patches

Patches for composer dependencies are stored in this directory.

### src

The `src` directory contains the source code specific for the application. Both for the domain and infrastructure layer, among which PHP interfaces and classes. All code in here is covered by unit tests located in the `test` directory.

### vendor
The vendor directory contains the application's dependencies, which were installed with Composer.

You should never commit the contents of this directory to the source code repository Instead, you manage the exact versions of the dependencies with composer.json and composer.lock.

### test

The test directory contains unit tests, covering implementation code in the src directory.

### web
The `web` directory contains publicly accessible resources. It should be set as the document root when configuring a virtual host on your webserver.

Inside the `web` directory, you will find the front controller `index.php`. If the webserver supports it, you should put the proper rewrite rules in place to handle most of the requests with this file. A `.htaccess` file is present with the necessary rewrite rules for the Apache HTTP server.
