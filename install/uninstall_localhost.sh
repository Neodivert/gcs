#!/bin/bash

# Step 0: Check if we have root privileges and load configuration file.
###############################################################################

if [ "$(whoami)" != "root" ]; then
	printf "ERROR: This command must be run with sudo\n" 1>&2
	exit 1
fi

source config.cfg

# Step 1: Check if user gave us an argument (as expected)
###############################################################################

if [ $# -ne 1 ]; then
	printf "ERROR: \"unistansll_localhost.sh\" expects a path string\n" 1>&2
	printf "\tUsage: ./unistansll_localhost.sh \"path\"\n" 1>&2
	exit 1
fi


# Step 2: Check if utilities file is found in path given by user.
# The utilities file is neccesary in order to retrieve DB information.
###############################################################################

# Retrieve web path and remove the '/' at the end (if exists).
WEB_PATH=${1%/}

UTILITIES_FILE="${WEB_PATH}/php/utilities.php"
if [ ! -f "$UTILITIES_FILE" ]; then
	printf "ERROR: expected file [%s] not found\n" $UTILITIES_FILE 1>&2
	exit 1
fi


# Step 3: Retrieve database info.
###############################################################################

printf "Retrieving database info ...\n"

DB_NAME=`grep -o "db_name *= *.*;.*" $UTILITIES_FILE | sed "s/.*'\(.*\)'.*/\1/g"`
DB_USER_NAME=`grep -o "db_user_name *= *.*;.*" $UTILITIES_FILE | sed "s/.*'\(.*\)'.*/\1/g"`

printf "Retrieving database info ...OK\n"


# Step 4: Wait for user confirmation.
###############################################################################

printf "This uninstall script will perform the following actions: \n"
printf " - Delete MySQL database [%s]\n" $DB_NAME
printf " - Delete MySQL user [%s]\n" $DB_USER_NAME
printf "	- DELETE ENTIRE DIRECTORY (INCLUDING USERS CONTENT DIRECTORY) [%s]\n\n" $WEB_PATH

# Ask user for permission.
read -p "Uninstall? (y/n): " -n 1 -r

# Exit if user didn't give us confirmation.
echo # Move to a new line
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
	printf "Uninstall script exited by user\n\n"
	exit 1
fi


# Step 5: Ask the user for him/her administrative MySQL password.
############################################################################### 

# Get mysql command's path
mysql="${XAMPP_DIRECTORY}/bin/mysql"

# Ask the user for him/her administrative MySQL password.
MYSQL_PASSWORD=`./install_utilities/src/get_mysql_user_password.sh "$mysql"`
printf "\n"


# Step 6: Database uninstall
###############################################################################

# Start XAMPP MySQL
printf "Starting MySQL ...\n"
sudo ${XAMPP_DIRECTORY}/lampp startmysql
printf "Starting MySQL ...OK\n"

# Delete MySQL user.
printf "Deleting MySQL user [%s] ...\n" "${DB_USER_NAME}"
"$mysql" -u root --password="${MYSQL_PASSWORD}" -e "DROP USER '$DB_USER_NAME'@'localhost';"
printf "Deleting MySQL user [%s] ...OK\n" "${DB_USER_NAME}"

# Delete MySQL database.
printf "Deleting MySQL database [%s] ...\n" "${DB_NAME}"
"$mysql" -u root --password="${MYSQL_PASSWORD}" -e "DROP DATABASE $DB_NAME;"
printf "Deleting MySQL database [%s] ...OK\n" "${DB_NAME}"

# Backup users dir.
USERS_DIR="$WEB_PATH/users_dirs"
printf "Making a backup of users dir [%s] ...\n" "${USERS_DIR}"
current_date=`date +%H_%M_%S__%d_%m_%Y`
users_dir_backup="backup_gcs_users_dir_$current_date.zip"
zip -r $users_dir_backup $USERS_DIR
printf "Making a backup of users dir [%s] ...OK\n" "${USERS_DIR}"

# Delete web path.
printf "Deleting web path [%s] ...\n" $WEB_PATH
rm -r $WEB_PATH
printf "Deleting web path [%s] ...OK\n" $WEB_PATH


# Step 6: Done!
###############################################################################

printf "\n\nUsers dir backup saved in [%s]\n" $users_dir_backup
printf "GCS uninstall script has finished\n\n"


# Reference
###############################################################################
# 15 Practical Grep Command Examples In Linux / UNIX - The geek stuff
# http://www.thegeekstuff.com/2009/03/15-practical-unix-grep-command-examples/
#
# sed get string between two delimiters - permanent TODO
# http://blog.dragon-tortuga.net/?p=812
#
# How do you append to an already existing string? - Stack Overflow
# http://stackoverflow.com/questions/2250131/how-do-you-append-to-an-already-existing-string
#
# Sintaxis de DROP USER - MySQL 5.0 Reference Manual
# https://dev.mysql.com/doc/refman/5.0/es/drop-user.html
#
# Sintaxis de DROP DATABASE - MySQL 5.0 Reference Manual
# https://dev.mysql.com/doc/refman/5.0/es/drop-database.html
###############################################################################
