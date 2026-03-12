#!/usr/bin/env bash

REMOTE="fbhi.se:/www/webvol53/ww/lh9azq37w77rgig/fbhi.se/public_html/wp-content/themes/"
LOCAL="/home/dblom/wsl-projects/fbhi-website/salient-child"

rsync -avz --progress \
  --exclude ".git" \
  --exclude "upload.sh" \
  "$LOCAL" \
  "$REMOTE"
  