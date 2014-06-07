#!/usr/bin/env bash

DIR="$(cd "$(dirname ${BASH_SOURCE[0]})"  && pwd)"
SECONDS=30

while true; do
	sleep $SECONDS
	cd $DIR/../ ; ./yiic uscore score
	cd $DIR/../ ; ./yiic tscore score
done;
