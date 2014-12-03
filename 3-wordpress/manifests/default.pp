exec { 'apt-get update':
  command => '/usr/bin/apt-get update'
}

package { ['libapache2-mod-php5', 'php5-mysql']:
  ensure => installed,
  require => Exec['apt-get update']
}

import 'apache.pp'
import 'mysql.pp'

# Get the wordpress source code
exec { 'fetch wordpress':
  creates => '/vagrant/webroot/index.php',
  command => 'mkdir -p /vagrant/webroot && wget -qO- https://wordpress.org/latest.tar.gz | tar xvz -C /vagrant/webroot --strip 1',
  path => ['/bin', '/usr/bin', '/usr/local/bin'],
}

# Put our wordpress config file in place
file { '/vagrant/webroot/wp-config.php':
  source => 'puppet:///files/wp-config.php',
  require => Exec['fetch wordpress'],
}