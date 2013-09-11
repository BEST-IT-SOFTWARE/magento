mysql -u root --pass=root -e "drop database magento_dev"
mysql -u root --pass=root -e "create database magento_dev"
mysql -u root --pass=root magento_dev < magento_dev.sql
