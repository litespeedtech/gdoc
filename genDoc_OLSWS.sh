#!/bin/sh

rm -rf docs/*

cd scripts
php gen_ows_help.php
cd ..

cp static/ows/*.html docs/
cp -R static/css docs/css
cp -R static/img docs/img
