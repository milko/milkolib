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
directory=`pwd`
script=$directory/vendor/apigen/apigen/bin/apigen
$script generate --config $directory/apigen.conf

echo
echo "=> Done"
echo
