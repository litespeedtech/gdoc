#!/bin/sh

rm -rf docs/*

cd scripts
php gen_ws_help.php
cd ..

cp static/ws/*.html docs/
cp -R static/css docs/css
cp -R static/img docs/img

