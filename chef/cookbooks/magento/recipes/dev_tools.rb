%w(xdebug).each do |ext| 
	package "php5-"+ext do
		action :install
	end
end

template "/etc/php5/conf.d/xdebug_remote.ini" do
  source "xdebug_remote.ini.erb"
  mode 0660
  owner "root"
  group "root"
end


node.default[:phpmyadmin][:blowfish_secret]='6TQ8QnKl8f1}+ZQCW#$0Q_cHaYkIDN[]2K[$)g~g'
include_recipe "phpmyadmin"


template "/etc/nginx/sites-available/phpmyadmin" do
  source "phpmyadmin.nginx.conf.erb"
  mode 0660
  owner "root"
  group "root"
end

link "/etc/nginx/sites-enabled/phpmyadmin" do
   to "/etc/nginx/sites-available/phpmyadmin"
end