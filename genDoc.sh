#!/bin/sh

if [ "$1" = "lb" ]  || [ "$1" = "ws" ] || [ "$1" = "ows" ] ; then
    DOCTYPE=$1
else
    echo "Invalid parameter, requires type [lb|ws|ows]"
    exit
fi

CUR_DIR=`pwd`
SCRIPT_DIR=`dirname "$0"`
cd "${SCRIPT_DIR}"

if [ -e output ] ; then
    rm -rf output/*
else
    mkdir output
fi

mkdir -p output/docs
mkdir -p output/tips/lang
mkdir -p output/forweb/docs
mkdir -p output/forweb/lang
mkdir -p output/docs_lang

cd scripts
php gen_main.php $DOCTYPE
cd ..

cp -Rf static/css output/docs/
cp -Rf static/img output/docs/

cd ${CUR_DIR}
