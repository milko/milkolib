#!/bin/bash

#####################################################################################
#                                                                                   #
# Script for generating documentation.										        #
#                                                                                   #
#####################################################################################

echo
echo "********************************************************************************"
echo "*                             Generate documentation                           *"
echo "********************************************************************************"
directory=`dirname $0`
script=$directory/vendor/apigen/apigen/bin/apigen
cd $directory
$script generate --config apigen.conf

echo
echo "=> Done"
echo
