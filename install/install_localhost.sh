#!/bin/bash

# Default configuration
###############################################################################
declare -A default_config

default_config[DB_USER_PASSWORD]="1234"
default_config[MYSQL_PASSWORD]=""


# Auxiliar functions
###############################################################################

function remove_last_slash(){
	# Exit if user didn't pass us exactly one argument.
	if [ $# -ne 1 ]; then
		printf "ERROR: \"remove_last_slash\" function expects 1 argument (arguments passed: $#)\n" 1>&2
		exit -1
	fi

	# If the given string contains a slash ('/') at the end, remove it and return
	# the resulting string.
	# TODO: Remove one OR MORE slashs?
	echo ${1%/}
}


# Step 0: Check if we have root privileges.
###############################################################################

if [ "$(whoami)" != "root" ]; then
	printf "ERROR: This command must be run with sudo\n" 1>&2
	exit 1
fi


# Step 1: Load configuration file.
###############################################################################

# Load configuration file.
source config.cfg

# If the given path contains a "/" at the end, remove it.
XAMPP_DIRECTORY=`remove_last_slash "$XAMPP_DIRECTORY"`

# Construct the web path.
WEB_PATH="${XAMPP_DIRECTORY}/htdocs/${WEB_NAME}"


# Step 2: Wait for user confirmation.
###############################################################################

# Tell the user the steps that this instalation script will perform.
printf "The instalation script will perform the following steps: \n";
printf "* Copying web data to [%s]\n" "${WEB_PATH}"
printf "* Creating mysql database [%s]\n" "${DB_NAME}"
printf "* Creating mysql user [%s]\n" "${DB_USER_NAME}"
printf "* Restarting XAMPP\n\n"
printf "If you want to change any configuration, exit this install and edit the file \"config.cfg\" before trying again.\n\n"

# Ask user for permission.
read -p "Install? (y/n): " -n 1 -r

# Exit if user didn't give us confirmation.
echo # Move to a new line
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
	printf "Install script exited by user\n\n"
	exit 1
fi


# Step 3: Check if XAMPP is installed where the user said.
###############################################################################

# Check if XAMPP directory exists.
if [ ! -d "${XAMPP_DIRECTORY}" ]; then
	printf "ERROR: directory [%s] not found\n" "${XAMPP_DIRECTORY}" 1>&2
	exit 1
fi

# Check if XAMPP directory contains "lampp" executable.
if [ ! -f "${XAMPP_DIRECTORY}/lampp" ]; then
	printf "ERROR: executable [%s] not found\n" "${XAMPP_DIRECTORY}/lampp" 1>&2
	exit 1
fi

printf "XAMPP found in [%s]\n" ${XAMPP_DIRECTORY}


# Step 4: Check if given web path isn't already in use.
###############################################################################

# Check if web path isn't already in use.
if [ -d "${WEB_PATH}" ]; then
	printf "ERROR: web path [%s] already in use\n" "${WEB_PATH}" 1>&2
	exit 1
fi


# Step 5: Start XAMPP
###############################################################################

printf "Starting MySQL ...\n"
sudo ${XAMPP_DIRECTORY}/lampp startmysql
printf "Starting MySQL ...OK\n"

# Get mysql command's path
mysql="${XAMPP_DIRECTORY}/bin/mysql"


# Step 6: Check if a MYSQL database and/or user with the given names already 
# exist.
###############################################################################

# Check if a MySQL database with the given name already exists.
if [[ ! -z "`$mysql -u root --password="${MYSQL_PASSWORD}" -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$DB_NAME'" 2>&1`" ]];
then
	printf "ERROR: database [%s] already exists\n" $DB_NAME 1>&2
	exit 1
fi

# Check if a MySQL user with the given name already exists.
if [[ ! -z "`$mysql -u root --password=${MYSQL_PASSWORD} -e "SELECT 1 FROM mysql.user WHERE user = '$DB_USER_NAME'" 2>&1`" ]] ;then
	printf "ERROR: MySQL user [%s] already exists\n" $DB_USER_NAME 1>&2
	exit 1
fi


# Step 7: Database instalation
###############################################################################

# Ask the user for him/her administrative MySQL password.
read -e -s -p "Write your database administrative password (Used for login in phpmyadmin): " -i "${default_config[MYSQL_PASSWORD]}" MYSQL_PASSWORD
echo

# Ask the user for a password for the database user.
read -e -s -p "Write a password for the GCS database user: " DB_USER_PASSWORD
echo

# Create the database
printf "Creating database [%s] ...\n" "${DB_NAME}"
"$mysql" -u root --password="${MYSQL_PASSWORD}" -e "create database ${DB_NAME}"
printf "Creating database [%s] ...OK\n" "${DB_NAME}"

# Import the database structure from file ../bd/bd-gcs.sql.
printf "Importing database structure from file ...\n"
"$mysql" -u root --password="${MYSQL_PASSWORD}" "${DB_NAME}" < ../bd/bd_gcs.sql
printf "Importing database structure from file ...OK\n"

# Create the database user.
printf "Creating mysql user [%s] ...\n" "${DB_USER_NAME}"
"$mysql" -u root --password="${MYSQL_PASSWORD}" -e "CREATE USER '${DB_USER_NAME}'@'localhost' IDENTIFIED BY '${DB_USER_PASSWORD}';"
printf "Creating mysql user [%s] ...OK\n" "${DB_USER_NAME}"

# Allow the created user to perfom SELECT on the database.
DB_USER_PRIVILEGES="DELETE, INSERT, SELECT, UPDATE"

printf "Giving [%s] privileges to user [%s] ...\n" "${DB_USER_PRIVILEGES}" "${DB_USER_NAME}"
$mysql -u root --password="${MYSQL_PASSWORD}" -e "GRANT ${DB_USER_PRIVILEGES} ON ${DB_NAME}.* TO '${DB_USER_NAME}'@'localhost';"
"$mysql" -u root --password="${MYSQL_PASSWORD}" -e "FLUSH PRIVILEGES;"
printf "Giving [%s] privileges to user [%s] ...OK\n" "${DB_USER_PRIVILEGES}" "${DB_USER_NAME}"


# Step 8: Directory instalation
###############################################################################

printf "Copying web content to [%s] ...\n" "${WEB_PATH}"
sudo cp -r "../web" "${WEB_PATH}"
sudo chown -R "$APACHE_USER" "${WEB_PATH}"
sudo chmod -R 0755 "${WEB_PATH}"
printf "Copying web content to [%s] ...OK\n" "${WEB_PATH}"

utilities_file="${WEB_PATH}/php/utilities.php"
printf "utilities_file: [%s]\n" "$utilities_file"

printf "Personalizing web configuration ...\n"
sudo sed -i "s/~~DB_USER_NAME~~/'${DB_USER_NAME}'/g" "$utilities_file"
sudo sed -i "s/~~DB_USER_PASSWORD~~/'${DB_USER_PASSWORD}'/g" "$utilities_file"
sudo sed -i "s/~~DB_NAME~~/'${DB_NAME}'/g" "$utilities_file"
printf "Personalizing web configuration ...OK\n"


# Step 9: Done!
###############################################################################

printf "\n\nInstall finished. Now you can visit \"localhost/$WEB_NAME\"\n\n"
exit 0


# References
###############################################################################
# Remove slash from the end of a variable - Stack Overflow
# http://stackoverflow.com/questions/1848415/remove-slash-from-the-end-of-a-variable
#
# How to return a string value from a bash function - Stack Overflow
# http://stackoverflow.com/questions/3236871/how-to-return-a-string-value-from-a-bash-function
#
# Unix Sed Tutorial: Find and Replace Text Inside a File Using RegEx - The geek stuff
# http://www.thegeekstuff.com/2009/09/unix-sed-tutorial-replace-text-inside-a-file-using-substitute-command/
#
# Bash script: Trabajando en sed con variables bash - La plaga Tux
# http://plagatux.es/2010/03/bash-script-trabajando-en-sed-con-variables-bash/
#
# sed doesn't accept $variable in bash script - LinuxQuestions.org
# http://www.linuxquestions.org/questions/programming-9/sed-doesn%27t-accept-$variable-in-bash-script-325935/
# 
# Bash: Check if sudo - Ubuntu forums
# http://ubuntuforums.org/showthread.php?t=479255
#
# Config files for your script - Bash Hackers Wiki
# http://wiki.bash-hackers.org/howto/conffile
#
# How to check if mysql database exists - Stack Overflow
# http://stackoverflow.com/questions/838978/how-to-check-if-mysql-database-exists
#
# Sintaxis de GRANT y REVOKE - MySQL 5.0 Reference Manual
# https://dev.mysql.com/doc/refman/5.0/es/grant.html
#
# What does -z mean in Bash? - Stack Overflow
# http://stackoverflow.com/questions/18096670/what-does-z-mean-in-bash
#
# Checking if mysql user exists - Stack Overflow
# http://stackoverflow.com/questions/3049929/checking-if-mysql-user-exists
#
# How to Use MySQL GRANT to Grant Privileges to Account - MySQL tutorial
# http://www.mysqltutorial.org/mysql-grant.aspx
#
# PHP mkdir: Permission denied problem - Stack Overflow
# http://stackoverflow.com/questions/5246114/php-mkdir-permission-denied-problem
###############################################################################
