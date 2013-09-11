node.default['supervisor']['inet_port']='127.0.0.1:9001'
#node.default['supervisor']['inet_username']=' '
#node.default['supervisor']['inet_password']=' '
include_recipe "supervisor"

service "supervisor" do
	action [:stop]
end


node.default[:beanstalkd][:start_during_boot]=true
include_recipe "beanstalkd"

template "/etc/supervisor.d/magento.conf" do
  source "magento.supervisor.conf.erb"
  mode 0660
  owner "root"
  group "root"
end

service "supervisor" do
	action [:start]
end


python_pip "beantop" do
  action :upgrade
end

magic_shell_alias 'bean' do
  command 'beantop'
end
