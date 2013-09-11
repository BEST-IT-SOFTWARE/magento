include_recipe "nginx"

node.default['php-fpm']['pools'] = ["www"]
node.default['php']['ini_settings']['memory_limit'] = '512M'

include_recipe "php"
include_recipe "php-fpm"


%w(mcrypt mysql curl memcache xmlrpc).each do |ext| 
	package "php5-"+ext do
		action :install
	end
end	

%w(apc).each do |ext| 
	package "php-"+ext do
		action :install
	end
end	

template "/etc/nginx/sites-available/magento" do
  source "magento.nginx.conf.erb"
  mode 0660
  owner "root"
  group "root"
  variables({
       :web_root => "/vagrant",
       :web_address => "magento.dev"
    })

end

link "/etc/nginx/sites-enabled/magento" do
   to "/etc/nginx/sites-available/magento"
end

service "nginx" do
	action [:restart]
end
