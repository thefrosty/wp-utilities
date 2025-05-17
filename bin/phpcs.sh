#!/usr/bin/env bash

set -e

source "$(dirname "$0")/functions.sh"
echo 'Checking PHPCS'

args="${ARGS:=--runtime-set testVersion ${PHP_VERSION}- $*}"
phpFiles=""
phpFilesCount=0
for f in ${commitFiles}; do
  if [[ ! -e ${f} ]]; then
    continue
  fi
  if [[ ${f} =~ \.(php|ctp)$ ]]; then
    phpFilesCount=$((phpFilesCount + 1))
    phpFiles="$phpFiles $f"
  fi
done
if [[ ${phpFilesCount} == 0 ]]; then
  echo "No PHP files updated, nothing to check."
  exit 0
fi

phpFiles=$(echo "${phpFiles}" | xargs)
echo "Checking files: $phpFiles"
echo "Args: $args"

# shellcheck disable=SC2086
source_bin_file phpcs ${args} ${phpFiles} --report-full --report-checkstyle=./phpcs-report.xml
