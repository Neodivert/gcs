# GCS - Online Compiler

## About
GCS is a website which allows users to create and compile multi-platform C/C++ programming projects.

## Current status

Project functional, **but inactive and pottentially unsafe for its use in production**.

## Authors
All the code except the one listed in next "Third-party" libraries was done by:
* Moisés J. Bonilla Caraballo.
* Garoe Dorta Pérez (https://github.com/Garoe).

## Dependencies

* Operating system: GNU/Linux
* Server: XAMPP
* Compilers: gcc and g++

GCS also needs the following packets (found in the repositories for Ubuntu) for working:
* gcc-multilib : For compatibility between 32-bit and 64-bit architectures.
* g++-multilib : For compatibility between 32-bit and 64-bit architectures.
* mingw-w64-i686 : Cross-compiling to Windows (32 bits)
* mingw-w64-x86-64 : Cross-compiling to Windows (64 bits)


## Third-party libraries

GCS makes use of the following third-party libraries:
* jQuery 1.7.2 (http://jquery.com/)
* jQuery UI 1.8.20 (https://jqueryui.com/)
* md5 2.2 (http://pajhome.org.uk/crypt/md5/)

## Automatic instalation (localhost)

1. Install dependencies listed in previous "Dependencies" section.

2. Clone this repository.
 ```
 git clone git@github.com:moisesjbc/gcs
 ```

3. cd to gcs directory
 ```
 cs gcs
 ```

4. Initialize and update repository's submodules
 ```
 git submodule init
 git submodule update
 ```

5. cd to install directory
 ```
 cs install
 ```

6. Execute installer and follow instructions.
 ```
 sudo ./install_localhost.sh
 ```

## Automatic uninstallation (localhost)

1. cd to install directory

 ```
 cs gcs/install
 ```

2. Execute uninstall script and follow instructions.

 ```
 sudo ./uninstall_localhost.sh
 ```
