#!/usr/bin/env bash

DIR="$(cd "$(dirname ${BASH_SOURCE[0]})"  && pwd)"


while true; do
	sleep 30
	cd $DIR/../ ; ./yiic uscore score
	cd $DIR/../ ; ./yiic tscore score
done;
