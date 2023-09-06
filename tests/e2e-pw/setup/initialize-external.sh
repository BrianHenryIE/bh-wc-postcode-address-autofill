#!/bin/bash

# Script which runs outside Docker

echo $(basename $PWD)

# Build the plugin
npm run build
vendor/bin/wp i18n make-pot src languages/$(basename $PWD).pot --domain=$(basename $PWD)
vendor/bin/wp dist-archive . ./tests/e2e-pw/setup --plugin-dirname=$(basename $PWD) --filename-format="{name}.latest"

## Configure the environment
wp-env run tests-cli ./e2e-setup/initialize-internal.sh;
