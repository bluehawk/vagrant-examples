Warning, this is still a very rough draft, and is not completed yet. 


# Vagrant Examples

This is an introduction to Vagrant.  

## Introduction

Vagrant is a tool for managing development environments. To get started, let's make a very simple Vagrant setup. 

This guide assumes you already have Vagrant and VirtualBox installed.

Go into the `0-intro` folder and let's look at the `Vagrantfile`:

```
Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.post_up_message = "Machine ready, use `vagrant ssh` to connect to it."
end
```

Now, open a terminal to that folder and run `vagrant up`. Vagrant will find the Vagrantfile, and identify that you want a vm that is based on the `ubuntu/trusty64` box. If you have never used/downloaded that box, Vagrant will download it (boxes are typically stored in `~/.vagrant/boxes`). 

Once Vagrant has verified you have the needed box, it imports that box (the box is basically a template) as a new VM using the provider specified. (If no provider is specified, it defaults to VirtualBox). 

Vagrant also sets up various network things, port forwards and shared folders by default, which you can see the in output. (These are all configurable, which we'll see in a bit)

It then outputs the message that we set as the `post_up_message` which is optional, but a useful way to give users of the VM instructions on how to use it. 

### Basic commands

Let's talk about some of the basic commands to control the VM at this point:

**`vagrant help`** - If you only remember one, remember this one. It lists the most used commands. 

**`vagrant ssh`** - This connects you to the VM over ssh

**`vagrant halt`** - Shutsdown the VM

**`vagrant up`** - Turns on the VM (or creates it if it doesn't exist yet)

**`vagrant status`** - See what the VM is up to

**`vagrant destroy`** - Completely delete the VM and all data on it

**`vagrant provision`** - Rerun the provisioning

### What is Vagrant?

If you open up VirtualBox, you will see a VM running named after the current folder you are in with `default` and a bunch of numbers after it. This illustrates that Vagrant is not itself a VM provider, it's a tool that allows you to define *how* a VM is created (like what VM software or cloud provider to use), configured (network, shared folders, etc) and what software is installed (provisioning, which we will cover shortly). Because these things are defined in code which you can version control, it makes it possible to use Infrastructure as Code. 

## Basic Apache Example

Okay, now that we know how to create a Vagrantfile and then control the VM, let's actually put something on the VM. Let's make a simple apache server with a static page. 

There are lots of provisioners that work with Vagrant. We will be using Puppet simply because it's what I'm familiar with.

Luckily for us, the ubuntu/trusty64 box we are using already has Puppet installed on it, so we can start using it right away.

Take a look at `1-apache-simple/Vagrantfile1`. There are two differences from our last example:

```
  config.vm.network "private_network", ip: "192.168.33.101"
```

This creates a second network interface (the first network interface in a Vagrant box is always a NAT interface) and gives the VM a specific IP address so we can access the apache we are about to install. 

Next up, is the provisioning section:

```
  config.vm.provision "puppet" do |puppet|
    puppet.manifests_path = "manifests"
    puppet.manifest_file  = "default.pp"
  end
```

This instructs Vagrant to run Puppet, and tells it where the manifest to run is located. Let's take a look at the `manifests/default.pp`:

```
exec { 'apt-get update':
  command => '/usr/bin/apt-get update'
}
```

If you aren't familiar with Puppet, you define things by declaring resources. This first resource says that we need to run a command, in this case `apt-get update`. 


```
package { 'apache2':
  ensure => installed,
  require => Exec['apt-get update']
}

service { 'apache2':
  ensure => running,
  require => Package['apache2'],
  enable => true,     # Start on boot
}
```

These next sections install the `apache2` package, and ensure that the service is running. Note the `require =>` blocks. This tells Puppet that these resources depend on another resource being run. By default, the order that puppet resources is run is effectively random, so you need to explicitly declare dependencies or you will get errors when puppet tries to start apache but it's not installed yet. 

```
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
```
These resources put the apache configuration files that we need in place. The first one removes the default config, the second adds a very basic config for our site, and the third one creates the symlink to enable that site. (It's worth mentioning that this is a pretty ugly way to enable the site in apache. Later on we will use puppet modules to make this prettier, so bear with me. It's a good example of declaring file resources.)

So with all of that in place, let's run `vagrant up` and then go to `192.168.33.101` like it says in the post-up message. You should see a wonderful "Hello world" page. 

By default, Vagrant shares the folder that has the `Vagrantfile` in it as `/vagrant` on the VM. Since we pointed our DocumentRoot to `/vagrant/webroot` Apache is actually serving the file from the host machine through the VirtualBox shared folder that Vagrant set up for us. 

This means you can modify `webroot/index.html` and when you reload the page, apache will pull the latest content. 

## 2 Apache Improvements

Putting the content of `example.conf` inline in the puppet manifest was ugly. One option is to use puppet modules, which is great for creating small reusable bits of puppet manifests. In fact, there are hundreds of puppet modules available. However, if you just want a way to share some files from your host in the VM, you can use a simple puppet fileserver. I learned about this from [The Holy Java](http://theholyjava.wordpress.com/2012/06/14/serving-files-with-puppet-standalone-in-vagrant-from-the-puppet-uris/).

Create `fileserver.conf` and put it in the root of your project with the following content:
