# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure("2") do |config|
  config.vm.box = 'geerlingguy/ubuntu1604'
  config.vm.network :private_network, ip: '172.31.31.30', hostsupdater: 'skip'
  config.vm.provider 'virtualbox' do |vb|
    vb.name = config.vm.hostname
    vb.customize ['modifyvm', :id, '--cpus', 1]
    vb.customize ['modifyvm', :id, '--memory', 512]

    # Fix for slow external network connections
    vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
  end
    config.vm.provision "ansible" do |ansible|
      ansible.playbook = "res/provision/ansible/server.yml"
      ansible.galaxy_role_file = "res/provision/ansible/requirements.yml"
      ansible.galaxy_roles_path = "res/provision/ansible/roles"
      ansible.extra_vars = {
          hostip: "default",
          user: "vagrant"
      }
    end
end
