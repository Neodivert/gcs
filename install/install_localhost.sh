#!/bin/bash

# Default configuration
###############################################################################
declare -A default_config
default_config[XAMPP_DIRECTORY]="/opt/lampp"
default_config[WEB_NAME]="gcs"
default_config[DB_NAME]="db_gcs"
default_config[DB_USER_NAME]="db_gcs"
default_config[BD_USER_PASSWORD]="1234"
default_config[MYSQL_PASSWORD]=""

declare -A config


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


# Instalation configuration
###############################################################################

# Ask the user where is XAMPP installed.
read -e -p "Write the path where XAMPP is installed: " -i "${default_config[XAMPP_DIRECTORY]}" config[XAMPP_DIRECTORY]

# If the given path contains a "/" at the end, remove it.
config[XAMPP_DIRECTORY]=`remove_last_slash "${config[XAMPP_DIRECTORY]}"`

# Check if given directory exits.
if [ ! -d "${config[XAMPP_DIRECTORY]}" ]; then
	printf "ERROR: directory [%s] not found\n" "${config[XAMPP_DIRECTORY]}" 1>&2
	exit -1
fi

# Check if given directory contains "lampp" executable.
if [ ! -f "${config[XAMPP_DIRECTORY]}/lampp" ]; then
	printf "ERROR: executable [%s] not found\n" "${config[XAMPP_DIRECTORY]}/lampp" 1>&2
	exit -1
fi

printf "XAMPP found in [%s]\n" ${config[XAMPP_DIRECTORY]}

# Ask the user for a name for the web.
read -e -p "Write a name for the web: " -i "${default_config[WEB_NAME]}" config[WEB_NAME]

# Check if web path isn't already in use.
config[WEB_PATH]="${config[XAMPP_DIRECTORY]}/htdocs/${config[WEB_NAME]}"
if [ -d "${config[WEB_PATH]}" ]; then
	printf "ERROR: web path [%s] already in use\n" "${config[WEB_PATH]}" 1>&2
	exit -1
fi

# Ask the user for a name for the database.
read -e -p "Write a name for the GCS database: " -i "${default_config[DB_NAME]}" config[DB_NAME]

# Ask the user for a name for the database user.
read -e -p "Write a name for the GCS database user: " -i "${default_config[DB_USER_NAME]}" config[DB_USER_NAME]

# Ask the user for a password for the database user.
read -e -s -p "Write a password for the GCS database user: " -i "${default_config[DB_USER_PASSWORD]}" config[DB_USER_PASSWORD]
echo

# TODO: Allow user to change user_directory (GCS).

printf "The instalation script will perform the following steps: \n";
printf "* Copying web data to [%s]\n" "${config[WEB_PATH]}"
printf "* Creating mysql database [%s]\n" "${config[DB_NAME]}"
printf "* Creating mysql user [%s]\n" "${config[DB_USER_NAME]}"
printf "* Restart XAMPP\n\n"


# Install?
read -p "Install? (y/n): " -n 1 -r

# Exit if user didn't give us confirmation.
echo # Move to a new line
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
	printf "Install script exited by user\n"
	exit -1
fi


# Instalation
###############################################################################

printf "Copying web content to [%s] ...\n" "${config[WEB_PATH]}"
sudo cp -r "../web" "${config[WEB_PATH]}"
printf "Copying web content to [%s] ...OK\n" "${config[WEB_PATH]}"

config[USERS_DIRS]="'users_dirs'"
utilities_file="${config[WEB_PATH]}/php_html/scripts/utilities.php"
printf "utilities_file: [%s]\n" "$utilities_file"

printf "Personalizing web configuration ...\n"
sudo sed -i "s/~~DB_USER_NAME~~/${config[DB_USER_NAME]}/g" "$utilities_file"
sudo sed -i "s/~~DB_USER_PASSWORD~~/${config[DB_USER_PASSWORD]}/g" "$utilities_file"
sudo sed -i "s/~~USERS_DIR~~/${config[USERS_DIRS]}/g" "$utilities_file"
printf "Personalizing web configuration ...OK\n"

#echo sudo sed -e "s|**DB_USER_PASSWORD**|${config[DB_USER_PASSWORD]}|g" "$utilities_file"
#
#echo "2"

#echo sudo sed -e "s|**DB_USERS_DIR**|${config[DB_USERS_DIR]}|g" "$utilities_file"
#sudo sed -e "s|**DB_USERS_DIR**|${config[DB_USERS_DIR]}|g" "$utilities_file"
#echo "3"




# Database instalation
###############################################################################

# Start XAMP
printf "Restarting XAMPP ...\n"
sudo ${config[XAMPP_DIRECTORY]}/lampp restart
printf "Restarting XAMPP ...OK\n"

mysql="${config[XAMPP_DIRECTORY]}/bin/mysql"

# Ask the user for him/her administrative MySQL password.
read -e -s -p "Write your database administrative password (Used for login in phpmyadmin): " -i "${default_config[MYSQL_PASSWORD]}" config[MYSQL_PASSWORD]
echo

# Create the database
printf "Creating database [%s] ...\n" "${config[DB_NAME]}"
"$mysql" -u root --password="${config[MYSQL_PASSWORD]}" -e "create database ${config[DB_NAME]}"
printf "Creating database [%s] ...OK\n" "${config[DB_NAME]}"

# Import the database structure from file ../bd/bd-gcs.sql.
printf "Importing database structure from file ...\n"
"$mysql" -u root --password="${config[MYSQL_PASSWORD]}" "${config[DB_NAME]}" < ../bd/bd_gcs.sql
printf "Importing database structure from file ...OK\n"

# Create the database user.
printf "Creating mysql user [%s] ...\n" "${config[DB_USER_NAME]}"
"$mysql" -u root --password="${config[MYSQL_PASSWORD]}" -e "CREATE USER '${config[DB_USER_NAME]}'@'localhost' IDENTIFIED BY '${config[DB_USER_PASSWORD]}';"
printf "Creating mysql user [%s] ...OK\n" "${config[DB_USER_NAME]}"

# Allow the created user to perfom SELECT on the database.
printf "Giving SELECT privileges to user [%s] ...\n" "${config[DB_USER_NAME]}"
$mysql -u root --password="${config[MYSQL_PASSWORD]}" -e "GRANT SELECT ON ${config[DB_NAME]}.* TO '${config[DB_USER_NAME]}'@'localhost';"
"$mysql" -u root --password="${config[MYSQL_PASSWORD]}" -e "FLUSH PRIVILEGES;"
printf "Giving SELECT privileges to user [%s] ...OK\n" "${config[DB_USER_NAME]}"


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
###############################################################################
