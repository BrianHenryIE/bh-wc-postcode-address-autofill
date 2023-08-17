#!/bin/bash

echo "$ printenv"
printenv

echo "Set up pretty permalinks for REST API."
wp rewrite structure /%year%/%monthnum%/%postname%/ --hard;

echo "Adding the Block Checkout page"
wp post create --post_type=page --post_title="Blocks Checkout" --post_status=publish ./wp-content/plugins/bh-wc-postcode-address-autofill/tests/e2e-pw/blocks-checkout-post-content.txt
