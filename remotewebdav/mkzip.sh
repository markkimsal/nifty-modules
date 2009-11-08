#!/bin/bash
VER=`git rev-parse --short HEAD`
PKG="Metrof_RemoteWebdav-1.0.$VER.zip"
echo $VER
cd ..
zip -r $PKG remotewebdav -x "remotewebdav/.git*" -x "*~" -x "remotewebdav/mkzip.sh"
