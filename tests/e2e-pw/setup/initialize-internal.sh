#!/bin/bash

echo "$ printenv"
printenv

echo "Set up pretty permalinks for REST API."
wp rewrite structure /%year%/%monthnum%/%postname%/ --hard;

echo "Adding the Block Checkout page"
wp post create --post_type=page --post_title="Blocks Checkout" --post_status=publish ./e2e-setup/blocks-checkout-post-content.txt

echo "Adding the Shortcode Checkout page"
# wp post list --name="shortcode-checkout" --post_type=page --format=ids
wp post create --post_type=page --post_title="Shortcode Checkout" --post_status=publish ./e2e-setup/shortcode-checkout-post-content.txt

echo "Installing latest build of bh-wc-postcode-address-autofill"
wp plugin install ./e2e-setup/bh-wc-postcode-address-autofill.latest.zip --activate --force
