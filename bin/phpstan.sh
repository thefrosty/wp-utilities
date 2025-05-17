#!/usr/bin/env bash

set -e

source "$(dirname "$0")/functions.sh"
echo 'Running PHPStan'

against="${GITHUB_HEAD_REF:=$(get_commit_against)}"
commit="${GITHUB_BASE_REF:=develop}"
echo "git merge-base commit: ${commit} against: ${against}"
if [[ -z ${CHANGED_FILES+x} ]]; then
  commitFiles=$(git diff --name-only "$(git merge-base "${commit}" "${against}")")
else
  commitFiles="${CHANGED_FILES}"
fi

args="${ARGS:=analyze --memory-limit 1G $*}"
phpFiles=""
phpFilesCount=0
for f in ${commitFiles}; do
  if [[ ! -e ${f} ]]; then
    continue
  fi
  if [[ ${f} =~ \.(php|ctp)$ && ! ${f} =~ ^tests/ ]]; then
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

# shellcheck disable=SC2086
source_bin_file phpstan ${args} ${phpFiles}
