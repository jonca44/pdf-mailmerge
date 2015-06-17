#!/bin/bash

##########################################
#
# Rocket Mail Merge - PDF Preview Cleanup
# Author : Leon Muller
# Date   : 15-01-13
#
# Desc   : Delete preview pdf's > 1 hour old to save hdd space.
#
#########################################
base_dir='/home/mymailme/app/user_files'
document_dir='generated_documents_preview'

for dir in `find $base_dir -maxdepth 1 -type d \( ! -name '$base_dir' \)`
do
  test -d "$dir" || continue #Check that it is a directory,
  if [ "$dir" = $base_dir ];  #Check it's not out base directory.
  then
	continue
  fi
  
  test -d "$dir/$document_dir" || continue #Check out document dir exists.
	

  #Set to find files > 59 minutes old, as our cron runs once an hour using this cleanup script.
  files="$(find -L "$dir/$document_dir" -type f -mmin +59)"
  filecount=$(echo -n "$files" | wc -w)

  if [ $filecount -gt 0 ]; then
    echo "Clearing $dir/$document_dir - $filecount found."
    echo "$files" | while read file; do
      rm $file
    done
  fi

done
