#!/bin/sh

# setup config & key files
DIR="../appconfig/files/projectaanvraag/docker/"
if [ -d "$DIR" ]; then
  cp -R "$DIR"/* .
  # needed because it is hidden
  cp "$DIR"/.env .
else
  echo "Error: missing appconfig see docker.md prerequisites to fix this."
  exit 1
fi