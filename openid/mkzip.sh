#!/bin/bash
VER=`grep "version" meta.ini`
VER=`echo "$VER" | cut -c 16-`
REV=`git rev-parse --short HEAD`
PKG="Metrof_OpenID-1.$VER-$REV.zip"
#echo $VER
cd ..
zip -r $PKG openid -x "openid/.git*" -x "*~" -x "openid/mkzip.sh" -x "openid/install.ini" -x "openid/local.ini"
