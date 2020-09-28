# Mellon - Backend

For project overview, please refer to the front end [repository](https://github.com/PengHuang0508/streaming-app-frontend)

### Installation

Mellon's backend requires [Nginx](https://www.nginx.com/), [PHP](https://www.php.net/) and [MySQL](https://www.mysql.com/) to run.
Download and install them on your machine. Please refer to the following guides to help you set up the backend environment.

- [How To Install Linux, Nginx, MySQL, PHP (LEMP stack) in Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04)
- [How To Install Linux, Nginx, MySQL, PHP (LEMP stack) on Ubuntu 18.04](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-ubuntu-18-04)
- [How to Install Nginx, PHP, and MySQL on Windows 10](https://codefaq.org/server/how-to-install-nginx-php-mysql-on-windows-10/)
-

##### Settings

Once you have Nginx, PHP, MySQL all installed and functional, we need to change the settings a bit. Go to _php/php.ini_ and change the maximum upload file size allowed. You can change to whatever amount you feel reasonable.

```
upload_max_filesize = 25M
post_max_size = 25M
```

Setup your _nginx/conf/nginx.conf_ to like this

```
server {
        listen 80 default_server;
        listen [::]:80 default_server ipv6only=on;
        client_max_body_size 25M;


        location / {
            root PATH_TO_FRONTEND_BUILD_FOLDER
            index index.html index.htm;

            // Choose the port your frontend will be hosting on
            proxy_pass https://localhost:3000;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            proxy_cache_bypass $http_upgrade;

            add_header Access-Control-Allow-Origin *;
        }
        server_name localhost;

        location /api {
            root PATH_TO_BACKEND_FOLDER
            index index.php index.html index.htm;
            try_files $uri  /index.php$is_args$args;

            add_header Access-Control-Allow-Origin *;
        }

        error_page 404 /404.html;
        error_page 500 502 503 504 /50x.html;
        location = /50x.html {
            root /usr/share/nginx/html;
        }

        location ~ \.php$ {
            try_files $uri =404;

            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass    127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
```

Next, we will also need [Composer](https://getcomposer.org/) to install PHP dependencies. Go to the repository and install the dependencies with Composer.

```sh
$ cd streaming-app-backend
$ composer install
```

##### AWS

Please refer to this official AWS [documentation](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_environment.html) to set up environment variables for accessing AWS services. The last thing that we need to do is to go to _/src/config.sample.php_. Follow the instructions to fill in MySQL Database information and AWS credentials. Once it's done, rename it to **config.php**

Everything should be ready to go.

### Extra

If you are using Windows, there are scripts to start/close both Nginx & PHP services at the same time.

First, download the **RunHiddenConsole** from [Nginx official resources](https://www.nginx.com/resources/wiki/start/topics/examples/phpfastcgionwindows/). Then, create two text files and rename them to .bat.

_start.bat_

```
    @ECHO OFF
    ECHO starting Nginx...
    CD C:\PATH\TO\Nginx
    C:\PATH\TO\RunHiddenConsole.exe nginx.exe -c C:\PATH\TO\Nginx\conf\nginx.conf
    ECHO Starting PHP FastCGI...
    set PATH=C:\PATH\TO\YOUR\PHP;%PATH%
    C:\PATH\TO\RunHiddenConsole.exe C:\PATH\TO\PHP\php-cgi.exe -b 127.0.0.1:9000 -c C:\PATH\TO\PHP\php.ini
    timeout /t 5 /nobreak > NUL
```

_stop.bat_

```
@ECHO OFF
taskkill /f /IM nginx.exe
taskkill /f /IM php-cgi.exe
timeout /t 5 /nobreak > NUL
exit
```

# Database structure

For the demonstration purpose of this project, the database is kept minimal with only two tables.

![Database structure](http://u.cubeupload.com/phuang/databasestructure.png 'Database structure')

# Workflow

Simplified backend workflow:

![Workflow chart](http://u.cubeupload.com/phuang/backendworkflow.png 'Backend workflow chart')
