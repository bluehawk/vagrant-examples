class { '::mysql::server':
  databases => {
    'wordpress' => {
      ensure => 'present',
    }
  },
  users => {
    'wordpress@localhost' => {
      ensure        => 'present',
      password_hash => '*987E04344A060DF398BF24173837C6FC350EA9E8', # w0rdpr3ss
    }
  },
  grants => {
    'wordpress@localhost/wordpress.*' => {
      ensure     => 'present',
      options    => ['GRANT'],
      privileges => ['ALL'],
      table      => 'wordpress.*',
      user       => 'wordpress@localhost',
    }
  }
}