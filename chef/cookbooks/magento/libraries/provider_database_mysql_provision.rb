#
# Author:: Seth Chisamore (<schisamo@opscode.com>)
# Copyright:: Copyright (c) 2011 Opscode, Inc.
# License:: Apache License, Version 2.0
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

#require File.join(File.dirname(__FILE__), 'provider_database_mysql')

class Chef
  class Provider
    class Database
      class MysqlProvision < Chef::Provider::Database::Mysql
        include Chef::Mixin::ShellOut

        def load_current_resource
          Gem.clear_paths
          require 'mysql'
          @current_resource = Chef::Resource::DatabaseProvision.new(@new_resource.name)
          @current_resource.database_name(@new_resource.name)
          @current_resource
        end

        def action_provision
          unless database_exists?
            begin
              shell_out!("mysql -h #{@new_resource.connection[:host]} "+
			"-P #{@new_resource.connection[:port] || 3306} "+
			"-u #{@new_resource.connection[:username]} "+
			"--pass=#{@new_resource.connection[:password]} "+
			"#{@new_resource.database_name} < #{@new_resource.data_file}")
              @new_resource.updated_by_last_action(true)
            ensure
              close
            end
          end
        end

        private
        def database_exists?
          db.select_db(@new_resource.database_name)
          db.query("show tables").num_rows != 0
        end

      end
    end
  end
end
