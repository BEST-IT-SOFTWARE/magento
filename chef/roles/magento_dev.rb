name 'magento_dev'
description 'Development workstation for Magento'
run_list(
  'recipe[magento::dev]'
)
