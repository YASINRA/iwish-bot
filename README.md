# iWishco Bot

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
  </ol>
</details>

<!-- GETTING STARTED -->
## Getting Started

This is an example of how you may give instructions on setting up your project locally.
To get a local copy up and running follow these simple example steps.

### Prerequisites

This is an example of how to list things you need to use the software and how to install them.

* [LEMP stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-20-04)
* [nginx](https://www.digitalocean.com/community/tutorials/how-to-install-nginx-on-ubuntu-20-04)
* [php](https://www.digitalocean.com/community/tutorials/how-to-install-php-8-1-and-set-up-a-local-development-environment-on-ubuntu-22-04)
* [mysql](https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-22-04)
* [phpmyadmin](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-on-ubuntu-20-04)

### Installation

_Below is an example of how you can instruct your audience on installing and setting up your app. This template doesn't rely on any external dependencies or services._

1. Create or edit the config file of nginx
   ```sh
   dirname="botName"
   nano /etc/nginx/conf.d/ip.conf
   sudo systemctl restart nginx
   ```
   2. Clone the repo
   ```sh
   git clone https://github.com/YASINRA/iwish-bot.git /var/www/$dirname
   ```
3. Install NPM packages
   ```sh
   npm install
   ```
4. Set the environment variables. Enter your API in `config.js`
   ```sh
   cd /var/www/$dirname
   cp config.env.example config.env
   nano config.env
   ```
   then please set the variables.
   ```js
   const API_KEY = 'ENTER YOUR API';
   ```
5. Build the project
   ```sh
   botName="YourBotName"
   git clone https://github.com/YASINRA/iwish-bot.git /var/www/ip/public_html/$botName
   cd /var/www/ip/public_html/$botName
   composer update
   cp config.example.php config.php
   nano config.php
   crontab -e   --> * * * * * php /var/www/ip/public_html/iwish-bot/getUpdatesCLI.php
   ```

<p align="right">(<a href="#readme-top">back to top</a>)</p>
