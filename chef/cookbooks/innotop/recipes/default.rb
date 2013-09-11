
bash "install innotop" do
        cwd '/home/vagrant'
	user "root"
	code <<-EOH 
		wget https://innotop.googlecode.com/files/innotop-1.9.0.tar.gz
                tar xvzf innotop-1.9.0.tar.gz
                cd innotop-1.9.0
                perl Makefile.PL
                make install
	EOH
        not_if { ::File.exists?('innotop-1.9.0') }
end

