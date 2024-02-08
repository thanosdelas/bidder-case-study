# App Deployment

Bidder Case Study app was developed and tested on Linux Debian 10 "buster" with php7.3 and apache2.4 web server.

# Code structure

- App root folder contains 'config.php' file with application configuration options.
- Application entry point is in 'pulbic/index.php' which initiates slim framework.
- All source code is 'src' folder.

### Install on a debian based distribution

```
apt update
apt install apache2 curl git php unzip nano tmux php-cli libapache2-mod-php php-xml php-mbstring php-curl
```
If there are any missing extensions find them and install them.
```
apt search php- | grep 'mbstring'
```

### Install composer
```
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### Upload bidder app zip to server or a to docker container

```
docker cp pa-bidder-case-study-app.zip <container_id>:/var/www/html
unzip pa-bidder-case-study-app.zip -d /var/www/html/bidder
```

### Install composer dependencies

Navigate to app root folder

```
composer install
```

# Web server

To run a php web server navigate to public folder and issue:
```
php -S localhost:8888
```
Or configure apache2 with app public folder.


## To run unit tests
```
./vendor/bin/phpunit unit-tests
./vendor/bin/phpunit --testdox unit-tests
```

## To run api end-to-end test cases

Set app api url according to server running:

```
./codeception.yml
```

If web sever running is php -S localhost:8888

```
...
  - REST:
    url: http://localhost:8888
    depends: PhpBrowser
```

or if apache2.4 is running

```
...
  - REST:
    url: http://localhost/bidder/public
    depends: PhpBrowser
```

and then run in bidder app root folder

```
php vendor/bin/codecept run
```
