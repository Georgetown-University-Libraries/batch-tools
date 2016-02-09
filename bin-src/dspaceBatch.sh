ROOT=DSPACETOOLSROOT
DSROOT=DSPACEROOT
HPFX=YOURPFX
SOLR=SOLRROOT
VER=DSPACEVER
SCRIBD=

USERNAME=$1
shift
BATCH=`date +"%Y-%m-%d_%H:%M:%S"`
FNAME=job.${BATCH}-${USERNAME}.$1
QDIR=${ROOT}/queue/
RUNNING=${QDIR}/${FNAME}.running.txt
COMPLETE=${QDIR}/${FNAME}.complete.txt


RUN=false

function update_discovery {
    export JAVA_OPTS=-Xmx1200m   
    echo "${DSROOT}/bin/dspace index-discovery" >> ${RUNNING} 2>&1 
    ${DSROOT}/bin/dspace index-discovery >> ${RUNNING} 2>&1 
}

function update_oai {
    export JAVA_OPTS=-Xmx1200m   
    echo "${DSROOT}/bin/dspace oai import" >> ${RUNNING} 2>&1
    ${DSROOT}/bin/dspace oai import >> ${RUNNING} 2>&1
}

function update_oai_opt {
    export JAVA_OPTS=-Xmx1200m   
    echo "${DSROOT}/bin/dspace oai import -o" >> ${RUNNING} 2>&1
    ${DSROOT}/bin/dspace oai import -o >> ${RUNNING} 2>&1
}

function update_solr {
  $(update_discovery)
  $(update_oai)
}

function discovery_opt {
    echo "${DSROOT}/bin/dspace index-discovery -o" >> ${RUNNING} 2>&1 
    ${DSROOT}/bin/dspace index-discovery -o >> ${RUNNING} 2>&1 
}

function index_update {
    echo "Index update is N/A since DSpace 4" >> ${RUNNING} 2>&1 
}

function bulk_ingest {
  echo Command: import -a -e $USER -c $COLL -s $LOC -m $MAP >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace import -a -e $USER -c $COLL -s $LOC -m $MAP >> ${RUNNING} 2>&1 

  echo "Modify Map File : ${MAP}" >> ${RUNNING} 
  sed -e "s/ /_/g" -i $MAP >> ${RUNNING} 2>&1 
  sed -e "s|_\(${HPFX}\\.[0-9]/\)| \1|" -i $MAP >> ${RUNNING} 2>&1 
  sed -e "s|_\(${HPFX}/\)| \1|" -i $MAP >> ${RUNNING} 2>&1 

  if [ "$SCRIBD" != "" ]
  then
    echo "${DSROOT}/bin/dspace filter-media -p '${SCRIBD}' -f -n -v -i $COLL" >> ${RUNNING} 
    ${DSROOT}/bin/dspace filter-media -p "${SCRIBD}" -f -n -v -i $COLL >> ${RUNNING} 2>&1 
  fi
     
  export JAVA_OPTS=-Xmx1200m   
  echo "${DSROOT}/bin/dspace filter-media -i $COLL" >> ${RUNNING} 
  ${DSROOT}/bin/dspace filter-media -i $COLL >> ${RUNNING} 2>&1         
}

function download_zip {
  echo "wget -O ${ZIP} ${URL}" >> ${RUNNING}
  wget -O ${ZIP} ${URL} >> ${RUNNING} 2>&1
}

function unzip_ingest {
  echo "rm -rf $LOC" >> ${RUNNING}
  rm -rf $LOC >> ${RUNNING} 2>&1

  echo "unzip $ZIP -d $LOC" >> ${RUNNING}
  unzip $ZIP -d $LOC >> ${RUNNING} 2>&1
}

echo Command: "$@" > ${RUNNING}

