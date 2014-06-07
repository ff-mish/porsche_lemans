#!/usr/bin/env bash

DIR="$(cd "$(dirname ${BASH_SOURCE[0]})"  && pwd)"
SECONDS=60

while true; do
	sleep $SECONDS
	cd $DIR/../ ; ./yiic weibo searchtag
	cd $DIR/../ ; ./yiic twitter searchtag
done;
