#!/bin/sh

if [ -e docs ]; then
    rm -rf docs/*
else
    mkdir docs
fi

cd scripts
php gen_ows_help.php
cd ..

cp -Rf static/css docs/
cp -Rf static/img docs/
