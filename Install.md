
# Minimum requirements

* Operating Sistem: Linux
* Apache 2.2.x
* MySQL 5.5
* PHP 5.3.x with extensions:
   * PDO
   * PDO_mysql
   * JSON
   * GDlib 2

# Configuration
Clone the repository and publish the src folder in your webserver.

Open config.xml, located inside the folder: application/config/
Edit the network parameters and the DB name, username, password (to access the DB):
 

`<glz:Param name="DB_HOST" value="localhost" />`
`<glz:Param name="DB_NAME" value="movio" />`
`<glz:Param name="DB_USER" value="root" />`
`<glz:Param name="DB_PSW" value="root" />`
  

* DB_HOST: is the name of the data base host in mysql
* DB_NAME: name of the data base
* DB_USER: name of the user connecting to the DB
* DB_PSW: password to connect to the DB


MOVIO can work with different configurations: the developers might use one configuration for the development and one for the production  
To have different configuration, you should copy and modify the congig.xml and rename it as config_DomainName.xml (as example, if you are using www.myserver.com, the file should be named as: config_www.myserver.com; or if the server is www.athenaplus.org you should name the config file as: config_www.athenaplus.org.xml)


You need to use an administration tool to manage mySql (es. phpMyAdmin): create the MOVIO DB (if you want to use a different name, you need to change accordingly the parameter DB_NAME in the config.xml file (see previous steps).

Once you created the DB you need to import the file file install/movio.sql.

The following folders must be set with ‘write’ rights:

* cache
* application/mediaArchive (and all the sub folders)
* application/classes/userModules
* application/startup
* admin/cache


The Mod Rewrite in Apache must be active with right configuration in virtual-host (AllowOverride All)

Open the browser at http://www.myserver.com to check that all has been executed correctly.
Open the admin page http://www.myserver.com/admin/ using the admim/admin to login.

