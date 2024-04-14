#!/bin/bash

# Set default Xdebug mode (can be overridden)
XDEBUG_MODE=""

# Loop through arguments, checking for --coverage
for arg in "$@"
do
  if [[ "$arg" =~ ^--coverage(-[a-zA-Z0-9]+)?$ ]]; then
    export XDEBUG_MODE=coverage
    break  # Exit the loop after finding coverage flag
  fi
done

# Run PEST with remaining arguments
./vendor/bin/pest $@
