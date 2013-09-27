#!/usr/bin/env bash

## correcting htdocs path in /etc/nginx/sites-enabled/sportpursuit
sed -i 's,root /vagrant/htdocs;,root /vagrant;,g' /etc/nginx/sites-enabled/sportpursuit

## echo "Installing gd-library"

sudo apt-get -y -qq install php5-gd
sudo /etc/init.d/nginx restart
sudo /etc/init.d/php5-fpm restart
sudo /etc/init.d/mysql restart

echo "Creating Database user"
mysql -uroot -proot < /vagrant/vagrant/scripts/create_magento_user.sql

echo "Importing DB"
mysql -uroot -proot sportpursuit_dev < /vagrant/db_dumps/new_default_allinone_database.sql

##echo "Importing DB"
##mysql -uroot -proot sportpursuit_dev < /vagrant/db_dumps/sportpursuit_dev.sql

##echo "Importing reporting DB"
##mysql -uroot -proot sportpursuit_reports < /vagrant/db_dumps/sportpursuit_reports.sql

##echo "Importing blog DB"
##mysql -uroot -proot sportpursuit_blog < /vagrant/db_dumps/sportpursuit_blog.sql

##echo "Changing admin password to admin/admin"
##mysql -uroot -proot sportpursuit_dev < /vagrant/vagrant/scripts/change_admin_password.sql