#!/bin/sh
for i in auth bean cache concurrent console database event helper http-message http-server log pool redis redis-subscribe route server session validate view websocket sync-invoke
do
  echo -- $i
  cd src/$i
  git checkout $1
  cd ../../
done
