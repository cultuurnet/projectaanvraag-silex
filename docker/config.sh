#!/bin/sh

UPDATE_HOSTS=${HAS_SUDO:-true}

HOSTS="projectaanvraag.publiq.local"

if [ "$UPDATE_HOSTS" = "true" ]; then
  MISSING_HOSTS=""

  set -- $HOSTS
  for HOST; do
    if ! grep -q "$HOST" /etc/hosts; then
      MISSING_HOSTS="$MISSING_HOSTS $HOST"
    fi
  done

  set -- $MISSING_HOSTS
  for MISSING_HOST; do
    echo "$MISSING_HOST has to be in your hosts-file, to add you need sudo privileges"
    sudo sh -c "echo \"\n127.0.0.1 $MISSING_HOST\" >> /etc/hosts"
  done
fi

APPCONFIG_ROOTDIR=${APPCONFIG:-'../appconfig'}

DIR="${APPCONFIG_ROOTDIR}/templates/docker/projectaanvraag/api"
if [ -d "$DIR" ]; then
  cp -R "$DIR"/* .
  cp "${DIR}/.env" .env
else
  echo "Error: missing appconfig. The appconfig repository must be cloned at ${APPCONFIG_ROOTDIR}."
  exit 1
fi
