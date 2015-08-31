# Fabfile to:
#    - update the remote system(s) 
#    - download and install an application

# Import Fabric's API module
from fabric.api import *

env.hosts = [
    'gemz.gallery',
  # 'ip.add.rr.ess
  # 'server2.domain.tld',
]
# Set the username
env.user   = "debbiz"

# Set the password [NOT RECOMMENDED]
#env.password = "nothing"

def update_upgrade():
    """
        Update the default OS installation's
        basic default tools.
                                            """
    sudo("yum update -y")
    #sudo("aptitude -y upgrade")

def deploy():
    """ Pull new code and run script to set permissions """
    with cd("~/public_html/"):
        run("/usr/local/cpanel/3rdparty/bin/git pull")
    #sudo("/scripts/enablefileprotect")

def update_install():

    # Update
    update_upgrade()

    # Install
    install_memcached()
