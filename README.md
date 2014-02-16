GCS - Online Compiler
===

## About
GCS is a website which allows users to create and compile multi-platform C/C++ programming projects.


## Dependencies

* Operating system: GNU/Linux
* Server: XAMPP
* Compilers: gcc and g++

GCS also needs the following packets (found in the repositories for Ubuntu) for working:
* gcc-multilib : For compatibility between 32-bit and 64-bit architectures.
* g++-multilib : For compatibility between 32-bit and 64-bit architectures.
* mingw-w64-i686 : Cross-compiling to Windows (32 bits)
* mingw-w64-x86-64 : Cross-compiling to Windows (64 bits)


## Automatic instalation (localhost)

* Clone this repository.
```
git clone git@github.com:Neodivert/gcs
```

* cd to gcs directory
```
cs gcs
```

* Initialize and update repository's submodules
```
git submodule init
git submodule update
```

* cd to install directory
```
cs gcs/install
```

* Execute installer and follow instructions.
```
./install_localhost.sh
```
