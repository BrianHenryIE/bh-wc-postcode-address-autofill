#!/bin/bash

# Print the script name.
echo $(basename "$0")


echo "Installing latest build of bh-wc-postcode-address-autofill"
wp plugin install ./setup/bh-wc-postcode-address-autofill.latest.zip --activate --force
