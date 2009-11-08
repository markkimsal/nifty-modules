#!/bin/bash
VER=`grep "version" meta.ini`
VER=`echo "$VER" | cut -c 16-`
REV=`git rev-parse --short HEAD`
PKG="Metrof_RemoteWebdav-1.$VER-$REV.zip"
#echo $VER
cd ..
zip -r $PKG remotewebdav -x "remotewebdav/.git*" -x "*~" -x "remotewebdav/mkzip.sh"
