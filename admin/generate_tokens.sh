#!/bin/bash

for i in `seq 10`
do
	head -c 100 /dev/urandom | md5sum | cut -c1-10
done
