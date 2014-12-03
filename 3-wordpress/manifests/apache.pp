class { 'apache': 
  default_mods => ['php'],
  mpm_module => 'prefork',
}

apache::vhost { 'example3.local':
  port    => '80',
  docroot => '/vagrant/webroot',
}