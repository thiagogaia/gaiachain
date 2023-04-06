#!/bin/bash

echo "Transfering $3 gaiacoins from $1 to $2"
#http --form post :`cat data/$1.port`/transfer to=`cat data/$2.port` amount=$3
catTo=`cat data/$1.port`
catFrom=`cat data/$2.port`
curl -X POST -d "from=$catFrom&amount=$3&to=$1" "localhost:$catTo/transfer"
#./bin/transfer.sh gaia joao 2