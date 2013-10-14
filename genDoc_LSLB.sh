#!/bin/sh

if [ -e docs ]; then
    rm -rf docs/*
else
    mkdir docs
fi

if [ -e forweb ]; then
    rm -rf forweb/*
fi
mkdir -p forweb/docs

cd scripts
php gen_lb_help.php
cd ..

cp -Rf static/css docs/
cp -Rf static/img docs/
