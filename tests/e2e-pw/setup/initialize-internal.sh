#!/bin/bash

# Print the script name.
echo $(basename "$0")

# Print out all environmental variables
echo "$ printenv"
printenv

echo "Set up pretty permalinks for REST API."
wp rewrite structure /%year%/%monthnum%/%postname%/ --hard;

echo "Adding pages"
if [[ '[]' == $(wp post list --name="Blocks Checkout" --post_type="page" --format=json) ]]; then
  echo "Adding the Block Checkout page";
  wp post create --post_type=page --post_title="Blocks Checkout" --post_status=publish ./setup/blocks-checkout-post-content.txt;
fi

if [[ '[]' == $(wp post list --name="Shortcode Checkout" --post_type=page --format=json) ]]; then
  echo "Adding the Shortcode Checkout page";
  wp post create --post_type=page --post_title="Shortcode Checkout" --post_status=publish ./setup/shortcode-checkout-post-content.txt;
fi

wp plugin activate --all

# https://sarathlal.com/create-shipping-zone-and-add-shippig-method-in-to-shipping-zone-using-wp-cli-wordpress/
echo "Configuring shipping"
if [[ '[]' == $(wp wc shipping_zone_method list 0 --format=json) ]]; then
  echo "Adding free shipping";
  wp wc shipping_zone_method create 0 --method_id="free_shipping";
fi;

echo "Maybe updating WooCommerce database"
wp wc update