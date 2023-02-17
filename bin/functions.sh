#!/usr/bin/env bash

set -e

# Default values of arguments
# https://stackoverflow.com/a/44750379/558561 -- get the default git branch name
DEFAULT_BRANCH=$(git remote show $(git remote) | sed -n '/HEAD branch/s/.*: //p')
OTHER_ARGUMENTS=()
PHP_VERSION=${PHP_VERSION:-"8.0"}

# Loop through arguments and process them
# @ref https://pretzelhands.com/posts/command-line-flags/
function get_arguments() {
  for arg in "$@"; do
    case $arg in
    --default-branch=*)
      DEFAULT_BRANCH="${arg#*=}"
      shift # Remove --default-branch= from processing
      ;;
    --test-version=*)
      TEST_VERSION="${arg#*=}"
      shift # Remove --test-version= from processing
      ;;
    *)
      OTHER_ARGUMENTS+=("$1")
      shift # Remove generic argument from processing
      ;;
    esac
  done
}

# Composer 2.2.x https://getcomposer.org/doc/articles/vendor-binaries.md#finding-the-composer-bin-dir-from-a-binary
if [[ -z "$COMPOSER_BIN_DIR" ]]; then
  BIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
else
  BIN_DIR="$COMPOSER_BIN_DIR"
fi

export BIN_DIR

function get_branch() {
  echo "${GITHUB_BASE_REF:=develop}"
}

# Based off: https://gist.github.com/Hounddog/3891872
# Branch to check current commit against
function get_commit_against() {
  if [[ $(git rev-parse --verify HEAD) ]]; then
    echo 'HEAD'
  elif [[ $(git rev-parse --verify develop) ]]; then
    echo 'develop'
  elif [[ $(git rev-parse --verify main) ]]; then
    echo 'main'
  elif [[ $(git rev-parse --verify master) ]]; then
    echo 'master'
  elif [[ $(git rev-parse --verify "$1") ]]; then
    echo "$1"
  else
    echo "git can't verify HEAD, develop, main, or master."
    exit 1
  fi
}

# Helper function to call a bash file with arguments
# $1: The file to call
# $2: The first arguments to pass to the file
# $3: The second arguments to pass to the file
function source_bin_file() {
  if [[ ! "${1+x}" ]]; then
    echo "Error: missing file" && exit 1
  fi

  FILE=./vendor/bin/"$1"
  if [[ -f "$FILE" ]]; then
    # shellcheck disable=SC2086
    "$FILE" "${@:2}"
  else
    # shellcheck disable=SC2086
    "$BIN_DIR"/"$1" "${@:2}"
  fi
}

against=$(get_commit_against "$@")
commit=$(get_branch)
echo "git merge-base commit: ${commit} against: ${against}"
if [[ -z ${CHANGED_FILES+x} ]]; then
    #commitFiles=$(git diff --name-only "$(git merge-base "${commit}" "${against}")")
    commitFiles=$(git diff --name-only "$(git merge-base ${DEFAULT_BRANCH:-develop} ${against})")
  else
    commitFiles="${CHANGED_FILES}"
fi

export commitFiles
