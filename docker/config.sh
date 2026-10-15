#!/bin/sh

UPDATE_HOSTS=${HAS_SUDO:-true}

# Add host.docker.internal to /etc/hosts
if [ "$UPDATE_HOSTS" = "true" ] && ! grep -q "host.docker.internal" /etc/hosts; then
  echo "host.docker.internal has to be in your hosts-file, to add you need sudo privileges"
  sudo sh -c 'echo "127.0.0.1 host.docker.internal" >> /etc/hosts'
fi

# setup config & key files
APPCONFIG_ROOTDIR=${APPCONFIG:-'../appconfig'}

DIR="${APPCONFIG_ROOTDIR}/templates/docker/projectaanvraag/api/"
if [ -d "$DIR" ]; then
  cp -R "$DIR"/* .
  # needed because it is hidden
  cp "$DIR"/.env .
else
  echo "Error: missing appconfig see docker.md prerequisites to fix this."
  exit 1
fi
