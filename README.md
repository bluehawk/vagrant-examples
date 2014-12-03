Warning, this is still a very rough draft, and is not completed yet. 


# Vagrant Examples

This is an introduction to Vagrant.  

## What is Vagrant?

Vagrant is a tool for managing development environments. To get started, let's make a very simple Vagrant setup. 

## Introduction

Spoiler alert, you need to have Vagrant and VirtualBox installed. So if you don't you should go do that. 

Now, go into the `0-intro` folder (you have cloned the repo, right?) and let's look at the `Vagrantfile`:

```
Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.post_up_message = "Machine ready, use `vagrant ssh` to connect to it."
end
```

The `Vagrant.configure` bit has to surround everything in the Vagrantfile. The 2 let's you know which [configuration version](https://docs.vagrantup.com/v2/vagrantfile/version.html) this Vagrantfile uses. The `config.vm.box` section is required in every Vagrantfile. It tells Vagrant which box (or template) to use for this VM. 

Open a terminal to that folder and run `vagrant up`. Vagrant will identify that you want a vm that is based on the `ubuntu/trusty64` box. If you have never used/downloaded that box, Vagrant will download it (boxes are typically stored in `~/.vagrant/boxes`). 

Once Vagrant has verified you have the needed box, it imports that box as a new VM using the provider specified. (If no provider is specified, it defaults to VirtualBox). 