if [ "$1" = "filter-media" ]
then
  export JAVA_OPTS=-Xmx1200m   

  ${DSROOT}/bin/dspace "$@" >> ${RUNNING} 2>&1 
  
  REINDEX=1
  while [ $# -ge 1 ]
  do 
    x=$1
    shift
    
    if [ "$x" = "-n" ]
    then
      REINDEX=0
    fi
  done
  
  if [ $REINDEX = 1 ]
  then
    $(update_solr)
  fi

elif [ "$1" = "metadata-import" ]
then
  export JAVA_OPTS="-Dfile.encoding=UTF-8"
  ${DSROOT}/bin/dspace "$@" >> ${RUNNING} 2>&1 

elif [ "$1" = "gu-refresh-statistics" ]
then
  export JAVA_OPTS=-Xmx1200m   
  echo ${DSROOT}/bin/dspace stat-general >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-general >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stat-report-general >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-report-general >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stat-monthly >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-monthly >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stat-report-monthly >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-report-monthly >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stats-util -o >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stats-util -o >> ${RUNNING} 2>&1 

  $(discovery_opt)
elif [ "$1" = "gu-update-index" ]
then
  $(index_update)
  $(discovery_opt)
  $(update_oai_opt)

elif [ "$1" = "gu-change-parent" ]
then
  echo ${DSROOT}/bin/dspace community-filiator -r -c $2 -p $3 >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace community-filiator -r -c $2 -p $3 >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace community-filiator -s -c $2 -p $4 >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace community-filiator -s -c $2 -p $4 >> ${RUNNING} 2>&1 
elif [ "$1" = "gu-change-coll-parent" ]
then
  echo "Updating database..." >> ${RUNNING} 2>&1 
  /usr/bin/psql -c "update community2collection set community_id=$4 where community_id=$3 and collection_id=$2;" >> ${RUNNING} 2>&1
  echo " ** The item has been moved, but the search index does not yet reflect the change" >> ${RUNNING} 2>&1 
  echo " ** Try re-indexing the parent community" >> ${RUNNING} 2>&1 
  echo " ** " >> ${RUNNING} 2>&1 
  echo " ** If items do not appear to be correctly indexed, then run the following steps" >> ${RUNNING} 2>&1 
  echo " ** You must run index-init while the server is offline" >> ${RUNNING} 2>&1 
  echo " ** You must run update-discovery-index/index-discovery -f after restarting the server" >> ${RUNNING} 2>&1 
  echo " ** You must then run oai import" >> ${RUNNING} 2>&1 
elif [ "$1" = "gu-ingest" ]
then 
  USER=$2
  COLL=$3
  LOC=$4
  MAP=$5
  
  $(bulk_ingest)
  $(update_solr)
elif [ "$1" = "gu-ingest-zip" ]
then 
  USER=$2
  COLL=$3
  ZIP=$4
  LOC=${ZIP%\.[Zz][Ii][Pp]}
  MAP=$5

  $(unzip_ingest)
  $(bulk_ingest)
  $(update_solr)
elif [ "$1" = "gu-ingest-zipurl" ]
then 
  USER=$2
  COLL=$3
  URL=$4
  ZIP=$5
  LOC=${ZIP%\.[Zz][Ii][Pp]}
  MAP=$6

  $(download_zip)
  $(unzip_ingest)
  $(bulk_ingest)
  $(update_solr)
elif [ "$1" = "gu-ingest-skipindex" ]
then 
  USER=$2
  COLL=$3
  LOC=$4
  MAP=$5
  
  $(bulk_ingest)
elif [ "$1" = "gu-ingest-zip-skipindex" ]
then 
  USER=$2
  COLL=$3
  ZIP=$4
  LOC=${ZIP%\.[Zz][Ii][Pp]}
  MAP=$5

  $(unzip_ingest)
  $(bulk_ingest)
elif [ "$1" = "gu-ingest-zipurl-skipindex" ]
then 
  USER=$2
  COLL=$3
  URL=$4
  ZIP=$5
  LOC=${ZIP%\.[Zz][Ii][Pp]}
  MAP=$6

  $(download_zip)
  $(unzip_ingest)
  $(bulk_ingest)
elif [ "$1" = "gu-uningest" ]
then 
  USER=$2
  MAP=$3
  
  echo Command: import -d -e ${USER} -m "${MAP}" >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace import -d -e ${USER} -m "${MAP}" >> ${RUNNING} 2>&1 
elif [ "$1" = "gu-reindex" ]
then 
  SRCH=$2
  VAL=$3
  
  echo Command: curl "${SOLR}/search/update?stream.body=%3Cupdate%3E%3Cdelete%3E%3Cquery%3Elocation.${SRCH}:${VAL}%3C/query%3E%3C/delete%3E%3C/update%3E" >> ${RUNNING} 2>&1 
  curl "${SOLR}/search/update?stream.body=%3Cupdate%3E%3Cdelete%3E%3Cquery%3Elocation.${SRCH}:${VAL}%3C/query%3E%3C/delete%3E%3C/update%3E" >> ${RUNNING} 2>&1 

  $(update_discovery)

elif [ "$1" = "gu-clean-oai-cache" ]
then
  export JAVA_OPTS=-Xmx1200m   

  echo ${DSROOT}/bin/dspace oai import >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace oai import >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace oai clean-cache >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace oai clean-cache >> ${RUNNING} 2>&1 
elif [ "$1" = "normalize-lang" ]
then
  echo "Update lang from null to en" >> ${RUNNING} 2>&1 
  /usr/bin/psql -c "update metadatavalue set text_lang='en' where text_lang is null and resource_type_id=2;" >> ${RUNNING} 2>&1

  echo "Update lang from '',en_US, en_us to en" >> ${RUNNING} 2>&1 
  /usr/bin/psql -c "update metadatavalue set text_lang='en' where text_lang in ('','en_US','en_us') and resource_type_id=2;" >> ${RUNNING} 2>&1
elif [ "$1" = "apt-export" ]
then
  shift
  USER=shift
  for ITEM in "$@"
  do
    OUTFILE=/opt/dg-transfer/data/apt-export/${ITEM//\//-}.zip

    echo "/opt/dspace/bin/dspace packager -d -e ${USER} -t AIP -o includeBundles=ORIGINAL,LICENSE -i ${ITEM} $OUTFILE" >> ${RUNNING} 2>&1 
    /opt/dspace/bin/dspace packager -d -e ${USER} -t AIP -o includeBundles=ORIGINAL,LICENSE -i "${ITEM}" $OUTFILE
  done
else
  echo "Unsupported DSpace Command" >> ${RUNNING}
fi

echo "Job complete" >> ${RUNNING} 2>&1
date >> ${RUNNING} 2>&1
mv ${RUNNING} ${COMPLETE}
