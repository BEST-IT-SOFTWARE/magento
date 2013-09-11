node.default["mysql"]["server_root_password"]= "root"
node.default["mysql"]["server_repl_password"]= "root"
node.default["mysql"]["server_debian_password"]= "root"


include_recipe 'mysql::client'
include_recipe 'mysql::server'
include_recipe 'database::mysql'
include_recipe 'innotop'


databases = %w(magento_dev)

mysql_connection_info = {:host => "localhost",
                             :username => 'root',
                             :password => node['mysql']['server_root_password']}


mysql_database_user 'magento' do
	connection mysql_connection_info
	password 'magento'
	action :create
end

databases.each do |db_name|
	mysql_database db_name do
		connection mysql_connection_info
		action :create
	end

	mysql_database_user 'magento' do
	      connection mysql_connection_info
	      password 'magento'
	      database_name db_name
	      host '%'
	      action :grant
	end

	mysql_database_provision db_name do
	      connection mysql_connection_info
	      data_file "/vagrant/db_dumps/"+db_name+".sql"      
        end 
end

mysql_database_user 'magento' do
	connection mysql_connection_info
        privileges ['process']
        database_name '*'
        table '*'
	password 'magento'
	action :grant
end

magic_shell_alias 'inno' do
  command "innotop -u root -p #{node['mysql']['server_root_password']}"
end