Vagrant also sets up various network things, port forwards and shared folders by default, which you can see the in output. (These are all configurable, which we'll see in a bit)

It then outputs the message that we set as the `post_up_message` in the Vagrantfile. This is optional but is a useful way to give users of the VM instructions on how to use it. 

### Basic commands

So we now have a running VM. Let's talk about some of the basic commands to control the VM at this point:

* **`vagrant help`** - If you only remember one, remember this one. It lists the most used commands. 
* **`vagrant ssh`** - This connects you to the VM over ssh
* **`vagrant halt`** - Shutsdown the VM
* **`vagrant up`** - Turns on the VM (or creates it if it doesn't exist yet)
* **`vagrant status`** - See what the VM is up to
* **`vagrant destroy`** - Completely delete the VM and all data on it
* **`vagrant provision`** - Rerun the provisioning
* **`vagrant reload`** - Reloads the vm. Useful if you change the Vagrantfile. Essentially a `vagrant halt` followed by `vagrant up`.

### Vagrantfile settings

Let's go back to the Vagrantfile. Our example is pretty basic, and really only sets the box. But we can set all kinds of things in the Vagrantfile. Go to an empty folder and run `vagrant init`. This will create a Vagrantfile with lots of commented out samples that you can look at. Also be sure to look at the [complete docs](https://docs.vagrantup.com/v2/vagrantfile/index.html) online. Here are some examples of things you could set:

```
  # Forward port 8080 on the host to port 80 on the guest
  config.vm.network "forwarded_port", guest: 80, host: 8080
  
  # Configure a network between the host and guest and give the guest a specific ip
  config.vm.network "private_network", ip: "192.168.50.4"

  # Sync a folder between the host and guest
  # Note that this synced folder is actually done by default, you don't have to add it
  config.vm.synced_folder "./", "/vagrant"
```

### What is Vagrant?

If you open up VirtualBox, you will see a VM running named after the current folder you are in with `default` and a bunch of numbers after it. This illustrates that Vagrant is not itself a VM provider, it's a tool that allows you to define *how* a VM is created (like what VM software or cloud provider to use), configured (network, shared folders, etc) and what software is installed (provisioning, which we will cover shortly). These definitions are in source code, a concept known as Infrastructure as Code. 

## Basic Apache Example

Okay, now that we know how to create a Vagrantfile and control the created VM, let's actually put something on the VM. Let's make a simple apache server with a static "Hello World" page. 

There are lots of provisioners that work with Vagrant. We will be using Puppet simply because it's what I'm familiar with.

Luckily for us, the `ubuntu/trusty64` box we are using already has Puppet installed on it, so we can start using it right away.

Take a look at `1-apache-simple/Vagrantfile`. There are two differences from our last example:

```
  config.vm.network "private_network", ip: "192.168.33.101"
```

This creates a network between the host and VM and gives the VM a specific IP address so we can access the apache server we are about to install. 

Next up, is the provisioning section:

```
  config.vm.provision "puppet" do |puppet|
    puppet.manifests_path = "manifests"
    puppet.manifest_file  = "default.pp"
  end
```

This instructs Vagrant to run Puppet, and tells it where the manifest to run is located. Let's take a look at `manifests/default.pp`:

```
exec { 'apt-get update':
  command => '/usr/bin/apt-get update'
}
```

If you aren't familiar with Puppet, you define things by declaring resources. This first resource says that we need to execute a command, in this case `apt-get update`. 


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

These next resources install the `apache2` package (Puppet determines we are on Ubuntu, so it uses `apt-get` to install it), and ensure that the service is running. Note the `require =>` blocks. This tells Puppet that these resources depend on another resource being complete first. By default, the order that puppet resources is run is effectively random, so you need to explicitly declare dependencies or you will get errors when puppet tries to start apache before it's installed. 

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
These resources put the apache configuration files that we need in place. The first one removes the default config, the second adds a very basic config for our site, and the third one creates the symlink to enable that site. (It's worth mentioning that this is a pretty awkward way to enable the site in apache. Later on we will use puppet modules to make this prettier, but for now bear with me, it's a decent example of declaring file resources.)

So with all of that in place, let's run `vagrant up` and then go to `192.168.33.101` in our browser like it says in the post-up message. You should see a wonderful "Hello world" page. 

By default, Vagrant shares the folder that has the `Vagrantfile` in it as `/vagrant` on the VM. Since we pointed our DocumentRoot to `/vagrant/webroot` Apache is actually serving files from the host machine through the VirtualBox shared folder that Vagrant set up for us. 

Go ahead and modify `webroot/index.html` and reload the page in your browser and you will see your changes. So our Vagrant VM is now a controlled apache environment, but serves files right from our host machine. Pretty snazzy!

## 2 Apache Improvements

Let's make some improvements, and put something useful on the VM. In this example, we will be downloading 2048 and putting it on the VM. We will also us a plugin called `vagrant-hostmanager` to do some `hosts` file magic. 

In the last example, we referred to the web server by IP address in the browser. This is a bit clunky. We could manually edit our `/etc/hosts` file to point some domain at that ip, which lets us use the domain instead. That's great, but manual processes are no fun. We will use a plugin called `vagrant-hostmanager` to manage the hosts file for us. 

In `2-apache-better/Vagrantfile` we've added the following section:

```
  # Manage host
  if Vagrant.has_plugin?('vagrant-hostmanager')
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
  else
    puts '[WARNING] Host manager plugin is not installed! Please run `vagrant plugin install vagrant-hostmanager`'
  end
```

This configures the hostmanager plugin, but only after checking if we have it (so Vagrant doesn't throw errors). It tells the user how to install it if it's missing. If you haven't yet, run `vagrant plugin install vagrant-hostmanager`. 

Putting the content of `example.conf` inline in the puppet manifest was ugly. So instead, let's put that content somewhere else, and include it. The method we will use in this example is to use a simple puppet fileserver. I learned about this from [The Holy Java](http://theholyjava.wordpress.com/2012/06/14/serving-files-with-puppet-standalone-in-vagrant-from-the-puppet-uris/).

We create a `fileserver.conf` which defines the Puppet fileserver, which we pass into Puppet in the Vagrant file. We also add a shared folder to put `puppet-files` from the host onto the VM as `/etc/puppet/files`. Now we can include the file in our manifest like so: 

```
# Add in our example conf
file { '/etc/apache2/sites-available/example.conf':
  require => Package['apache2'],
  notify => Service['apache2'],
  source => 'puppet:///files/example.conf',
}
```

Now that we have that taken care of, let's grab 2048 and install it on our VM!

```
exec { 'fetch 2048':
  creates => '/vagrant/webroot/index.html',
  command => 'mkdir -p /vagrant/webroot && wget -qO- https://github.com/gabrielecirulli/2048/archive/master.tar.gz | tar xvz -C /vagrant/webroot --strip 1',
  path => ['/bin', '/usr/bin', '/usr/local/bin'],
}
```

This tells puppet to execute a command that fetches and extracts a tar from github and puts it in our webroot (remember that `/vagrant` is shared, so that means this is actually putting these files on the host. This is useful, in that now it's easier to see and modify them from the host, but there is a speed penalty for shared folders). The `creates` tells puppet that running this command will create a specific file and not to run the command if that file already exists. This goes along with Puppet's [idempotency](https://docs.puppetlabs.com/guides/introduction.html#idempotency). You define the state you want the system to be in ("X package should be installed, Y file should be here") rather than describing all the commands to run to get there. 

After running `vagrant up` from `2-apache-better` you can browse to `example2.local` in your browser and you can play 2048. The files exist on your host machine, so you are free to modify them there. 

## Wordpress

In this example, we will introduce Puppet Modules and configure a server with Apache, PHP, mysql and then install Wordpress on it. 

TODO

## Tomcat

In this example, we will spin up a CentOS box, install Tomcat and put a sample War file on it. 

TODO