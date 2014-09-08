#Basic Speed Portal Installation

##Important: 

**It is advised to work with someone in your organisation who understands software programming and servers.  The following instructions assume experince with Linux server administration.**

##Requirements

The following must be installed in the target system:

- PHP version >= 5.2.3
- MySQL version >= 5.0
- An HTTP Server such as:
	- Apache 1.3+
	- Apache 2.0+
	- Lighttpd
	- Microsoft Internet Information Server (MS IIS)
	- Nginx
- Unicode support in the operating system

Additionally, the following is a list of PHP extensions that must be installed on your server:

- PCRE (<http://php.net/pcre>) must be compiled with --enable-utf8 and --enable-unicode-properties for UTF-8 functions to work properly.
- iconv (<http://php.net/iconv>) is required for UTF-8 transliteration.
- mcrypt (<http://php.net/mcrypt>) is required for encryption.
- SPL (<http://php.net/spl>) is required for several core libraries
- mbstring (<http://php.net/mbstring>) which speeds up Kohana's UTF-8 functions.
- cURL (<http://php.net/curl>) which is used to access remote sites.
- MySQL (<http://php.net/mysql>) is required for database access.
- GD (<http://php.net/gd>) is required for image manipulation.
- IMAP (<http://php.net/imap>) is required for handling e-mails.

The code itself, can be accessed from GitHub, so you will need a GitHub account to download it.

##Installation

###Creating a new Ushahidi instance

1. SSH into the server as root, or su­ to root
	
2. Navigate to the web directory:

		cd /var/www/website
	
3. Clone the Ushahidi source code into the folder you want it accessible from.  The recursive option is required as many of the plugins are installed as GIT submodules:

		git clone --recursive git@bitbucket.org:andydixon/ushahidi_web-wvspeed.git <foldername>
	
4. Change the permissions:

		cd <foldername>
		chmod -­R 777 application/config
		chmod ­-R 777 application/cache
		chmod ­-R 777 application/logs
		chmod ­-R 777 media/uploads
		chmod 777 .htaccess
	
###Database

1. Login to MySQL (enter password when prompted)

		mysql ­-u root -p
	
2. Create a database for the site:

		CREATE DATABASE <databasename>;
	
3. Create a username and password for the site, replacing the databasename, username and password:

		GRANT SELECT, INSERT, DELETE, UPDATE, CREATE, DROP, ALTER, INDEX, LOCK TABLES on <databasename>.* TO '<username>'@'localhost' IDENTIFIED BY '<password>';
	
4. Flush privileges for the new user:

		FLUSH PRIVILEGES;

###Setup
	
Visit the new install in a web browser and you will be redirected to the installation wizard.  Follow the on­screen instructions, entering the database name entered previously, the database username and password with the details above, as well as a few other questions.

This will then set up the database for the site, configure the default user and sort out a few initial site related setting

###House keeping

1. Remove the installation directory for security by running this command from within the new folder:

		rm -rf installer

###Manuals

- [Speed General User Guide](http://speedevidence.files.wordpress.com/2013/10/speed-general-user-guide-v2.pdf)
- [Speed Information Manager Guide](http://speedevidence.files.wordpress.com/2014/09/speed-portal-information-manager-guide.docx)
- [Speed Login video](http://speedevidence.wordpress.com/2013/10/17/how-to-speed-how-to-login/)
- [Speed Features video](http://speedevidence.wordpress.com/2013/10/17/how-to-speed-portal-features/)
- [SMAP Manual](https://speedevidence.files.wordpress.com/2014/09/smap-manual-v2-0.pdf)

###SMAP

####Option One:  
- Rent a cloud server from Smap Consulting for $100 per month including support
- Contact Neil Penman: <neilpenman@gmail.com>

####Option Two:  
- Download SMAP onto your own server
- Runs on Ubuntu Linux and requires 2GB of Memory & 20GB of disk
- Download and install the server software from <http://www.smap.com.au/downloads.shtml> 
- Add backups
- You will also need Android based smartphones with the "Fieldtask" app loaded on them for the data collection
- It is recommended to buy the phones locally & check they have the languages you need, GPS, and appropriate screen size


###FrontlineSMS
###FrontlineCloud

- Go to <https://cloud.frontlinesms.com/register> and create an account
- A mobile phone with sufficient credit on it to send and receive text messages that is connected to the local wifi, with “FrontlineSync” or “SMSsync” app loaded on it
- FrontlineSMS 
- Go to <http://www.frontlinesms.com/technologies/download/> to download Desktop App and follow instructions
- A compatible modem, with sufficient credit on it to send and receive text messages
- The <http://www.frontlinesms.com> website has lots of easy to use "help" guides and resources available to assist you getting set up.

