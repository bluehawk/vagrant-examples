exec { 'apt-get update':
  command => '/usr/bin/apt-get update'
}

package { 'apache2':
  ensure => installed,
  require => Exec['apt-get update']
}

service { 'apache2':
  ensure => running,
  require => Package['apache2'],
  enable => true,     # Start on boot
}

# Remove the default apache site conf
file { '/etc/apache2/sites-enabled/000-default.conf':
  ensure => absent,
  require => Package['apache2'],
}

# Add in our example conf
file { '/etc/apache2/sites-available/example.conf':
  require => Package['apache2'],
  notify => Service['apache2'],
  content => "<VirtualHost *:80>
    DocumentRoot /vagrant/webroot
    <Directory /vagrant/webroot/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
</VirtualHost>"
}

# Enable our site
file { '/etc/apache2/sites-enabled/example.conf':
  ensure => 'link',
  target => '/etc/apache2/sites-available/example.conf',
  require => Package['apache2'],
  notify => Service['apache2'],
}