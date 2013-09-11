#
# Cookbook Name:: phpunit
# Recipe:: pear
#
# Copyright 2012-2013, Escape Studios
#

include_recipe "php"

#PHP Extension and Application Repository PEAR channel
pearhub_chan = php_pear_channel "pear.php.net" do
  action :update
end

#upgrade PEAR
php_pear "PEAR" do
## gp- commented out, preventing vagrant up
##    channel pearhub_chan.channel_name
##    action :upgrade
end

#Symfony2 PEAR channel
php_pear_channel "pear.symfony.com" do
    action :discover
end

#PHPUnit PEAR channel
pearhub_chan = php_pear_channel "pear.phpunit.de" do
    action :discover
end

#upgrade PHPUnit

## gp- commented out, preventing vagrant up
##%w(PHPUnit DbUnit PHP_Invoker PHPUnit_Selenium PHPUnit_Story).each do |n|
#	php_pear n do
#		channel pearhub_chan.channel_name
#        if node[n][:version] != "latest"
#            version "#{node[n][:version]}"
#        end
#        action :upgrade if node[n][:version] == "latest"
#	end
#end
