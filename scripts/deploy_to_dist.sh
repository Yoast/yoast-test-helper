#!/usr/bin/env bash
# Repo to deploy to:
USER="Yoast-dist"
REPO="yoast-test-helper"
REPO_URL="git@github.com:$USER/$REPO.git"
# Get the latest tag.
lastTag=$(TRAVIS_TAG)
mainDir=$(pwd)
# Create a new git repos.
cd ./artifact
git init
git remote add origin ${REPO_URL}
#commit the files.
git add -A
git commit -m "commit version tag ${lastTag} "
#tag the commit.
git tag ${lastTag} $(git rev-parse HEAD)
#push.
git push -u origin master --tags -f -v
