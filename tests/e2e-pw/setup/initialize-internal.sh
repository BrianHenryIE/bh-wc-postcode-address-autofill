#!/bin/bash

# Print the script name.
echo $(basename "$0")

# Print out all environmental variables
echo "$ printenv"
printenv

echo "Set up pretty permalinks for REST API."
wp rewrite structure '/%year%/%monthnum%/%postname%/' --hard
wp rewrite flush

wp plugin activate woocommerce

# WooCommerce setup
if wp plugin is-active woocommerce; then

  echo "Maybe updating WooCommerce database"
  wp wc update

  echo "Adding pages"
  if [[ '[]' == $(wp post list --name="Blocks Checkout" --post_type="page" --format=json) ]]; then
    echo "Adding the Block Checkout page";
    wp post create --post_type=page --post_title="Blocks Checkout" --post_status=publish ./setup/blocks-checkout-post-content.txt;
  fi

  if [[ '[]' == $(wp post list --name="Shortcode Checkout" --post_type=page --format=json) ]]; then
    echo "Adding the Shortcode Checkout page";
    wp post create --post_type=page --post_title="Shortcode Checkout" --post_status=publish ./setup/shortcode-checkout-post-content.txt;
  fi

  # Create a simple product for checkout testing.
  if ! wp post list --post_type=product --field=post_title 2>/dev/null | grep -q "Test Product"; then
    echo "Creating test product..."
    # The product needs to be a shipping product to allow setting billing and shipping address separate.
    wp wc product create --user=1 --name="Test Product" --regular_price="19.99" --type=simple  --status=publish 2>/dev/null || true
  fi


  # Disable "coming soon" mode.
  wp option update woocommerce_coming_soon no

  # Add shipping methods (flat rate + free shipping) to the default zone.
  echo "Configuring shipping methods..."
  ZONE_METHODS=$(wp wc shipping_zone_method list 0 --user=admin --format=count 2>/dev/null || echo "0")
  if [ "$ZONE_METHODS" = "0" ]; then
    # wp wc shipping_zone_method create 0 --method_id=flat_rate --user=admin
    wp wc shipping_zone_method create 0 --method_id=free_shipping --user=admin
    echo "Shipping methods added."
  else
    echo "Shipping methods already configured."
  fi

  # Ensure both addresses can be visible on the checkout.
  # shipping | billing | billing_only
  wp option update woocommerce_ship_to_destination shipping

  # Enable a payment gateway
  wp wc payment_gateway update cheque --enabled=1 --user=admin

else
	echo "WooCommerce is not active, skipping WooCommerce setup."
fi


wp plugin activate --all
