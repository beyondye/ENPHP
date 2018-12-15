#!/bin/sh
#
# release script
#

# config
GIT_PATH=/srv/release/dashboard
ROOT_PATH=/srv/release
PRD_SERVERS=(prd.web1.server prd.web2.server)

USER="www"

# function
log() {
    exec 3>&1
    exec 1>>"${ROOT_PATH}/release.log"
    echo `date`:$*
    exec 1>&3
    exec 3>&-
}

dlog() {
    log $*
    echo $*
}

gitexport() {
    cd $1
    git pull origin production
}



to_rsync() {
    dlog "Starting rsync $GIT_PATH/code/ ==> $TARGET $USER@$1:$DEST_DIR"
    rsync -rvz -c  --progress "$GIT_PATH/code/" "$USER@$1:$DEST_DIR"
}


# main begin
if [ $# -ne 1 ]; then
    echo "Usage: `basename $0` production"
    exit 1
fi

TARGET="$1"



# main begin
if [ $# -ne 1 ]; then
    echo "Usage: `basename $0` production"
    exit 1
fi

TARGET="$1"

case "$TARGET" in
    production)
       SERVERS=${PRD_SERVERS[@]}
       USER=root
       DEST_DIR=/srv/www/
    ;;
    *)
       dlog "error TARGET: $TARGET"
       exit 1
    ;;
esac


# Exporting source code
dlog "Exporting Source Code:"
gitexport $GIT_PATH


# Start  rsync run
read -p "Are you sure you want to release $TARGET to production environment ?(yes to continue, else to quit) " chk
if [ "x$chk" == "xyes" ]; then
    dlog "Rsyncing source code to $TARGET:"
    for i in $SERVERS
    do
       echo " "
       echo "---------$i------------"
       to_rsync $i
    done

else
    dlog "Quiting."
    exit 0
fi

