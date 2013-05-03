#!/bin/sh

if [ -e docs ]; then
    rm -rf docs/*
else
    mkdir docs
fi

cd scripts
php gen_ws_help.php
cd ..

cp static/ws/*.html docs/
cp -Rf static/css docs/
cp -Rf static/img docs/

