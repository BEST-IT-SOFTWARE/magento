
include_recipe "vim"
include_recipe "git"

%w(nfs-common htop tig).each do |p|
    package p do
        action :install
    end
end

magic_shell_alias 'h' do
  command 'cd /vagrant'
end
