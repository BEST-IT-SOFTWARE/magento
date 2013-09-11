include_recipe "apt"
include_recipe "magento::utilities"
include_recipe "magento::database"
include_recipe "magento::tasks"
include_recipe "magento::web"
include_recipe "magento::magento"
include_recipe "magento::dev_tools"

include_recipe "redisio::install"
include_recipe "redisio::enable"
include_recipe "memcached"


