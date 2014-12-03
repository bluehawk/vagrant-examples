package { 'tomcat6':
  ensure => 'installed',
}

service { 'tomcat6':
  ensure => running,
  enable => true,
}

exec { 'download sample app':
  require => Package['tomcat6'],
  creates => '/var/lib/tomcat6/webapps/sample.war',
  command => 'wget -O /var/lib/tomcat6/webapps/sample.war http://tomcat.apache.org/tomcat-6.0-doc/appdev/sample/sample.war',
  path => ['/bin', '/usr/bin', '/usr/local/bin'],
}