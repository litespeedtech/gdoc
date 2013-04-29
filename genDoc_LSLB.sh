#!/bin/sh
cd scripts
php gen_lb_help.php
cd ..

cp static/lb/*.html docs/
cp -R static/css docs/css
cp -R static/img docs/img
